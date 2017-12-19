<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class UserModel{
    /**
     * 修改用户信息
     * @param type $param
     * @param type $uid
     */
    public function update($param,$uid) {
        $afrows = FALSE;    //影响行数
        $userarr = array();
        //修改user表信息
        if(!empty($param['username'])){
            $userarr['username'] = $param['username'];
        }
        if (!empty($param['password']))
            $userarr['password'] = md5($param['password']);
        if (!empty($param['mpassword']))	//md5加密后的用户密码
            $userarr['password'] = $param['mpassword'];
        if (isset($param['status']))
            $userarr['status'] = $param['status'];
        if (isset($param['balance']))
            $userarr['balance'] = $param['balance'];
        if (isset($param['realname']))
            $userarr['realname'] = $param['realname'];
        if (isset($param['nickname']))
            $userarr['nickname'] = $param['nickname'];
        if (isset($param['mysign']))
            $userarr['mysign'] = $param['mysign'];
        if (isset($param['sex']))
            $userarr['sex'] = $param['sex'];
        if (isset($param['mobile']))
            $userarr['mobile'] = $param['mobile'];
        if (isset($param['cellphone']))
            $userarr['cellphone'] = $param['cellphone'];
        if (isset($param['email']))
            $userarr['email'] = $param['email'];
        if (isset($param['citycode']))
            $userarr['citycode'] = $param['citycode'];
        if (isset($param['address']))
            $userarr['address'] = $param['address'];
        if (isset($param['face']))
            $userarr['face'] = $param['face'];
        if(!empty($param['qqopid']))
            $userarr['qqopid'] = $param['qqopid'];
        if(!empty($param['sinaopid']))
            $userarr['sinaopid'] = $param['sinaopid'];
        if(!empty($param['wxopenid']))
            $userarr['wxopenid'] = $param['wxopenid'];
        if(!empty($param['wxunionid']))
            $userarr['wxunionid'] = $param['wxunionid'];
        if(!empty($param['wxopid']))
            $userarr['wxopid'] = $param['wxopid'];

        if(!empty($param['lastlogintime']))
            $userarr['lastlogintime'] = $param['lastlogintime'];
        if(!empty($param['lastloginip']))
            $userarr['lastloginip'] = $param['lastloginip'];
        if(isset($param['allowip']))
            $userarr['allowip'] = $param['allowip'];
        $sarr = array();
        if(isset($param['logincount']))
            $sarr['logincount'] = 'logincount+1';
        $wherearr = array('uid' => $uid);
        if (!empty($userarr)) {
            $afrows = Ebh()->db->update('ebh_users', $userarr, $wherearr, $sarr);
        }
        return $afrows;
    }

    /**
     * 检测Email是否存在
     * @param $email
     * @return mixed
     */
    public function emailExists($email,$uid){
        if($uid > 0){
            $where = ' and uid !='.$uid;
            $sql = 'select 1 from ebh_users where email = \''.Ebh()->db->escape_str($email).'\' '.$where.' limit 1';
        }else{
            $sql = 'select 1 from ebh_users where email = \''.Ebh()->db->escape_str($email).'\' '.'limit 1';
        }
        
        return Ebh()->db->query($sql)->row_array();
    }
    /**
     * 检测用户是否存在
     * @param $username
     * @return mixed
     */
    public function exists($username){
        $sql = 'select `username`,`sex`,`realname`,`groupid` from ebh_users where username = \''.Ebh()->db->escape_str($username).'\' limit 1';
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 根据用户名获取用户信息
     * @param $username
     * @return mixed
     */
    public function getUserByUsername($username) {
        $sql = 'select u.uid,u.groupid,u.username,u.realname,u.sex,u.email,u.mobile,u.password from ebh_users u where u.username = \''.Ebh()->db->escape_str($username).'\'';
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 通过手机号码获取用户信息
     * @param $mobile
     * @return mixed
     */
    public function getUserByMobile($mobile) {
        $sql = 'select u.uid,u.groupid,u.username,u.realname,u.sex,u.email,u.mobile,u.password from ebh_users u where u.mobile = \''.Ebh()->db->escape_str($mobile).'\'';
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 获取用户通过用户名或者手机号码
     * @param $username
     * @return mixed
     */
    public function getUserByEmail($email){
        $sql = 'select u.uid,u.groupid,u.username,u.realname,u.sex,u.email,u.mobile,u.password from ebh_users u where u.email = \''.Ebh()->db->escape_str($email).'\' ';
        return Ebh()->db->query($sql)->row_array();
    }
    /**
     * 获取用户通过用户名或者手机号码
     * @param $username
     * @return mixed
     */
    public function getUserByUsernameOrMobile($username){
        $sql = 'select u.uid,u.groupid,u.username,u.realname,u.sex,u.email,u.mobile,u.password from ebh_users u where u.mobile = \''.Ebh()->db->escape_str($username).'\' or u.username = \''.Ebh()->db->escape_str($username).'\'';
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 用户UID获取用户信息
     * @param $uid
     * @return mixed
     */
    public function getUserById($uid) {
        $sql = 'select u.uid,u.groupid,u.username,u.realname,u.sex,u.email,u.mobile,u.password from ebh_users u where u.uid = '.intval($uid);
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 通过UID获取用户信息
     * @param $uid
     * @return mixed
     */
    public function getUserByUid($uid){
        $sql = 'select u.uid, u.username,u.realname,u.sex,u.email,u.mobile,u.face, u.groupid, u.credit,u.logincount,u.password,u.balance,u.lastloginip,u.lastlogintime,u.status,u.allowip,u.schoolname,u.mysign  from ebh_users u where u.uid = '.intval($uid).' limit 1';
        return Ebh()->db->query($sql)->row_array();
    }
	
	/*
	根据多个uid的组合字符串查询用户信息
	 * @param string $uids 以逗号分隔的uid字符串
     * @return array 
	*/
	public function getUserByUids($uids){
		$sql = 'select username,realname,face,sex,uid,groupid from ebh_users where uid in ('.$uids.')';
		return Ebh()->db->query($sql)->list_array('uid');
	}
	/*
	根据多个uid的组合字符串查询用户班级信息
	 * @param string $uids 以逗号分隔的uid字符串
     * @return array 
	*/
	public function getUserClassByUids($param){
		$sql = 'select classname,c.classid,uid from ebh_classes c join ebh_classstudents cs on c.classid=cs.classid ';
		$wherearr[]= 'uid in ('.$param['uids'].')';
		$wherearr[]= 'crid ='.$param['crid'];
		$sql.= ' where '.implode(' AND ',$wherearr);
		return Ebh()->db->query($sql)->list_array('uid');
	}

    /**
     *根据用户uid查询用户信息(支持数组)
     *
     */
    public function getUserInfoByUid($uid,$getCity=0){
        $uidArr = array();
        if(is_scalar($uid)){
            $uidArr = array($uid);
        }
        if(is_array($uid)){
            $uidArr = $uid;
        }
        $in = '('.implode(',',$uidArr).')';
        if (!$getCity) {
            $sql = 'select uid,username,realname,face,sex,groupid from ebh_users where uid in '.$in;
        } else {
            $sql = 'select c.cityname,u.uid,u.username,u.realname,u.face,u.sex,u.groupid from ebh_users u left join ebh_cities c on c.citycode=u.citycode where u.uid in '.$in;
        }
        return Ebh()->db->query($sql)->list_array();
    }


    /*
    qq,sina, wx 获取用户信息
    */
    public function openlogin ($opcode,$type,$cookietime=0) {
        if(empty($opcode))
            return FALSE;
        if($type=='sina'){
            $sql = "SELECT uid,username,password FROM ebh_users  WHERE sinaopid='$opcode'";
        }elseif($type=='wx'){
            $sql = "SELECT uid,username,password FROM ebh_users  WHERE wxunionid='$opcode'";
        }else{
            $sql = "SELECT uid,username,password FROM ebh_users  WHERE qqopid='$opcode'";
        }
        $data = Ebh()->db->query($sql)->row_array();
        if($data){
            return $data;
        }else{
            return false;
        }
    }


    /**
     *根据微信openid获取用户信息
     */
    public function getUserByWxOpenid($wxopid = 0){
        $sql = 'select u.* from ebh_users u where ( wxopid = \''.$wxopid.'\' ) OR (wxopenid = \''.$wxopid.'\' )  OR (wxunionid  = \''.$wxopid.'\')  limit 1';
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 获取包含多个用户的数组
     * @param  array $uid_array uid数组
     * @return array            用户数组
     */
    public function getuserarray($uid_array) {
        $user_array = array();
        if (!empty($uid_array) && is_array($uid_array))
        {
            $uid_array = array_unique($uid_array);
            $sql = 'SELECT uid,username,realname,email,face FROM ebh_users WHERE uid IN(' . implode(',', $uid_array) . ')';
            $row = Ebh()->db->query($sql)->list_array();
            foreach ($row as $v)
            {
                $user_array[$v['uid']] = array('username' => $v['username'], 'face' => $v['face'], 'realname' => $v['realname'],'email' => $v['email']);
            }
        }
        return $user_array;
    }
}