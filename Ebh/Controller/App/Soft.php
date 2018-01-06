<?php
/**
 * 软件注册，验证.
 * User: ckx
 */
class SoftController extends Controller{
	private $crid;//intro.ebh.net
	public function __construct(){
        parent::init();
		$this->crid = getConfig('weikedashi');
    }
    public function parameterRules(){
        return array(
            'registerAction'   =>  array(
                'username'=>array('name'=>'username','require'=>TRUE,'type'=>'string'),
                'password'=>array('name'=>'password','require'=>TRUE,'type'=>'string'),
                'company'=>array('name'=>'company','require'=>FALSE,'type'=>'string'),
                'realname'=>array('name'=>'realname','require'=>FALSE,'type'=>'string'),
                'mobile'=>array('name'=>'mobile','require'=>FALSE,'type'=>'string'),
                'email'=>array('name'=>'email','require'=>FALSE,'type'=>'string'),
                'mac'=>array('name'=>'mac','require'=>TRUE,'type'=>'string'),
                'system'=>array('name'=>'system','require'=>TRUE,'type'=>'string'),
                'ver'=>array('name'=>'ver','require'=>TRUE,'type'=>'string'),
                'screen'=>array('name'=>'screen','require'=>TRUE,'type'=>'string'),
                'ip'=>array('name'=>'ip','require'=>TRUE,'type'=>'string'),
            ),
			'validAction'   =>  array(
                'username'=>array('name'=>'username','require'=>TRUE,'type'=>'string'),
                'password'=>array('name'=>'password','require'=>TRUE,'type'=>'string'),
                'mac'=>array('name'=>'mac','require'=>TRUE,'type'=>'string'),
                'system'=>array('name'=>'system','require'=>TRUE,'type'=>'string'),
                'ver'=>array('name'=>'ver','require'=>TRUE,'type'=>'string'),
                'screen'=>array('name'=>'screen','require'=>TRUE,'type'=>'string'),
                'ip'=>array('name'=>'ip','require'=>TRUE,'type'=>'string'),
            ),
        );
    }

    /**
     * 微课大师注册
     * @return mixed
     */
    public function registerAction(){
        $param['username'] = trim($this->username);
        $param['password'] = md5(trim($this->password));
		$crid = $this->crid;
		$param['crid'] = $crid;
		$rumodel = new RoomUserModel();
		// return $param;
		$uid = $rumodel->verifyRoomUserByPassword($param);
		if(empty($uid)){//用户不在该网校
			return array('status'=>401,'msg'=>'账号验证失败');
		}
		
		//查询已有设备记录,该设备记录
		$ucmodel = new UserClientModel();
		$clientcount = $ucmodel->getClientCountByUid($uid,$crid,$this->mac,$this->system);
		if(!empty($clinetcount)){
			return array('status'=>500,'msg'=>'设备授权查询失败');
		}
		$curtime = microtime(TRUE);
		$param['dateline'] = intval($curtime);
		$extradata = array('schoolname'=>$this->company,
										'realname'=>$this->realname,
										'mobile'=>$this->mobile,
										'email'=>$this->email
										);
		$param['extradata'] = serialize($extradata);
		$logtype = 8;//登录日志类型：微课大师验证
		if(empty($clientcount['countreg'])){//没有在该设备注册
			$logtype = 7;//登录日志类型：微课大师注册
			//限制人数查询
			$settingmodel = new SystemSettingModel();
			$settings = $settingmodel->getOtherSetting($crid);
			if(!empty($settings['limitnum']) && $clientcount['count']>=$settings['limitnum']){//超过限制
				return array('status'=>405,'msg'=>'设备限制');
			}
			$param['ip'] = $this->ip;
			
			$param['lasttime'] = $curtime;
			$param['uid'] = $uid;
			$param['mac'] = $this->mac;
			$param['system'] = $this->system;
			

			// $param['broversion'] = $this->ver;
			// $param['browser'] = '微课大师';
			$param['screen'] = $this->screen;
			
			$clientid = $ucmodel->add($param);
			if(empty($clientid)){//失败
				return array('status'=>401,'msg'=>'授权失败');
			}
			
		}
		
		//更新用户信息
		$usermodel = new UserModel();
		$usermodel->update($extradata,$uid);
		//验证（登录日志处理）
		$this->validAction($uid,$logtype);
		return array('status'=>0,'timestamp'=>$param['dateline']);
    }
	
	
	 /**
     * 微课大师验证
     * @return mixed
     */
    public function validAction($uid=0,$logtype=8){
        $param['username'] = trim($this->username);
        $param['password'] = md5(trim($this->password));
		$crid = $this->crid;
		$param['crid'] = $crid;
		$rumodel = new RoomUserModel();
		// return $param;
		
		if(empty($uid)){
			$uid = $rumodel->verifyRoomUserByPassword($param);
			if(empty($uid)){//用户不在该网校
				return array('status'=>401,'msg'=>'账号验证失败');
			}
			$ucmodel = new UserClientModel();
			$clientcount = $ucmodel->getClientCountByUid($uid,$crid,$this->mac,$this->system);
			if(empty($clientcount['countreg'])){//没有注册过
				return array('status'=>401,'msg'=>'账号验证失败');
			}
		}
		
		$param['ip'] = $this->ip;
		$param['dateline'] = SYSTIME;
		$param['uid'] = $uid;
		$param['mac'] = $this->mac;
		$param['system'] = $this->system;
        $param['systemversion'] = $this->ver;
        // $param['browser'] = '微课大师';
        $param['screen'] = $this->screen;
		$param['logtype'] = $logtype;
		
		$loginLogModel = new LoginlogModel();
		//查询citycode
		$iplib = new IPaddress();
		$address = $iplib->find($param['ip']);//101.69.252.186
		if(!empty($address) && $address[0] == '中国'){
			$cityname = empty($address[2])?$address[1]:$address[2];
			$city = $loginLogModel->getCityByName($cityname);
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
		//查询网络提供商
		$ispmodel = new IspModel();
		$isp = $ispmodel->getIspType($param['ip']);
		if(!empty($isp)){
			$param['isp'] = $isp;
		}
		$lastlogin = $loginLogModel->lastLogin(array('uid'=>$uid,'crid'=>$crid));
		$loginLogModel->add($param);
		$ret = array('lasttime'=>$lastlogin['dateline'],'timestamp'=>$param['dateline'],'lastip'=>$lastlogin['ip']);
		return array('status'=>0,'ret'=>$ret);
    }
}