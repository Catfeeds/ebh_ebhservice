<?php
/**
 * LogFixTaskController 学习日志相关的任务控制器.
 * Author: lch
 * Email: 15335667@qq.com
 * 此控制器主要用于redis中的ebh_totallog 的值有明显问题的修复，
 * 主要针对两种情况 1， totalltime 小于 ltime的 等 2，totalltime 跟数据库中不符，还比数据库中小的
 */
class LogFixTaskController extends Controller{
    /** redishash和队列存放规则
    1， ebh_totallog hash 用于存放每个用户每个课件的总的缓存
    2,  ebh_course 用于存放课件的缓存信息hash,key为 cwid_实际cwid值
    */
    private $hashName = 'log';    //存放当前最大的logid hash name
    private $totalHashName = 'totallog';    //存放用于的总的学习记录缓存
    private $fullTotalHashName = 'ebh_totallog';    //实际的hashkey
    private $courseHashName = 'course'; //用到的课件缓存，都放到此hash中
    private $zjdlr = 12210 ;    //用于保存浙江省国土厅的crid,需要特殊处理积分
    public function init(){
        Ebh()->filter = 'Filter_Server';    //验证IP来源
        parent::init();
    }
    
    
    /**
     * 此控制器不处理，只列出有问题的redis记录
     */
    public function doCheckAction() {
        set_time_limit(0);  //设置不超时
		ini_set('memory_limit','2512M'); //升级为申请512M内存
        $result = array();
        $redis = Ebh()->cache;  //redis对象
        $keys = $this->_getKeys();
        $samekeys = array();
        $unsamekeys = array();
        if (!empty($keys)) {
            foreach($keys as $key) {
                @list($cwid,$uid) = explode('_', $key);
                if (!empty($cwid) && !empty($uid)) {
                    $cresult = $this->_checkKey($uid,$cwid);
                    if ($cresult === TRUE) {
                        $samekeys[$key] = true;
                    } else {
                        $unsamekeys[$key] = $cresult;
                    }
                }
            }
        }
        return array('same'=>$samekeys,'unsame'=>$unsamekeys);
    }
    /**
     * 处理与数据库不一致的缓存记录db->redis
     */
    public function doFixAction() {
        set_time_limit(0);  //设置不超时
		ini_set('memory_limit','2512M'); //升级为申请512M内存
        $result = array();
        $redis = Ebh()->cache;  //redis对象
        $keys = $this->_getKeys();
        $samekeys = array();
        $unsamekeys = array();
        if (!empty($keys)) {
            foreach($keys as $key) {
                @list($cwid,$uid) = explode('_', $key);
                if (!empty($cwid) && !empty($uid)) {
                    $cresult = $this->_doUpdateKey($uid,$cwid);
                    if ($cresult === TRUE) {
                        $samekeys[$key] = true;
                    } else {
                        $unsamekeys[$key] = FALSE;
                    }
                }
            }
        }
        $samecount = count($samekeys);
        $unsamecount = count($unsamekeys);
        log_message("same count is :".$samecount);
        log_message("unsame count is :".$unsamecount);
        return array('same'=>$samecount,'unsame'=>$unsamecount);
    }
    private function _getKeys() {
        $redis = Ebh()->cache;  //redis对象
        $nativeRedis = $redis->getRedis();  //原生redis对象，可以操作redis支持的方法，一般临时的功能才用
        $keys = $nativeRedis->hkeys($this->fullTotalHashName);
        return $keys;
    }
    private function _checkKey($uid,$cwid) {
        $redis = Ebh()->cache;  //redis对象
        $totalLogForm = $redis->hGet($this->totalHashName,$cwid.'_'.$uid);
        if (empty($totalLogForm)) { //不存在缓存记录则直接显示相同 
            return TRUE;
        }
        $logmodel = new PlayLogModel();
        $param = array('uid'=>$uid,'cwid'=>$cwid);
        $trueLogForm = $logmodel->getTotalLog($param,TRUE);
        if (empty($trueLogForm)) {   //不存在数据库记录也显示相同 
            return TRUE;
        }
        $same = TRUE;
        if ($trueLogForm['playcount'] != $totalLogForm['playcount']
            || $trueLogForm['ltime'] != $totalLogForm['ltime'] 
            || $trueLogForm['totalltime'] != $totalLogForm['totalltime'] 
			|| $trueLogForm['finished'] != $totalLogForm['finished'] ) {
            $same = FALSE;
            if ($trueLogForm['playcount'] > $totalLogForm['playcount']) {
                $totalLogForm['newplaycount'] = $trueLogForm['playcount'];
            }
            if ($trueLogForm['ltime'] > $totalLogForm['ltime']) {
                $totalLogForm['newltime'] = $trueLogForm['ltime'];
            }

            if ($trueLogForm['totalltime'] > $totalLogForm['totalltime']) {
                $totalLogForm['newtotalltime'] = $trueLogForm['totalltime'];
            }
			if ($trueLogForm['finished'] != $totalLogForm['finished']) {
                $totalLogForm['newfinished'] = $trueLogForm['finished'];
            }
            return $totalLogForm;
        }
        return $same;
    }
	/**
	*根据数据库记录更新总的缓存记录
	*/
    private function _doUpdateKey($uid,$cwid) {
        $redis = Ebh()->cache;  //redis对象
        $totalLogForm = $redis->hGet($this->totalHashName,$cwid.'_'.$uid);
        if (empty($totalLogForm))   //不存在缓存记录则直接显示相同
            return TRUE;
        $logmodel = new PlayLogModel();
        $param = array('uid'=>$uid,'cwid'=>$cwid);
        $trueLogForm = $logmodel->getTotalLog($param,TRUE);
        if (empty($trueLogForm))    //不存在数据库记录也显示相同
            return TRUE;
        $same = TRUE;
        if ($trueLogForm['playcount'] > $totalLogForm['playcount']
            || $trueLogForm['ltime'] > $totalLogForm['ltime'] 
            || $trueLogForm['totalltime'] > $totalLogForm['totalltime'] 
			|| $trueLogForm['finished'] != $totalLogForm['finished'] ) {
            $same = FALSE;
            if ($trueLogForm['playcount'] > $totalLogForm['playcount']) {
                $totalLogForm['playcount'] = $trueLogForm['playcount'];
            }
            if ($trueLogForm['ltime'] > $totalLogForm['ltime']) {
                $totalLogForm['ltime'] = $trueLogForm['ltime'];
            }

            if ($trueLogForm['totalltime'] > $totalLogForm['totalltime']) {
                $totalLogForm['totalltime'] = $trueLogForm['totalltime'];
            }
			if ($trueLogForm['finished'] != $totalLogForm['finished']) {
                $totalLogForm['finished'] = $trueLogForm['finished'];
            }
            $redis->hSet($this->totalHashName,$cwid.'_'.$uid,$totalLogForm);
        }
        return $same;
    }
	/**
     * 处理与缓存不一致的数据库记录 redis->db
     */
    public function doFixDbAction() {
        set_time_limit(0);  //设置不超时
		ini_set('memory_limit','2512M'); //升级为申请512M内存
        $result = array();
        $redis = Ebh()->cache;  //redis对象
        $keys = $this->_getKeys();
        $samekeys = array();
        $unsamekeys = array();
        if (!empty($keys)) {
            foreach($keys as $key) {
                @list($cwid,$uid) = explode('_', $key);
                if (!empty($cwid) && !empty($uid)) {
                    $cresult = $this->_doUpdateDb($uid,$cwid);
                    if ($cresult === TRUE) {
                        $samekeys[$key] = true;
                    } else {
                        $unsamekeys[$key] = FALSE;
                    }
                }
            }
        }
        $samecount = count($samekeys);
        $unsamecount = count($unsamekeys);
        log_message("same count is :".$samecount);
        log_message("unsame count is :".$unsamecount);
        return array('same'=>$samecount,'unsame'=>$unsamecount);
    }
	/**
	*根据缓存来同步更新数据库学习总记录
	*/
	private function _doUpdateDb($uid,$cwid) {
        $redis = Ebh()->cache;  //redis对象
        $totalLogForm = $redis->hGet($this->totalHashName,$cwid.'_'.$uid);
        if (empty($totalLogForm))   //不存在缓存记录则直接显示相同
            return TRUE;
        $logmodel = new PlayLogModel();
        $param = array('uid'=>$uid,'cwid'=>$cwid);
        $trueLogForm = $logmodel->getTotalLog($param,TRUE);
        if (empty($trueLogForm))    //不存在数据库记录也显示相同
            return TRUE;
        $same = TRUE;
        if ($trueLogForm['playcount'] < $totalLogForm['playcount']
            || $trueLogForm['ltime'] < $totalLogForm['ltime'] 
            || $trueLogForm['totalltime'] < $totalLogForm['totalltime'] 
			|| $trueLogForm['finished'] != $totalLogForm['finished'] ) {
            $same = FALSE;
            if ($trueLogForm['playcount'] < $totalLogForm['playcount']) {
                $trueLogForm['playcount'] = $totalLogForm['playcount'];
            }
            if ($trueLogForm['ltime'] < $totalLogForm['ltime']) {
                $trueLogForm['ltime'] = $totalLogForm['ltime'];
            }

            if ($trueLogForm['totalltime'] < $totalLogForm['totalltime']) {
                $trueLogForm['totalltime'] = $totalLogForm['totalltime'];
            }
			if ($trueLogForm['finished'] != $totalLogForm['finished'] && $trueLogForm['finished'] < 1) {
                $trueLogForm['finished'] = 1;
            }
            $result = $logmodel->addTotalLog($trueLogForm);
        }
        return $same;
    }
    
}