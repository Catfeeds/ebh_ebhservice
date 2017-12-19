<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class MemberModel{
    /*
	添加会员
	@param array $param
	@return int
	*/
    public function addMember($param){
        if(!empty($param['username']))
            $userarr['username'] = $param['username'];
        if(!empty($param['password']))
            $userarr['password'] = md5($param['password']);
        if (!empty($param['mpassword']))	//md5加密后的用户密码
            $userarr['password'] = $param['mpassword'];
        if(isset($param['realname']))
            $userarr['realname'] = $param['realname'];
        if(isset($param['nickname']))
            $userarr['nickname'] = $param['nickname'];
        if(!empty($param['dateline']))
            $userarr['dateline'] = $param['dateline'];
        if(isset($param['sex']))
            $userarr['sex'] = $param['sex'];
        if(!empty($param['mobile']))
            $userarr['mobile'] = $param['mobile'];
        if(!empty($param['citycode']))
            $userarr['citycode'] = $param['citycode'];
        if(isset($param['address']))
            $userarr['address'] = $param['address'];
        if(!empty($param['email']))
            $userarr['email'] = $param['email'];
        if(!empty($param['face']))
            $userarr['face'] = $param['face'];
        if(!empty($param['qqopid']))
            $userarr['qqopid'] = $param['qqopid'];
        if(!empty($param['sinaopid']))
            $userarr['sinaopid'] = $param['sinaopid'];

        if(!empty($param['wxopenid']))
            $userarr['wxopenid'] = $param['wxopenid'];

        if(!empty($param['schoolname']))
            $userarr['schoolname'] = $param['schoolname'];
        $userarr['status'] = 1;
        $userarr['groupid'] = 6;
        // var_dump($userarr);
        $uid = Ebh()->db->insert('ebh_users',$userarr);
        if($uid){
            $memberarr['memberid'] = $uid;
            if(isset($param['realname']))
                $memberarr['realname'] = $param['realname'];
            if(isset($param['nickname']))
                $memberarr['nickname'] = $param['nickname'];
            if(isset($param['sex']))
                $memberarr['sex'] = $param['sex'];
            if(!empty($param['birthdate']))
                $memberarr['birthdate'] = $param['birthdate'];
            if(!empty($param['phone']))
                $memberarr['phone'] = $param['phone'];
            if(!empty($param['mobile']))
                $memberarr['mobile'] = $param['mobile'];
            if(!empty($param['native']))
                $memberarr['native'] = $param['native'];
            if(!empty($param['citycode']))
                $memberarr['citycode'] = $param['citycode'];
            if(isset($param['address']))
                $memberarr['address'] = $param['address'];
            if(!empty($param['msn']))
                $memberarr['msn'] = $param['msn'];
            if(!empty($param['qq']))
                $memberarr['qq'] = $param['qq'];
            if(!empty($param['email']))
                $memberarr['email'] = $param['email'];
            if(!empty($param['face']))
                $memberarr['face'] = $param['face'];
            if(isset($param['profile']))
                $memberarr['profile'] = $param['profile'];
            $memberid = Ebh()->db->insert('ebh_members',$memberarr);
            // var_dump($uid.'___'.$memberid.'````');

        }
        return $uid;
    }



    /*
	修改会员
	@param array $param
	@return int
	*/
    public function editMember($param){
        $afrows=0;
        //修改user表信息
        if(!empty($param['password']))
            $userarr['password'] = md5($param['password']);
        if(isset($param['status']))
            $userarr['status'] = $param['status'];
        if(isset($param['cnname']))
            $userarr['realname'] = $param['cnname'];
        if(isset($param['nickname']))
            $userarr['nickname'] = $param['nickname'];
        if(isset($param['sex']))
            $userarr['sex'] = $param['sex'];
        if(isset($param['mysign']))
            $userarr['mysign'] = $param['mysign'];
        if(isset($param['mobile']))
            $userarr['mobile'] = $param['mobile'];
        if(isset($param['email']))
            $userarr['email'] = $param['email'];
        if(isset($param['citycode']))
            $userarr['citycode'] = $param['citycode'];
        if(isset($param['address']))
            $userarr['address'] = $param['address'];
        if(isset($param['face']))
            $userarr['face'] = $param['face'];
        if(isset($param['lastlogintime']))
            $userarr['lastlogintime'] = $param['lastlogintime'];
        $wherearr = array('uid'=>$param['uid']);
        if (!empty($userarr)) {
            $afrows+= Ebh()->db->update('ebh_users', $userarr, $wherearr);
        }
        //修改member表信息

        if(isset($param['birthdate']))
            $memberarr['birthdate'] = $param['birthdate'];
        if(isset($param['phone']))
            $memberarr['phone'] = $param['phone'];
        if(isset($param['qq']))
            $memberarr['qq'] = $param['qq'];
        if(isset($param['msn']))
            $memberarr['msn'] = $param['msn'];
        if(isset($param['native']))
            $memberarr['native'] = $param['native'];
        if(isset($param['profile']))
            $memberarr['profile'] = $param['profile'];
        if(isset($param['realname']))
            $memberarr['realname'] = $param['realname'];
        if(isset($param['nickname']))
            $memberarr['nickname'] = $param['nickname'];
        if(isset($param['sex']))
            $memberarr['sex'] = $param['sex'];
        if(isset($param['mobile']))
            $memberarr['mobile'] = $param['mobile'];
        if(isset($param['email']))
            $memberarr['email'] = $param['email'];
        if(isset($param['citycode']))
            $memberarr['citycode'] = $param['citycode'];
        if(isset($param['address']))
            $memberarr['address'] = $param['address'];
        if(isset($param['familyname']))
            $memberarr['familyname'] = $param['familyname'];
        if(isset($param['familyphone']))
            $memberarr['familyphone'] = $param['familyphone'];
        if(isset($param['familyjob']))
            $memberarr['familyjob'] = $param['familyjob'];
        if(isset($param['familyemail']))
            $memberarr['familyemail'] = $param['familyemail'];
        if(isset($param['hobbies']))
            $memberarr['hobbies'] = $param['hobbies'];
        if(isset($param['lovemusic']))
            $memberarr['lovemusic'] = $param['lovemusic'];
        if(isset($param['lovemovies']))
            $memberarr['lovemovies'] = $param['lovemovies'];
        if(isset($param['lovegames']))
            $memberarr['lovegames'] = $param['lovegames'];
        if(isset($param['lovecomics']))
            $memberarr['lovecomics'] = $param['lovecomics'];
        if(isset($param['lovesports']))
            $memberarr['lovesports'] = $param['lovesports'];
        if(isset($param['lovebooks']))
            $memberarr['lovebooks'] = $param['lovebooks'];

        $wherearr = array('memberid'=>$param['uid']);
        if (!empty($memberarr)) {
            $afrows+= Ebh()->db->update('ebh_members', $memberarr, $wherearr);
        }
        return $afrows;
    }
}