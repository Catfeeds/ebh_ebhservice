<?php
/**
 * 黑名单接口
 */

class BlackController extends Controller{
	protected $blackmodel;
	public function init(){
		//初始化黑名单模型对象
		$this->blackmodel = new BlackModel();
		parent::init();
	}

	public function parameterRules(){
		return array(
            'getBlackListAction' =>  array(
                'offset' => array(
                    'name' => 'offset',
                    'type' => 'int'
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int'
                )
            ),
			'delBlackAction'	=>	array(
				'bid' => array(
                    'name' => 'bid',
                    'type' => 'string',
                    'require' => true
                )
			),
			'checkUserAction' => array(
				'username' => array(
                    'name' => 'username',
                    'type' => 'string'
                ),
                'deny' => array(
                    'name' => 'deny',
                    'type' => 'string'
                )
            ),
            'addUserBlackAction' => array(
            	'school' => array(
            		'name' => 'school',
            		'type' => 'int'
            	),
            	'operid' => array(
            		'name' => 'operid',
            		'type' => 'int',
            		'require' => true
            	),
            	'username' => array(
            		'name' => 'username',
            		'type' => 'string',
            		'require' => true
            	),
            	'remark' => array(
            		'name' => 'remark',
            		'type' => 'string'
            	)
            ),
            'checkIpAction' => array(
            	'ip' => array(
            		'name' => 'ip',
            		'type' => 'string'
            	),
                'deny' => array(
                    'name' => 'deny',
                    'type' => 'string'
                )
            ),
            'addIpBlackAction' => array(
            	'operid' => array(
            		'name' => 'operid',
            		'type' => 'int',
            		'require' => true
            	),
            	'ip' => array(
            		'name' => 'ip',
            		'type' => 'string',
            		'require' => true
            	),
            	'addr' => array(
            		'name' => 'addr',
            		'type' => 'string'
            	),
            	'remark' => array(
            		'name' => 'remark',
            		'type' => 'string'
            	),
            ),
            'checkMobileAction' => array(
                'mobile' => array(
                    'name' => 'mobile',
                    'type' => 'string'
                ),
                'deny' => array(
                    'name' => 'deny',
                    'type' => 'string'
                )
            ),
            'addRegisterBlackAction' => array(
                'operid' => array(
                    'name' => 'operid',
                    'type' => 'string',
                    'require' => true
                ),
                'username' => array(
                    'name' => 'username',
                    'type' => 'string'
                ),
                'mobile' => array(
                    'name' => 'mobile',
                    'type' => 'string'
                ),
                'ip' => array(
                    'name' => 'ip',
                    'type' => 'string'
                ),
                'deny' => array(
                    'name' => 'deny',
                    'type' => 'string',
                    'require' => true
                ),
                'remark' => array(
                    'name' => 'remark',
                    'type' => 'string'
                ),
                'addr' => array(
                    'name' => 'addr',
                    'type' => 'string'
                )
            )
		);
	}

    /**
     * 获取黑名单首页
     */
    public function indexAction(){
        return $this->blackmodel->index();
    }

    /**
     * 获取总记录数
     */
    public function blackCountAction(){
        return $this->blackmodel->blackCount();
    }

	/**
	 * 获取黑名单列表
	 */
	public function getBlackListAction(){
		return $this->blackmodel->getBlackList($this->offset,$this->pagesize);
	}

	/**
	 * 更新黑名单启用状态
	 */
	public function changeStateAction(){
		return $this->blackmodel->changeState($this->bid);
	}

	/**
	 * 删除黑名单
	 */
	public function delBlackAction(){
		return $this->blackmodel->delBlack($this->bid);
	}

	/**
	 * 校验用户名是否已经存在于黑名单中
	 */
	public function checkUserAction(){
		return $this->blackmodel->checkUser($this->username,$this->deny);
	}

	/**
	 * 添加用户名黑名单
	 */
	public function addUserBlackAction(){
		//校验该用户名是否存在于ebh_users表 不存在不允许添加 
		$userModel = new UserModel();
		$user = $userModel->getUserByUsername($this->username);
        if(empty($user)){
            return -1;
        }else{
        	//存在则根据限制范围获取网校ID
        	if($this->school == 2){
        		$crid = 0;
        	}else{
                //获取所属网校的所有crid
        		$crid = $this->blackmodel->getCridByUid($user);
                if($crid == null){//如果不属于任何网校 crid设为0
                    $crid = 0;
                }
        	}
        }

        return $this->blackmodel->addUserBlack($crid,$user['uid'],$this->operid,$this->username,$this->remark);
	}

	/**
	 * 校验IP是否已经存在于黑名单中
	 */
	public function checkIpAction(){
		return $this->blackmodel->checkIp($this->ip,$this->deny);
	}

	/**
	 * 添加IP黑名单
	 */
	public function addIpBlackAction(){
		return $this->blackmodel->addIpBlack($this->operid,$this->ip,$this->addr,$this->remark);
	}

    /**
     * 校验手机号是否存在于黑名单中
     */
    public function checkMobileAction(){
        return $this->blackmodel->checkMobile($this->mobile,$this->deny);
    }

    /**
     * 添加注册黑名单
     */
    public function addRegisterBlackAction(){
        return $this->blackmodel->addRegister($this->operid,$this->username,$this->ip,$this->addr,$this->mobile,$this->deny,$this->remark);
    }

    /**
     * 获取创建网校黑名单
     */
    public function getCreateroomBlackAction(){
        return $this->blackmodel->getCreateroomBlack();
    }

    /**
     * 获取注册用户黑名单
     */
    public function getRegisterBlackAction(){
        return $this->blackmodel->getRegisterBlack();
    }

}