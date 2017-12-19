<?php
/**
 * LogController 视频课件学习播放日志控制器.
 * Author: lch
 * Email: 15335667@qq.com
 * 一般客户端会每分钟提交一次当前的播放状态
 */
class LogController extends Controller{
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
    private $newplaycourseHashName = 'newplaycourse'; //存在用户最新播放过的课件编号
    public function init(){
        parent::init();
    }
    public function parameterRules(){
        return array(
            'addAction'   =>  array(
                'logid'  =>  array('name'=>'logid','type'=>'int','require'=>true,'default'=>0),
                'uid'  =>  array('name'=>'uid','type'=>'int','require'=>true,'default'=>0),
                'cwid'  =>  array('name'=>'cwid','type'=>'int','require'=>true,'default'=>0),
                'ctime'  =>  array('name'=>'ctime','type'=>'int','require'=>true,'default'=>0),
                'ltime'  =>  array('name'=>'ltime','type'=>'int','require'=>true,'default'=>0),
                'finished'  =>  array('name'=>'finished','type'=>'int','default'=>0),
                'curtime'  =>  array('name'=>'curtime','type'=>'int','default'=>0),
                'crid'  =>  array('name'=>'crid','type'=>'int','default'=>0),
                'folderid'  =>  array('name'=>'folderid','type'=>'int','default'=>0),
                'ip' => array('name'=>'ip','type'=>'string','default'=>'','max'=>16)
            ),
            'getAction'   =>  array(
                'uid'  =>  array('name'=>'uid','type'=>'int','require'=>true),
                'cwid'  =>  array('name'=>'cwid','type'=>'int','require'=>true)
            ),
            'listAction'   =>  array(
                'uid'  =>  array('name'=>'uid','type'=>'int','require'=>true),
                'cwids'  =>  array('name'=>'cwids','type'=>'string','require'=>true)
            ),
            'newcourseAction'   =>  array(
                'uid'  =>  array('name'=>'uid','type'=>'int','require'=>true),
                'crid'  =>  array('name'=>'crid','type'=>'int','require'=>true)
            ),
        );
    }
    /**
     * 客户端播放视频课件添加学习时间记录接口
     * 如果已经存在logid，则为编辑，否则添加
     * 业务逻辑说明：
        1，客户端在播放视频时每一分钟会自动提交当前状态，客户也可以手动提交学习时间
        2，每播放一次视频，只添加一条记录，如在播放界面一直提交很多次，也只算作一次
           如果本次播放的第一次提交，则会生成一条记录同时返回新记录的logid，客户端第二次提交则将
           logid带上
        3，如果重新打开播放页面，或者不同终端，则为多次
        4，finished字段由客户端提交，当客户端整个视频播放完成则提交此字段
        5，每个用户对每个视频的学习记录有个总表，由totalflag字段来判断、
        6，对于totalflag为1的记录，则startdate为该用户对该视频的第一次播放，lastdate为最后一次
     * 性能问题：
        1，鉴于学习记录每分钟会自动提交，当多人同时看视频（如伪直播课）时，就会对服务器造成较大影响。
            如1000人同时看一个视频，那么理论上每分钟就会产生至少1000条的记录，而且对于一节课可能就会有
            1000的并发，如果多个网校，并发更大
        2，考虑到并发问题，如果每次提交都插入或者编辑数据库，则数据库将压力巨大，另外现在记录表中的lastdate字段为索引字段
            对数据库来说简直雪上加霜
     * 优化方式：
        1，所有的插入操作不操作数据库，而只是放入redis中
           logid不存在，则通过redis生成最大的logid，每次自增
           logid已存在，则直接通过redis修改记录
        2，totalflag为1的记录存在redis缓存中，缓存不存在则从数据库获取
        //单次的缓存key规则 name:log key:logid value:logForm
        //总次的缓存key规则 name:log key:cwid_uid value:logForm total
        3，对记录的处理在 /LogTask 控制器中处理
     */
	public function addAction() {
        $redis = Ebh()->cache;  //redis对象
        if ($this->ltime < 0)
			$this->ltime = 0;
        $logForm = array('logid' => $this->logid, 'uid' => $this->uid, 'cwid'=>$this->cwid, 'ctime'=>$this->ctime,
                         'ltime' => $this->ltime, 'finished' => $this->finished, 'curtime'=>$this->curtime, 'crid' => $this->crid, 
                         'folderid' => $this->folderid);
        $ctime = $this->ctime;
        $ltime = $this->ltime;
        $logid = $this->logid;
        //验证IP
        $ip = $this->ip;
        if(!empty($ip) && !preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $ip)) {
            return '';
        }
		//处理ctime为0问题，一般在转码未成功时或者老的视频播放器会存在这样的问题
		if ($logid <= 0 && $ctime <= 0) {
			return '';
		}
        $logForm['ip'] = $ip;

        $logForm['startdate'] = SYSTIME;
        $logForm['lastdate'] = SYSTIME;
        $logFormCache = FALSE;
        if ($logid > 0) {
            $logFormCache = $redis->hGet($this->hashName,$logid);
            if (!empty($logFormCache)) {    //如果logid存在并且已经缓存，则将缓存数据与最新记录合并，再回写缓存
                $ctime = $logFormCache['ctime'];
                $ltime = $logFormCache['ltime'] > $ltime ? $logFormCache['ltime'] : $ltime;
                $logForm['ltime'] = $ltime;
                $logForm['startdate'] = $logFormCache['startdate'];
                $logForm['crid'] = $logFormCache['crid'];
                $logForm['folderid'] = $logFormCache['folderid'];
                $logForm['lastltime'] = $logFormCache['ltime']; //用来记录上一次的学习持续时间，用于对totalltime做增量
            }
        }
        $isqueue = FALSE;
        if ($logid <= 0 || empty($logFormCache)) {    //表示首次播放，如果缓存失效则也当成首次播放
            $logid = $logid <= 0 ? $redis->hIncrBy($this->hashName,$this->maxlogidKey) : $logid;   //请求下redis并返回当前自增最大值
            //构造数据参数
            //不存在缓存，则需要添加课件的相关信息，再将最新信息放入缓存
            $logForm['logid'] = $logid;
            //获取课件相关信息，如时长，所在课程所在网校等
            $course = $this->_getSimpleCourseInfo($this->cwid);
            if (empty($course)) {   //课件已不存在，则直接返回
                return FALSE;
            }
            $ctime = $course['cwlength'];
            if ($ctime <= 0)
                $ctime = $this->ctime;
            if(empty($logForm['crid']))
                $logForm['crid'] = $course['crid'];
            if(empty($logForm['folderid']))
                $logForm['folderid'] = $course['folderid'];
            $isqueue = TRUE;
        }
        $logForm['ctime'] = $ctime;
        if ($logForm['ltime'] > 2 * $logForm['ctime'])  //持续时间太长，则进行控制
            $logForm['ltime'] = 2 * $logForm['ctime'];
        if ($logForm['ltime'] <= 0) {   //避免从缓存中读取到ltime为0的情况
            $logForm['ltime'] = $this->ltime;
        }
        if ($ctime <= 0)    //避免除0异常
            $ctime = 1;
        if ($ltime / $ctime > 0.9 ) {   //90%就当播放完成
            $logForm['finished'] = 1;
        }
        $redis->hSet($this->hashName,$logid,$logForm);
        if ($isqueue)  { //将一次播放的logid相关信息入队列
            $this->_addLogQueue($logForm);
            $this->_addNewPlayCourse($logForm);    //将最新播放过的cwid放到缓存，便于e板会平台等获取最新的课件播放记录
        }
        //将最后一次播放记录放入redis中
        $this->_updateTotalLog($logForm,$isqueue);
        return array('logid'=>$logid);
	}
    /**
     * 获取用户对于某个课件的总的学习记录
     */
    public function getAction() {
        $uid = $this->uid;
        $cwid = $this->cwid;
        $totalLogForm = $this->_getTotalLog($uid,$cwid);
        return $totalLogForm;
    }
    /**
     * 获取用户对于某几个课件的总的学习记录
     */
    public function listAction() {
        $loglist = array();
        $uid = $this->uid;
        $cwids = $this->cwids;  //课件的id组合，逗号隔开，如 10983,10984
        if (!empty($cwids)) {
            $cwidlist = explode(',', $cwids);
            foreach ($cwidlist as $cwid) {
                if (intval($cwid) <= 0)
                    continue;
                $loglist[$cwid] = $this->_getTotalLog($uid,$cwid,TRUE);
            }
        }
        return $loglist;
    }
    /** 
     * 获取用户对某个课件学习总记录
     * @param $uid int 用户编号
     * @param $cwid int 课件cwid
     * @param $isstrict bool 是否严格模式，如果true，当缓存中没有，就从数据库取
     */
    private function _getTotalLog($uid,$cwid,$isstrict = FALSE) {
        $redis = Ebh()->cache;  //redis对象
        $totalLogForm = $redis->hGet($this->totalHashName,$cwid.'_'.$uid);
        if (empty($totalLogForm) && $isstrict) {    //缓存中不存在，则从数据库中取
            $logmodel = new PlayLogModel();
            $param = array('uid'=>$uid,'cwid'=>$cwid);
            $totalLogForm = $logmodel->getTotalLog($param,TRUE);
        }
        return $totalLogForm;
    }
    /**
     * 将播放记录添加入列，用于后期对数据进行加工等处理
     */
    private function _addLogQueue($logForm) {
        $redis = Ebh()->cache;  //redis对象
        //按天和小时对队列进行命名即每天将会有24个队列格式为 2017033018
        $queueName = 'log_'.date('ymdH',$logForm['startdate']);    
        $redis->qPush($queueName,$logForm['logid']);
    }
    /**
     * 获取用户在某网校下最新学习过的课件编号数组
     */
    public function newcourseAction() {
        $redis = Ebh()->cache;  //redis对象
        $uid = $this->uid;
        $crid = $this->crid;
        $newlist = $redis->hGet($this->newplaycourseHashName,$crid.'_'.$uid);
        return $newlist;
    }
    /**
     * 将最近播放过的课件编号放入缓存
     * @param $logForm array 播放记录数组
     */
    private function _addNewPlayCourse($logForm) {
        $redis = Ebh()->cache;  //redis对象
        $newlist = $redis->hGet($this->newplaycourseHashName,$logForm['crid'].'_'.$logForm['uid']);
        $hasupdate = TRUE;
        if (empty($newlist)) {
            $newlist = array($logForm['cwid']);
        } else {
            if (in_array($logForm['cwid'],$newlist)) {
                $hasupdate = FALSE;
            } else {
                $newlist[] = $logForm['cwid'];
                if (count($newlist) > 10) { //只保留10个
                    unset($newlist[0]);
                }
            }
        }
        if ($hasupdate) {
            $redis->hSet($this->newplaycourseHashName,$logForm['crid'].'_'.$logForm['uid'],$newlist);
        }
    }
    /**
     * 更新最新的总的播放记录到缓存
     * @param $logForm array 播放记录数组
     * @param $isnew bool 是否是新的播放记录，如果是的话 需要更新播放次数
     */
    private function _updateTotalLog($logForm,$isnew = FALSE) {
        $redis = Ebh()->cache;  //redis对象
        $totalLogForm = $this->_getTotalLog($logForm['uid'],$logForm['cwid'],$isnew);
        if (empty($totalLogForm)) {
            $totalLogForm = $logForm;
            $totalLogForm['playcount'] = 1;
            $totalLogForm['totalltime'] = $logForm['ltime'];
        } else {    //已经存在则更新最大记录
            //处理持续时间，如果是不同的logid则需要累加，如果相同的logid，则不能累加，否则就会重复加
            if (empty($totalLogForm['totalltime']))
                $totalLogForm['totalltime'] = $totalLogForm['ltime'];
            if ($isnew) {
                $totalLogForm['totalltime'] += $logForm['ltime'];
            } else {
                $ltime = !empty($logForm['lastltime']) ? $logForm['ltime'] - $logForm['lastltime'] : SYSTIME - $totalLogForm['lastdate'];  
                if ($ltime < 0)
                    $ltime = 0;
                //同一次播放，则取每次ltime的差值
                $totalLogForm['totalltime'] += $ltime;
            }
            //更新最大的一次ltime时间，此记录用于表示每次的最长学习时间
            $totalLogForm['ltime'] = $logForm['ltime'] > $totalLogForm['ltime'] ? $logForm['ltime'] : $totalLogForm['ltime'];   
            if (empty($totalLogForm['ctime'])) {
                $totalLogForm['ctime'] = $logForm['ctime'];
            }
            $totalLogForm['lastdate'] = $logForm['lastdate'];
            $totalLogForm['curtime'] = $logForm['curtime'];
            $totalLogForm['finished'] = $totalLogForm['finished'] == 0 ? $logForm['finished'] : $totalLogForm['finished'] ;
            $totalLogForm['ip'] = $logForm['ip'];
            if (empty($totalLogForm['playcount']))
                $totalLogForm['playcount'] = 1;
            else if($isnew)
                $totalLogForm['playcount'] ++;    //每次学习次数累加
        }
        $redis->hSet($this->totalHashName,$logForm['cwid'].'_'.$logForm['uid'],$totalLogForm);
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