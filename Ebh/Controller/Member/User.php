<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class UserController extends Controller{
    public $userModel;
    public $memberModel;
    public function init(){
        parent::init();
        $this->userModel = new UserModel();
        $this->memberModel = new MemberModel();

    }

    public function parameterRules(){
        return array(
            'addAction'   =>  array(
                'username'  =>  array('name'=>'username','require'=>true),
                'password'  =>  array('name'=>'password','require'=>true),
                'realname'  =>  array('name'=>'realname','require'=>false,'default'=>''),
                'sex'  =>  array('name'=>'sex','type'=>'int','default'=>0),
                'birthdate'  =>  array('name'=>'birthdate','require'=>false,'default'=>0),
                'mobile'  =>  array('name'=>'mobile','require'=>false),
                'email'  =>  array('name'=>'email','require'=>false),
            ),
            'updateAction'   =>  array(
                'uid'  =>  array('name'=>'uid','type'=>'int','require'=>true),
                'password'  =>  array('name'=>'password'),
                'nickname'  =>  array('name'=>'nickname'),
                'realname'  =>  array('name'=>'realname'),
                'email'  =>  array('name'=>'email'),
                'face'  =>  array('name'=>'face'),
                'sex'  =>  array('name'=>'sex'),
                'mysign'  =>  array('name'=>'mysign'),
            ),
            'existsAction'   =>  array(
                'username'  =>  array('name'=>'username','require'=>true),
            ),
            'emailExistsAction'   =>  array(
                'uid'  =>  array('name'=>'uid','type'=>'int','default'=>0),
                'email'  =>  array('name'=>'email','require'=>true),
            ),
            'getUserByUsernameAction'   =>  array(
                'username'  =>  array('name'=>'username','require'=>true),
            ),
            'getUserByUidAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
            ),
            'getUserByMobileAction'   =>  array(
                'mobile'  =>  array('name'=>'mobile','require'=>true),
            ),
            'getUserByUserameOrMobileAction'   =>  array(
                'username'  =>  array('name'=>'username','require'=>true),
            ),
            'getUserByEmailAction'   =>  array(
                'email'  =>  array('name'=>'email','require'=>true),
            ),
			'signAction' =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'ip'  =>  array('name'=>'ip','default'=>'','type'=>'string'),
                'citycode'  =>  array('name'=>'citycode','default'=>0,'type'=>'int'),
                'parentcode'  =>  array('name'=>'parentcode','default'=>0,'type'=>'int'),
                'credit'    =>   array('name'=>'credit','default'=>0,'type'=>'int'),  //是否添加积分
            ),
			'signListAction' =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'starttime'  =>  array('name'=>'starttime','default'=>0,'type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','default'=>0,'type'=>'int'),
                'byday'  =>  array('name'=>'byday','default'=>0,'type'=>'int'),
                'citycode'  =>  array('name'=>'citycode','default'=>0,'type'=>'int'),
                'parentcode'  =>  array('name'=>'parentcode','default'=>0,'type'=>'int'),
            ),
			'folderCreditAction'=>array(
				'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
			),
            'getUserByUidsAction'   =>  array(
                'uids'  =>  array('name'=>'uids','require'=>true,'type'=>'array')
            ),
            'getUserExtendInfoAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>0),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>0)
            ),
            'getStudentClassIdAction' => array(
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            )
        );
    }




    /**
     * 新增会员
     * @return mixed
     */
    public function addAction(){
        $parameters = array();
        $parameters['username'] = $this->username;
        $parameters['password'] = $this->password;
        $parameters['realname'] = $this->realname;
        $parameters['sex'] = $this->sex;
        $parameters['birthdate'] = $this->birthdate;
        $parameters['mobile'] = $this->mobile;
        $parameters['email'] = $this->email;
        $parameters['dateline'] = time();


        if($this->userModel->getUserByUsernameOrMobile($this->username)){
            return array(
                'status'  =>  0,
                'msg'   =>'该用户名已存在'
            );
        }

        if(!empty($this->mobile) && $this->getUserByMobileAction($this->mobile)){
            return array(
                'status'  =>  0,
                'msg'   =>'该手机号码已存在'
            );
        }
        $rs = $this->memberModel->addMember($parameters);

        if($rs > 0 && !empty($this->mobile)){
            //添加成功后 绑定用户手机信息
            $bdModel = new BindModel();
            $bdata =array(
                'uid'=>$rs,
                'is_mobile'=>1,
                'mobile'=>$this->mobile,
                'mobile_str'=>json_encode(
                    array('mobile'=>$this->mobile,
                        'uid'=>$rs,
                        'dateline'=>SYSTIME
                    )
                )
            );
            $bdModel->doBind($bdata,$rs);
        }
        return array(
            'status'    =>  intval($rs)
        );
    }

    /**
     * 更新用户信息
     * @return array
     */
    public function updateAction(){
        $parameters['uid'] = $this->uid;
        if(!is_null($this->password)){
            $parameters['password'] = $this->password;
        }
        if(!is_null($this->realname)){
            $parameters['cnname'] = $this->realname;
            $parameters['realname'] = $this->realname;
        }
        if(!is_null($this->nickname)){
            $parameters['nickname'] = $this->nickname;
        }
        if(!is_null($this->sex)){
            $parameters['sex'] = $this->sex;
        }
        if(!is_null($this->mysign)){
            $parameters['mysign'] = $this->mysign;
        }
        if(!is_null($this->face)){
            $parameters['face'] = $this->face;
        }

        if(!is_null($this->email)){
            $parameters['email'] = $this->email;
            if($this->userModel->emailExists($this->email,$this->uid)){
                return returnData(0,'该邮箱已存在');
            }
        }

        $rs = $this->memberModel->editMember($parameters);
        if($rs !== false){
            return returnData(1);
        }else{
            return returnData(0,'修改用户信息失败');
        }

    }

    /**
     * 判断Email是否存在
     * @return mixed
     */
    public function emailExistsAction(){
        return $this->userModel->emailExists($this->email,$this->uid);
    }
    /**
     * 判断用户是否存在
     * @return array
     */
    public function existsAction(){
        return $this->userModel->exists($this->username);
    }

    /**
     * 通过用户名获取用户信息
     * @return array
     */
    public function getUserByUsernameAction(){
        return $this->userModel->getUserByUsername($this->username);
    }

    /**
     * 通过手机号码获取用户信息
     * @return mixed
     */
    public function getUserByMobileAction(){
        return $this->userModel->getUserByMobile($this->mobile);
    }

    /**
     * 通过UID获取用户信息
     * @return mixed
     */
    public function getUserByUidAction(){
        return $this->userModel->getUserByUid($this->uid);
    }

    /**
     * 通过邮箱获取用户信息
     * @return mixed
     */
    public function getUserByEmailAction(){
        return $this->userModel->getUserByEmail($this->email);
    }

    /**
     * 通过用户名或者手机号码获取用户信息
     * @return mixed
     */
    public function getUserByUserameOrMobileAction(){
        return $this->userModel->getUserByUsernameOrMobile($this->username);
    }


	/*
	 *签到
	*/
	public function signAction(){
		$param['uid'] = $this->uid;
		$param['crid'] = $this->crid;
		$param['ip'] = $this->ip;
		if($this->citycode == 0 && $this->parentcode == 0){
            $iPaddressLib = new IPaddress();
            $address = $iPaddressLib->find($param['ip']);
            $llModel = new LoginlogModel();
            if(!empty($address) && $address[0] == '中国'){
                $cityname = empty($address[2])?$address[1]:$address[2];
                $city = $llModel->getCityByName($cityname);
                if(!empty($city)){
                    $spcityarr = array('11','12','31','50');
                    if(in_array($city['citycode'],$spcityarr)){//直辖市,编号加01
                        $city['citycode'] = $city['citycode'].'01';
                    }
                    //同时记录省市编号
                    $param['citycode'] = $city['citycode'];
                    $param['parentcode'] = substr($city['citycode'],0,2);
                }
            }
        }else{
            $param['citycode'] = $this->citycode;
            $param['parentcode'] = $this->parentcode;
        }
        if($this->credit > 0 ){
            $creditModel = new CreditModel();
            $creditModel->addCreditlog(array('ruleid'=>22,'uid'=>$this->uid,'crid'=>$this->crid));
        }

		$signlogmodel = new SignLogModel();
		$signlogmodel->addLog($param);
	}
	
	/*
	 *签到列表
	*/
	public function signListAction(){
		$param['uid'] = $this->uid;
		$param['crid'] = $this->crid;
		$param['starttime'] = $this->starttime;
		$param['endtime'] = $this->endtime;
		$param['byday'] = $this->byday;
		$param['citycode'] = $this->citycode;
		$param['parentcode'] = $this->parentcode;
		$signlogmodel = new SignLogModel();
		return $signlogmodel->getSignList($param);
	}
	
	/*
	 *学分,学时
	 */
	public function folderCreditAction(){
		//学时
		$plmodel = new PlayLogModel();
		$ltime = $plmodel->getTimeByCrid(array('uid'=>$this->uid,'crid'=>$this->crid));
		$ltime = round(intval($ltime)/3600,1);
		
		$rcmodel = new CoursewareModel();
		//有设置学分的课程. 课件数量列表
		$cwcountlist = $rcmodel->getCountForFolderCredit($this->crid);
		$folderids = array_column($cwcountlist,'folderid');
		$folderids = implode(',',$folderids);
		
		$studylist = $plmodel->getTimeForForFolderCredit(array('uid'=>$this->uid,'folderids'=>$folderids));
		
		$foldercredit = 0;
		foreach($cwcountlist as $folder){
			$tempcredit = 0;
			if($folder['creditmode'] == 0){//按照课件和作业完成度,暂时不计作业
				$finishedcount = empty($studylist[$folder['folderid']])?0:$studylist[$folder['folderid']]['finishedcount'];
				$rulearr = explode(':',$folder['creditrule']);
				$coursepercent = 100 - (empty($rulearr[1])?0:$rulearr[1]);//课件占比
				
				$tempcredit = $finishedcount/$folder['cwcount']*$folder['credit']*$coursepercent/100;
				
			} else {//按照课件学习累计时长获取
				$tltime = empty($studylist[$folder['folderid']])?0:$studylist[$folder['folderid']]['ltime'];
				$tempcredit = (empty($folder['credittime'])?1:($tltime/$folder['credittime']>1?1:$tltime/$folder['credittime']))*$folder['credit'];
				// var_dump($tempcredit);
			}
			$foldercredit+= $tempcredit;
		}
		return array('ltime'=>$ltime,'foldercredit'=>round($foldercredit,1));
	}

	public function getUserByUidsAction(){
	    $userList = $this->userModel->getUserByUids(implode(',',$this->uids));

	    return returnData(1,'',$userList);
    }

    /**
     * 获取用户扩展信息
     */
    public function getUserExtendInfoAction(){
        $parameters['crid'] = $this->crid;
        $parameters['uid'] = $this->uid;
        $parameters['normal'] = 1;
        $playlogMoldel = new PlayLogModel();

        $total = $playlogMoldel->getCourseCountByUid($parameters);

        $result['has_study'] = $total;


        $scoresum = runAction('Classroom/Score/getUserSum',array('uid'=>$this->uid,'crid'=>$this->crid));
        $result['scoresum'] = isset($scoresum['scores']) ? $scoresum['scores'] : '0';
        $foldercredit = runAction('Member/User/folderCredit',array('uid'=>$this->uid,'crid'=>$this->crid));
        $result['ltime'] = isset($foldercredit['ltime']) ? $foldercredit['ltime'] : 0;


        //获取用户模块信息
        $userModel = new UserModel();
        $user = $userModel->getUserByUid($this->uid);
        if($user['groupid'] == 6){
            $tor = 0;
        }else{
            $tor = 1;
        }
        $modules = runAction('Aroomv3/Module/modules',array('tor'=>$tor,'crid'=>$this->crid));
        $result['modules'] = $modules !== false ? $modules : array();
        $result['appmodules'] = Ebh()->config->get('modules.app');
        return $result;
    }

    /**
     * 获取学生所在的班级ID
     * @return bool
     */
    public function getStudentClassIdAction() {
        $model = new ClassstudentsModel();
        return $model->getClassIdByUid($this->uid, $this->crid);
    }
}