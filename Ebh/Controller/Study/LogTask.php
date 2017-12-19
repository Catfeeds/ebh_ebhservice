<?php
/**
 * LogTaskController 学习日志相关的任务控制器.
 * Author: lch
 * Email: 15335667@qq.com
 * 一般客户端每分钟会提交学习记录，为了优化，会将学习记录先存缓存队列中
 * 本控制器可以用来处理队列中的记录，以及相关的积分任务处理
 * 可通过定时器调用 /LogTask/doTask 来处理学习记录的汇总
 * 可通过定时器调用 /LogTask/doThirdTask 来处理积分相关信息的处理
 * 积分单次添加可以参考 /Log/add 的实现
 */
class LogTaskController extends Controller{
    /** redishash和队列存放规则
    1， ebh_log hash 
        a,临时日志记录放到此hash,key 为logid ，此为缓存中的自增值，而非数据库中的logid
        b,maxlogid 作为key，存在当前自增的最大logid
    2， ebh_totallog hash 用于存放每个用户每个课件的总的缓存
    3,  ebh_course 用于存放课件的缓存信息hash,key为 cwid_实际cwid值
    4,  log_队列，每次添加日志后需要将日志的logid放入队列中，队列每小时一个 格式为 log_17042611 即表示 17年4月26日11时的队列
    5,  log_success_ 队列，用于存放已经处理成功的logid记录，主要用于处理积分等需要依赖播放的任务
    */
    private $hashName = 'log';    //存放当前最大的logid hash name
    private $totalHashName = 'totallog';    //存放用于的总的学习记录缓存
    private $maxlogidKey = 'maxlogid';  //存放当前最大的logid hash key
    private $courseHashName = 'course'; //用到的课件缓存，都放到此hash中
    private $successQueueName = 'log_success';  //处理成功的队列，用于处理积分等依赖任务
    private $zjdlr = 12210 ;    //用于保存浙江省国土厅的crid,需要特殊处理积分
	private $successCount = 0;	//执行成功的日志数
	private $failCount = 0;	//执行失败的日志数
    public function init(){
        Ebh()->filter = 'Filter_Server';    //验证IP来源
        parent::init();
    }
    
    
    /**
     * 处理队列中的log相关任务，主要将零散的存放在redis中的日志记录 更新的数据库中 便于统计等
     * 一般可以定时任务方式访问，设置成每小时比较合理
     */
    public function doTaskAction() {
        $redis = Ebh()->cache;  //redis对象
        $ret = TRUE;
		log_message("批量处理学习记录到数据库开始");
        for ($i = 24; $i > 4; $i --) {  //4小时前的队列记录处理到数据库。每次处理最近一天的队列信息 避免消息遗漏 
            $prevQueueName = $this->hashName.'_'.date('ymdH',SYSTIME - $i * 3600);  
            $qlen = $redis->qLen($prevQueueName);
            if ($qlen > 0) {
                $ret = $this->_doQueueTask($prevQueueName);
                if (!$ret) {
                    log_message("queue:$prevQueueName fail");
                }
            }
        }
		log_message("成功数 :{$this->successCount} 失败数 :{$this->failCount}");
		log_message("批量处理学习记录到数据库结束");
        return array('result'=>$ret);
    }
    /**
     * 处理某个队列中的任务
     * @param $queueName string 队列名
     */
    private function _doQueueTask($queueName) {
        set_time_limit(0);  //设置不超时
        $redis = Ebh()->cache;  //redis对象
        $failQueueName = $this->hashName.'_'.date('ymdH',SYSTIME);   //如果处理失败，则放到下一小时的队列中，继续执行，不至于失败后数据丢失
        $logid = $redis->qPop($queueName);
        while ($logid > 0) {
            $itemret = $this->_doItemTask($logid);
            if ($itemret) {
                $this->successCount ++;
                $redis->qPush($this->successQueueName,$logid);
            }
            else {
                $this->failCount ++;
                //如果失败，则将失败的logid放入当前队列，由下一次处理处理程序处理
                $redis->qPush($failQueueName,$logid);
            }
            $nextlogid = $redis->qPop($queueName);  //循环处理
            if ($logid == $nextlogid)
                break;
            $logid = $nextlogid;
        }
        return TRUE;
    }
    /**
     * 将单个logid作为任务进行处理。将队列中的logid计算后存入数据库
     */
    private function _doItemTask($logid) {
        $redis = Ebh()->cache;  //redis对象
        $logForm = $redis->hGet($this->hashName,$logid);
        if (empty($logForm))
            return FALSE;
        $cwid = $logForm['cwid'];
        $uid = $logForm['uid'];
        $lastdate = $logForm['lastdate'];
        //如果记录在在两分钟之内的，则不处理，避免用户记录还未播放完成情况
        if (SYSTIME < ($lastdate + 120))  { 
            return FALSE;
        }
        $logmodel = new PlayLogModel();
        //将缓存中记录插入到playlog表
        unset($logForm['logid']);   //需要去掉logid字段，避免跟数据库字段冲突
        unset($logForm['lastltime']);   //需要去掉logid字段，避免跟数据库字段冲突
        //处理部分课件学习记录由于课件未转码完成导致cwlength为0问题，会造成之前部分数据ltime和ctime为0
        if ($logForm['ctime'] == 0 && $logForm['ltime'] == 0) {
            $course = $this->_getSimpleCourseInfo($cwid);
            if (!empty($course)) {
                $logForm['ctime'] = $course['cwlength'];
                $logForm['ltime'] = $logForm['lastdate'] - $logForm['startdate'];
                if ($logForm['ltime'] > $logForm['ctime'])
                    $logForm['ltime'] = $logForm['ctime'];
            }
        }
        $addresult = $logmodel->addLog($logForm);
        //更新总记录
		$crid = $logForm['crid'];
        //国土厅则只要学员听课总时长达到了就计算积分
		$ismeger = FALSE;
        if($crid == $this->zjdlr) {	//如果是国土的，则ltime按照每次学习时间累加
			$ismeger = TRUE;
		}
        $totalresult = $logmodel->addTotalLog($logForm,$ismeger);
        if ($addresult !== FALSE && $totalresult !== FALSE) {
            return TRUE;
        }
        return FALSE;
    }
    /**
     * 处理日志相关的其他事件，如国土厅的处理，或者积分的处理，可定时任务进行处理
     */
    public function doThirdTaskAction() {
		log_message("批量处理学习记录等关联任务开始");
        $redis = Ebh()->cache;  //redis对象
        $logid = $redis->qPop($this->successQueueName);
        $successcount = 0;
        $failcount = 0;
        while ($logid > 0) {
            $result = $this->_doThirdItemTask($logid);
            if ($result)
                $successcount ++;
            else
                $failcount ++;
            $logid = $redis->qPop($this->successQueueName);
        }
		log_message("成功数：$successcount 失败数：$failcount");
		log_message("批量处理学习记录等关联任务结束");
        return array('success'=>$successcount,'fail'=>$failcount);
    }
    /**
     * 处理已成功处理的日志记录，主要用于第三方在日志成功后的处理，如积分等
     * @param $logid int 从队列中获取的logid (此id主要用于redis，而非数据库表的logid)
     */
    private function _doThirdItemTask($logid) {
        $redis = Ebh()->cache;  //redis对象
        $logForm = $redis->hGet($this->hashName,$logid);
        if (empty($logForm))
            return FALSE;
        $cwid = $logForm['cwid'];
        $uid = $logForm['uid'];
        $ctime = $logForm['ctime'];
        $ltime = $logForm['ltime'];
        $crid = $logForm['crid'];
        //国土厅则只要学员听课总时长达到了就计算积分
		$config = Ebh()->config->get('othersetting');
		$this->zjdlr = $config['zjdlr'];
        if($logForm['finished'] != 1 && $crid == $this->zjdlr) {
            $logmodel = new PlayLogModel();
            $totalLog = $logmodel->getTotalLog($logForm,TRUE);
            if (!empty($totalLog) && ($totalLog['totalltime'] / $ctime) > 0.9 ) {
                $logForm['finished'] = 1;
            }
        }
        //播放完成后处理积分相关信息
        if ($logForm['finished'] == 1) { //已经播放完成则处理积分信息
            $cwinfo = $this->_getSimpleCourseInfo($cwid);
            $creditmodel = new CreditModel();
            $res = $creditmodel->addCreditlog(array('ruleid'=>5,'detail'=>$cwinfo['title'],'cwid'=>$cwid,'uid'=>$uid,'crid'=>$crid));
			if(!empty($cwinfo['folderid']) && !empty($res)){//添加学分记录
				$foldermodel = new FolderModel();
				$folder = $foldermodel->getFolderById($cwinfo['folderid']);
				if(!empty($folder['credit']) && !empty($folder['cwcredit']) && !empty($folder['cwpercredit']) && $folder['cwpercredit'] != 0){
					$cwnum = floor($folder['cwcredit']/$folder['cwpercredit']);//该课程可获得多少次学分
					$slmodel = new StudycreditlogsModel();
					$count = $slmodel->getUserSum(array('folderid'=>$cwinfo['folderid'],'crid'=>$crid,'uid'=>$uid,'type'=>0));
					if(isset($count['count']) && $count['count']<$cwnum){ //已获得学分的次数小于可获得次数,添加学分记录
						$logarr['fromip'] = getip();
						$logarr['type'] = 0;
						$logarr['folderid'] = $cwinfo['folderid'];
						$logarr['cwid'] = $cwid;
						$logarr['crid'] = $crid;
						$logarr['uid'] = $uid;
						$logarr['score'] = $folder['cwpercredit'];
						$logarr['dateline'] = empty($logForm['startdate'])?SYSTIME:$logForm['startdate'];
						$slmodel->addOneScore($logarr);
					}
				}
			}
        }
        return TRUE;
    }
    /**
     * 根据课件编号获取课件基本信息，主要获取跟播放学习日志相关的字段
     * @param $cwid int 课件编号cwid
     * @return Array 
     */
    private function _getSimpleCourseInfo($cwid) {
        $redis = Ebh()->cache;  //redis对象
        $cwidHashKey = 'cwid_'.$cwid;
        $course = $redis->hGet($this->courseHashName,$cwidHashKey);
        if (!empty($course) && $course['cwlength'] <= 0) {  //如果课件长度为0 则重新取数据库
            $course = '';
        }
        if (empty($course)) {   //不存在则通过model获取
            $courseModel = new CoursewareModel();
            $courserow = $courseModel->getSimpleInfoById($cwid);
            if (empty($courserow))
                return FALSE;
            $course = array('cwlength'=>$courserow['cwlength'],'crid'=>0,'folderid'=>0,'title'=>$courserow['title']);
            $courseextra = $courseModel->getExtraInfoById($cwid);
            if(!empty($courseextra)) {
                $course['crid'] = $courseextra['crid'];
                $course['folderid'] = $courseextra['folderid'];
            }
            $redis->hSet($this->courseHashName,$cwidHashKey,$course);
        }
        return $course;
    }
}