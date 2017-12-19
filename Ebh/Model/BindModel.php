<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:37
 */
class BindModel{

    /**
     * 添加绑定
     * @param unknown $param
     */
    public function add($param){
        $setarr = array();
        if(empty($param))
            return false;
        if(!empty( $param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(isset($param['mobile'])){
            $setarr['mobile'] = $param['mobile'];
        }
        if(isset($param['is_mobile'])){
            $setarr['is_mobile'] = $param['is_mobile'];
        }
        if(!empty( $param['mobile_str'])){
            $setarr['mobile_str'] = $param['mobile_str'];
        }
        if(isset($param['is_email'])){
            $setarr['is_email'] = $param['is_email'];
        }
        if(!empty( $param['email_str'])){
            $setarr['email_str'] = $param['email_str'];
        }
        if(isset($param['is_qq'])){
            $setarr['is_qq'] = $param['is_qq'];
        }
        if(!empty( $param['qq_str'])){
            $setarr['qq_str'] = $param['qq_str'];
        }
        if(isset($param['is_wx'])){
            $setarr['is_wx'] = $param['is_wx'];
        }
        if(!empty( $param['wx_str'])){
            $setarr['wx_str'] = $param['wx_str'];
        }
        if(isset($param['is_weibo'])){
            $setarr['is_weibo'] = $param['is_weibo'];
        }
        if(!empty( $param['weibo_str'])){
            $setarr['weibo_str'] = $param['weibo_str'];
        }
        if(isset($param['is_paypass'])){
            $setarr['is_paypass'] = $param['is_paypass'];
        }
        if(!empty( $param['paypass_str'])){
            $setarr['paypass_str'] = $param['paypass_str'];
        }
        if(isset($param['is_bank'])){
            $setarr['is_bank'] = $param['is_bank'];
        }
        if(isset( $param['bank_str'])){
            $setarr['bank_str'] = $param['bank_str'];
        }
        return Ebh()->db->insert('ebh_binds',$setarr);
    }

    /**
     * 修改
     * @param unknown $param
     * @param unknown $uid
     * @return boolean
     */
    public function update($param,$uid){
        $setarr = array();
        if(empty($param)||$uid<0||!is_numeric($uid))
            return false;
        if(isset($param['mobile'])){
            $setarr['mobile'] = $param['mobile'];
        }
        if(isset($param['is_mobile'])){
            $setarr['is_mobile'] = $param['is_mobile'];
        }
        if(!empty( $param['mobile_str'])){
            $setarr['mobile_str'] = $param['mobile_str'];
        }
        if(isset($param['is_email'])){
            $setarr['is_email'] = $param['is_email'];
        }
        if(!empty( $param['email_str'])){
            $setarr['email_str'] = $param['email_str'];
        }
        if(isset($param['is_qq'])){
            $setarr['is_qq'] = $param['is_qq'];
        }
        if(isset( $param['qq_str'])){
            $setarr['qq_str'] = $param['qq_str'];
        }
        if(isset($param['is_wx'])){
            $setarr['is_wx'] = $param['is_wx'];
        }
        if(isset( $param['wx_str'])){
            $setarr['wx_str'] = $param['wx_str'];
        }
        if(isset($param['is_weibo'])){
            $setarr['is_weibo'] = $param['is_weibo'];
        }
        if(isset( $param['weibo_str'])){
            $setarr['weibo_str'] = $param['weibo_str'];
        }
        if(isset($param['is_paypass'])){
            $setarr['is_paypass'] = $param['is_paypass'];
        }
        if(!empty( $param['paypass_str'])){
            $setarr['paypass_str'] = $param['paypass_str'];
        }
        if(isset($param['is_bank'])){
            $setarr['is_bank'] = $param['is_bank'];
        }
        if(isset( $param['bank_str'])){
            $setarr['bank_str'] = $param['bank_str'];
        }
        return Ebh()->db->update('ebh_binds',$setarr,array('uid'=>$uid));
    }
    /**
     * 查找一个用户的绑定信息
     * @param unknown $uid
     */
    public function getUserBInd($uid){
        $sql = "select u.username,u.realname,u.qqopid,u.sinaopid,u.email,u.mobile,u.wxopenid,b.* from ebh_users u 
					left join ebh_binds b on b.uid = u.uid 
				where u.uid = $uid
				";
        //echo $sql;
        $row = Ebh()->db->query($sql)->row_array();
        return $row;
    }
	
	/*
	多个网校管理员绑定信息，结算发短信用
	*/
	public function getCrBindList($param){
		if(empty($param['crids']))
			return FALSE;
		$sql = 'select cr.crid,cr.crname,cr.uid,b.mobile from ebh_classrooms cr
				join ebh_binds b on cr.uid = b.uid';
		$sql.= ' where cr.crid in('.$param['crids'].')';
		return Ebh()->db->query($sql)->list_array('crid');
	}

    /**
     * 绑定处理
     */
    public function doBind($param,$uid){
        $ret =  false;
        if(empty($param)||$uid<0||!is_numeric($uid))
            return false;
        $row = $this->getUserBInd($uid);
        //log_message(var_export($row,true));
        if(!empty($row['bid'])){//修改
            $ret = $this->update($param, $uid);
        }else{//添加
            $ret = $this->add($param);
        }
        return $ret;
    }

    /**
     * 解绑处理
     */
    public function doUnbind($type,$uid){
        $ret =  false;
        if($type=='qq'){//qq解绑
            $param = array(
                'is_qq'=>0,
                'qq_str'=>''
            );
            $ret = $this->update($param, $uid);
            //删除openid
            if($ret){
                Ebh()->db->update("ebh_users",array('qqopid'=>''),array('uid'=>$uid));
            }
        }elseif($type=='wx'){
            $param = array(
                'is_wx'=>0,
                'wx_str'=>''
            );
            $ret = $this->update($param, $uid);
            //删除openid
            if($ret){
                Ebh()->db->update("ebh_users",array('wxopenid'=>'','wxopid'=>'','wxunionid'=>''),array('uid'=>$uid));
            }

        }elseif($type=='weibo'){
            $param = array(
                'is_weibo'=>0,
                'weibo_str'=>''
            );
            $ret = $this->update($param, $uid);
            //删除openid
            if($ret){
                Ebh()->db->update("ebh_users",array('sinaopid'=>''),array('uid'=>$uid));
            }

        }

        return $ret;
    }

    /**
     * 验证邮箱是否已经使用了
     */
    public function checkemail($eamil){
        $sql = "select count(*) as count from ebh_users u left join ebh_binds b on u.uid = b.uid where b.is_email = 1 and u.email = '{$eamil}'";
        $row = Ebh()->db->query($sql)->row_array();
        if($row['count']>0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 验证手机号 是否已经绑定了
     */
    public function checkmobile($mobile){

        $sql = "select count(*) as count from ebh_users where mobile = '{$mobile}'";
        $row = Ebh()->db->query($sql)->row_array();
        if($row['count']>0){
            return true;
        }
        //$sql = "select count(*) as count from ebh_users u left join ebh_binds b on u.uid = b.uid where b.is_mobile = 1 and u.mobile = '{$mobile}'";
        $sql = "select count(*) as count from ebh_binds where is_mobile = 1 and mobile = '{$mobile}'";
        $row = Ebh()->db->query($sql)->row_array();
        if($row['count']>0){
            return true;
        }else{
            return false;
        }
    }
}