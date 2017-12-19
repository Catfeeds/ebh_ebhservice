<?php
/**
 * 操作日志.
 * Author: ckx
 */
class LogController extends Controller{
	private $logmodel;
    public function init(){
		$this->logmodel = new LogModel();
        parent::init();
		
    }
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'starttime' =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
				'endtime' =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
				'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
				'page' =>	array('name'=>'page','default'=>1,'type'=>'int'),
				'q' =>  array('name'=>'q','default'=>'','type'=>'string'),
				'detailtype' =>  array('name'=>'detailtype','default'=>'','type'=>'string'),
				'roomtype' =>  array('name'=>'roomtype','default'=>'edu','type'=>'string'),
            ),
            'typeListAction'   =>  array(
                'roomtype' =>  array('name'=>'roomtype','default'=>'edu','type'=>'string'),
            ),
            'addLogAction'   =>  array(
                'toid'  =>  array('name'=>'toid','require'=>true,'type'=>'int'),
                'type' =>  array('name'=>'type','require'=>true,'type'=>'string'),
                'logsparam' =>  array('name'=>'logsparam','require'=>true,'type'=>'array'),
            ),
        );
    }
	
	/*
	操作日志列表
	*/
	public function listAction(){
		$param['crid'] = $this->crid;
		$param['starttime'] = $this->starttime;
		$param['endtime'] = $this->endtime;
		if($this->q !== NULL && $this->q !== ''){
			$param['q'] = $this->q;
		}
		$param['pagesize'] = $this->pagesize;
		$param['page'] = $this->page;
		$param['detailtype'] = $this->detailtype;
		$loglist = $this->logmodel->getLogList($param);
		$logcount = $this->logmodel->getLogCount($param);
		if(!empty($loglist)){
			$config = Ebh()->config->get('logdescription');
			foreach($loglist as &$log){
				if(!empty($config[$log['detailtype']])){
					$log['typestr'] = $config[$log['detailtype']]['typestr'];
				}
                if(!empty($this->roomtype) && $this->roomtype == 'com'){
                    $log['typestr'] = str_replace('[student]','员工',$log['typestr']);
                    $log['typestr'] = str_replace('[teacher]','讲师',$log['typestr']);
                }else{
                    $log['typestr'] = str_replace('[student]','学生',$log['typestr']);
                    $log['typestr'] = str_replace('[teacher]','教师',$log['typestr']);
                }
			}
		}
		return array('loglist'=>$loglist,'logcount'=>$logcount);
	}
	
	/*
	操作类型列表
	*/
	public function typeListAction(){
		$config = Ebh()->config->get('logdescription');
		$typearr = array();
		foreach($config as $type=>$info){
            if(!empty($this->roomtype) && $this->roomtype == 'com'){
                $info['typestr'] = str_replace('[student]','员工',$info['typestr']);
                $info['typestr'] = str_replace('[teacher]','讲师',$info['typestr']);
            }else{
                $info['typestr'] = str_replace('[student]','学生',$info['typestr']);
                $info['typestr'] = str_replace('[teacher]','教师',$info['typestr']);
            }
			$typearr[] = array('type'=>$type,'typestr'=>$info['typestr']);
		}
		return $typearr;
		
	}
    /**
     * 添加日志
     * @param $toid 被操作的id 被编辑的课程id,被添加的学生id 等
     * @param $type 类型,addcourse,editcourse,addstudent 等,见/config/logdescription.php
     * @param $param 需要从config替换的字符串数组
     * @return 操作日志logid
     */
    public function addLogAction(){
        $ebhlogs = array();
        $logsparam = $this->logsparam;
        if(empty($this->toid) || empty($this->type) || empty($logsparam['clientuid'])){
            return FALSE;
        }
        $ebhlogs['uid'] = $this->logsparam['clientuid'];//当前登录用户uid
        $ebhlogs['toid'] = $this->toid;//当前被操作用户uid
        $ebhlogs['crid'] = !empty($logsparam['crid']) ? $logsparam['crid'] : 0;//当前网校crid
        $fromip = getclientip();//操作人ip
        $ebhlogs['fromip'] = empty($fromip) ? '': $fromip;//操作人ip

        $type = $this->type;
        $config = Ebh()->config->get('logdescription');
        if(!empty($config[$type])){
            $ebhlogs['opid'] = $config[$type]['opid'];
            $ebhlogs['type'] = $config[$type]['type'];
        }
        if (empty($ebhlogs['opid']) || empty($ebhlogs['type'])){
            return FALSE;
        }
        $ebhlogs['detailtype'] = $type;
        $message = $config[$type]['message'];

        $logsparam = $this->logsparam;
        foreach($logsparam as $key=>$replace){
            $find = '['.$key.']';
            $message = str_replace($find,$replace,$message);
        }
        $ebhlogs['roomtype'] = !empty($logsparam['roomtype']) ? $logsparam['roomtype'] : 'edu';//当前网校类型
        if($ebhlogs['roomtype'] == 'com'){
            $message = str_replace('[student]','员工',$message);
            $message = str_replace('[teacher]','讲师',$message);
        }else{
            $message = str_replace('[student]','学生',$message);
            $message = str_replace('[teacher]','教师',$message);
        }
        $ebhlogs['message'] = $message;
        $logmodel = new LogModel();
        return $logmodel->add($ebhlogs);
    }
}