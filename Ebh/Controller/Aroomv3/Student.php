<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class StudentController extends Controller{
    public $roomUserModel;

    public function init(){
        parent::init();
        $this->roomUserModel = new RoomUserModel();
    }
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'q'  =>  array('name'=>'q','default'=>''),
                'classid'  =>  array('name'=>'classid','default'=>0,'type'=>'int'),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
                'isenterprise' => array('name'=>'isenterprise','type'=>'int','default' => 0),
                'issimple' => array('name' => 'issimple', 'type' => 'int', 'default' => 0)
            ),
            'detailAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
            ),
            'addAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'cnname'  =>  array('name'=>'cnname','require'=>true),
                'sex'  =>  array('name'=>'sex','type'=>'int','default'=>0),
                'birthdate'  =>  array('name'=>'birthdate','require'=>false),
                'email'  =>  array('name'=>'email','require'=>false),
                'mobile'  =>  array('name'=>'mobile'),
            ),
            'editAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'cnname'  =>  array('name'=>'cnname',),
                'sex'  =>  array('name'=>'sex','type'=>'int'),
                'birthdate'  =>  array('name'=>'birthdate'),
                'email'  =>  array('name'=>'email'),
                'mobile'  =>  array('name'=>'mobile'),
                'classid'  =>  array('name'=>'classid'),
                'oldclassid'  =>  array('name'=>'oldclassid'),
            ),
            'delAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'classid'  =>  array('name'=>'classid','require'=>true,'type'=>'int'),
            ),
			'newCountListAction' =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'starttime'  =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
				'byhour' => array('name'=>'byhour','default'=>0,'type'=>'int'),
            ),
			'uidListAction' =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'starttime'  =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
            ),
            'activateAction' => array(
                'crid' => array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid' => array('name'=>'uid','require'=>true,'type'=>'int'),
                'status' => array('name'=>'status','require'=>true,'type'=>'int')
            ),
            'editpwdAction'=>array(
                'password' => array('name'=>'password','require'=>true,'type'=>'string'),
                'uid' => array('name'=>'uid','require'=>true,'type'=>'int')
            ),
            'getRoomuserAction'=>array(
                'crid' => array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid' => array('name'=>'uid','require'=>true,'type'=>'int'),
            ),
            'getCreditCountAction'=>array(
                'beginTime'=>array('name'=>'beginTime','type'=>'int','require'=>true),
                'endTime'=>array('name'=>'endTime','type'=>'int','require'=>true),
                'p'=>array('name'=>'p','type'=>'int','default'=>1),
                'listRows'=>array('name'=>'listRows','type'=>'int'),
                'newlecture'=>array('name'=>'newlecture','type'=>'int','require'=>true),
                'newtransaction'=>array('name'=>'newtransaction','type'=>'int','require'=>true),
                'newregulations'=>array('name'=>'newregulations','type'=>'int','require'=>true),
                'communication'=>array('name'=>'communication','type'=>'int','require'=>true),
                'crid'=>array('name'=>'crid','type'=>'int','require'=>true),
                'type'=>array('name'=>'type','type'=>'int','require'=>true),
                'attach'=>array('name'=>'attach','type'=>'string'),
            )
        , 'outExclgetCreditCountAction'=>array(
                'beginTime'=>array('name'=>'beginTime','type'=>'int','require'=>true),
                'endTime'=>array('name'=>'endTime','type'=>'int','require'=>true),
                'newlecture'=>array('name'=>'newlecture','type'=>'int','require'=>true),
                'newtransaction'=>array('name'=>'newtransaction','type'=>'int','require'=>true),
                'newregulations'=>array('name'=>'newregulations','type'=>'int','require'=>true),
                'communication'=>array('name'=>'communication','type'=>'int','require'=>true),
                'crid'=>array('name'=>'crid','type'=>'int','require'=>true),

            )
        );
    }

    /**
     * 学生信息修改
     */
    public function editAction(){
        $parameters = array();
        $parameters['crid'] = $this->crid;
        $parameters['uid'] = $this->uid;

        if($this->cnname !== null && !empty($this->cnname)){
            $parameters['cnname'] = $this->cnname;
        }

        if($this->sex !== null){
            $parameters['sex'] = $this->sex;
        }

        if($this->birthdate !== null){
            $parameters['birthdate'] = $this->birthdate;
        }

        if($this->mobile !== null){ // && !empty($this->mobile)
            $parameters['mobile'] = $this->mobile;
        }

        if($this->email !== null && !empty($this->email)){
            $parameters['email'] = $this->email;
        }


        if($this->classid !== null && $this->oldclassid !== null){
            $parameters['classid'] = $this->classid;
            $parameters['oldclassid'] = $this->oldclassid;
        }

        $memberModel = new MemberModel();
        $afrows = $memberModel->editMember($parameters);
        $afrows += $this->roomUserModel->editStudent($parameters);
        return $afrows;

    }

    /**
     * 添加网校学生
     * @return mixed
     */
    public function addAction(){
        $parameters = array();
        $parameters['crid'] = $this->crid;
        $parameters['uid'] = $this->uid;
        $parameters['cnname'] = $this->cnname;
        $parameters['sex'] = $this->sex;
        $parameters['birthdate'] = $this->birthdate;
        $parameters['mobile'] = $this->mobile;
        $parameters['email'] = $this->email;

        return $this->roomUserModel->insert($parameters);
    }

    /**
     * 获取学生列表
     * @return array
     */
    public function listAction(){
        $parameters = array();
        $parameters['crid'] = $this->crid;
        if($this->q != ''){
            $parameters['q'] = $this->q;
        }
        $parameters['isEnterprise'] = $this->isenterprise;
        if($this->classid > 0){
            if ($parameters['isEnterprise'] == 1) {
                $classModel = new ClassesModel();
                $dept = $classModel->getDept($this->classid, $this->crid);
                if (empty($dept)) {
                    return array(
                        'total' =>  0,
                        'list'  =>  array(),
                        'nowPage'   =>  1,
                        'totalPage' =>  0
                    );
                }
                if ($dept['superior'] > 0) {
                    $parameters['lft'] = $dept['lft'];
                    $parameters['rgt'] = $dept['rgt'];
                }
            } else {
                $parameters['classid']  = $this->classid;
            }
        }


        $total = $this->roomUserModel->getStudentCount($parameters);
        $pageClass  = new Page($total,$this->pagesize);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $list = $this->roomUserModel->getStudentList($parameters);
		if(!empty($list) && $this->issimple == 0){
			$uids = array_column($list,'uid');
			$uids = implode(',',$uids);
			$llmodel = new LoginlogModel();
			$loginlist = $llmodel->firstLoginList(array('crid'=>$this->crid,'uids'=>$uids));
			$lastloginlist = $llmodel->lastLoginList(array('crid'=>$this->crid,'uids'=>$uids));
			foreach($list as &$user){
				$uid = $user['uid'];
				$user['cityname'] = empty($loginlist[$uid])?'':$loginlist[$uid]['cityname'];
				$user['pcityname'] = empty($loginlist[$uid])?'':$loginlist[$uid]['pcityname'];
				$user['ip'] = empty($loginlist[$uid])?'':$loginlist[$uid]['ip'];
				$user['lastlogin'] = empty($lastloginlist[$uid])?'':$lastloginlist[$uid]['lastlogin'];
			}
		}
        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages

        );
    }

    /**
     * 获取指定网校下学生信息
     */
    public function detailAction(){
        return $this->roomUserModel->getRoomuStudentDetail($this->crid,$this->uid);
    }

    /**
     * 删除指定班级的学生
     */
    public function delAction(){
        $parameters = array();
        $parameters['crid'] = $this->crid;
        $parameters['uid'] = $this->uid;
        $parameters['classid'] = $this->classid;
        $classesModel = new ClassesModel();

        return $classesModel->deleteStudent($parameters);

    }
	
	/*
	 *新成员数量列表，按天(只有一天,按小时)
	 */
	public function newCountListAction(){
		$param['crid'] = $this->crid;
		$param['starttime'] = $this->starttime;
		$param['endtime'] = $this->endtime;
		$param['byhour'] = $this->byhour;
		
		$list = $this->roomUserModel->getStudentCountByDay($param);
		return $list;
	}
	
	/*
	本校学生uid列表
	*/
	public function uidListAction(){
		$param['crid'] = $this->crid;
		$param['starttime'] = $this->starttime;
		$param['endtime'] = $this->endtime;
		return $this->roomUserModel->getUidList($param);
	}

    /**
     * 禁用/解禁用户
     * @return mixed
     */
	public function activateAction() {
	    return $this->roomUserModel->activate($this->status, $this->uid, $this->crid);
    }
    
    /**
     * 修改密码
     */
    public function editpwdAction(){
        $param = array(
            'uid'=>$this->uid,
            'password'=>$this->password
        );
        $userModel = new UserModel();
        $userinfo =  $userModel->getUserByUid($param['uid']);
        if(!empty($userinfo) && ($userinfo['password']==md5($param['password']))){
            return 1;
        }
        $memberModel = new MemberModel();
        return $memberModel->editMember($param);
    }

    //获取用户姓名和性别
    public function getRoomuserAction(){
        $crid = $this->crid;
        $uid = $this->uid;
        $roomuser =  $this->roomUserModel->getroomuserdetail($crid,$uid);
        if(!empty($roomuser)) {
            $logarr =array();
            $logarr['crid'] = $crid;
            $logarr['username'] = !empty($roomuser['username']) ? $roomuser['username'] : '';
            $logarr['realname'] = !empty($roomuser['cnname']) ? $roomuser['cnname'] : '';
            $logarr['sex'] = isset($roomuser['sex']) ? $roomuser['sex'] : 0;
            return $logarr;
        }
    }

    /**
     * @describe:网校学生统计查询
     * @User:tzq
     * @Date:2017/11/21
     * @param int $beginTime        查询开始时间
     * @param int $endTime          查询结束时间
     * @param int $p                当前页数
     * @param int $listRows         每页显示条数/有取传的值|取配置文件值
     * @param int $type             1 获取用户信息 2 获取统计数量
     * @param int $newlecture       讲座中心课程id
     * @param int $newtransaction   业务纵览课程id
     * @param int $newregulations   政治法规课程id
     * @param int $crid             网校id
     * @param int $attach           type为2必传参数要查询的用户uid字符串
     * @return array/false
     */
    public function getCreditCountAction(){
        $model                    = new StudyModel();
        $params['beginTime']      = $this->beginTime;
        $params['endTime']        = $this->endTime;
        $params['curr']           = $this->p;
        $params['listRows']       = $this->listRows;
        $params['newlecture']     = $this->newlecture;
        $params['newtransaction'] = $this->newtransaction;
        $params['newregulations'] = $this->newregulations;
        $params['type']           = $this->type;
        $params['crid']           = $this->crid;
        $params['attach']         = $this->attach;

        //获取分页配置
        if ($params['listRows'] <= 0) {
            $pageConfig         = Ebh()->config->get('system.page');
            $params['listRows'] = $pageConfig['listRows'];

        }
        //获取缓存数据
        $cacheKey = implodeKey($params);
        //log_message($cacheKey);
        $list = Ebh()->cache->get($cacheKey);

        if (is_array($list)) {
            //return $list;
        }


        $res = $model->getCreditCount($params);

        //将获取的数据缓存
        Ebh()->cache->set($cacheKey, $res, 300);
        return $res;

    }

    /**
     * @describe:获取网校学生统计导出表格数据
     * @User:tzq
     * @Date:2017/11/18
     * @param int $beginTime        查询开始时间
     * @param int $endTime          查询结束时间
     * @param int $newlecture       讲座课程id
     * @param int $newtransaction   业务纵览课程id
     * @param int $newregulations   政治法规课程id
     * @param int $crid             网校id
     * @return  array/false
     */
    public function outExclgetCreditCountAction(){
        $model                    = new StudyModel();
        $params['beginTime']      = $this->beginTime;
        $params['endTime']        = $this->endTime;
        $params['newlecture']     = $this->newlecture;
        $params['newtransaction'] = $this->newtransaction;
        $params['newregulations'] = $this->newregulations;
        $params['crid']           = $this->crid;
        $cacheKey                 = implodeKey($params);
        $list                     = Ebh()->cache->get($cacheKey);
        if (is_array($list)) {
           // return $list;
        }
        $list = $model->outExclgetCreditCount($params);
       Ebh()->cache->set($cacheKey, $list, 300);
        return $list;
    }
}