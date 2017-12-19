<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 16:55
 */
class SnsBaseinfoModel{

    public function __construct(){
        $this->db = Ebh()->snsdb;
    }



    //添加一条
    public function addone($param){
        $setarr = array();
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(!empty($param['followsnum'])){
            $setarr['followsnum'] = $param['followsnum'];
        }
        if(!empty($param['fansnum'])){
            $setarr['fansnum'] = $param['fansnum'];
        }
        if(!empty($param['viewsnum'])){
            $setarr['viewsnum'] = $param['viewsnum'];
        }
        if(!empty($param['cover'])){
            $setarr['cover'] = $param['cover'];
        }
        if(isset($param['crids'])){
            $setarr['crids'] = $param['crids'];
        }
        if(isset($param['feedsnum'])){
            $setarr['feedsnum'] = $param['feedsnum'];
        }
        if(isset($param['blogsnum'])){
            $setarr['blogsnum'] = $param['blogsnum'];
        }
        return $this->db->insert("ebh_sns_baseinfos",$setarr);
    }

    //修改一条
    public function updateone($param ,$uid, $sparam=array()){
        $setarr = array();
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(!empty($param['followsnum'])){
            $setarr['followsnum'] = $param['followsnum'];
        }
        if(!empty($param['fansnum'])){
            $setarr['fansnum'] = $param['fansnum'];
        }
        if(!empty($param['viewsnum'])){
            $setarr['viewsnum'] = $param['viewsnum'];
        }
        if(!empty($param['feedsnum'])){
            $setarr['feedsnum'] = $param['feedsnum'];
        }
        if(!empty($param['blogsnum'])){
            $setarr['blogsnum'] = $param['blogsnum'];
        }
        if(isset($param['cover'])){
            $setarr['cover'] = $param['cover'];
        }
        if(isset($param['crids'])){
            $setarr['crids'] = $param['crids'];
        }
        if(!empty($param['status'])){
            $setarr['status'] = $param['status'];
        }
        return $this->db->update("ebh_sns_baseinfos",$setarr,array('uid'=>$uid),$sparam);
    }
    /**
     * 获取用户的网校id
     */
    public function getUserCrid($uid){
        //获取
        $sql = "select crids from ebh_sns_baseinfos where uid = $uid ";
        $crids = $this->db->query($sql)->row_array();
        if(!empty($crids['crids'])){
            return explode(",", $crids['crids']);
        }
        return false;
    }

    /**
     * 获取用户的班级id
     */
    public function getUserClassId($uid){
        $sql = "select utype from ebh_sns_baseinfos where uid = $uid";
        $row = $this->db->query($sql)->row_array();
        if($row['utype'] == 5){
            $sql = "select classid from ebh_classteachers where uid = $uid";
        }else{
            $sql = "select classid from ebh_classstudents where uid = $uid";
        }
        $classes =  Ebh()->db->query($sql)->list_array();
        $retarr = array();
        if(!empty($classes[0])){
            $retarr = array_map(function($arr){return $arr['classid'];}, $classes);
        }
        return $retarr;
    }


    /**
     * 获取用户基本信息
     * 包含有,个人简介,头像,性别,关注,粉丝等
     *
     */
    public function getUserinfo($users,$keys="uid"){
        if(empty($users[0])) return false;
        $uidarr = array();
        foreach($users as $user){
            array_push($uidarr, $user[$keys]);
        }
        $sql = "select u.uid,u.username,u.balance,u.realname,u.nickname,u.sex,u.face,u.credit,u.mysign,u.groupid, m.hobbies,m.profile from ebh_users u left join ebh_members m on m.memberid = u.uid where u.uid in ( ".implode(",",$uidarr)." )";
        $infots = Ebh()->db->query($sql)->list_array();
        $sql2 = "select uid,followsnum,fansnum,viewsnum,feedsnum,blogsnum,cover,crids,nzcount,npcount,ngcount,nfcount from ebh_sns_baseinfos where uid in(".implode(",",$uidarr).") ";
        $infos = $this->db->query($sql2)->list_array();

        foreach($users as $key=>&$user){
            //组装ebh库信息
            foreach($infots as $infot){
                if(!empty($infot)&&$infot['uid']==$user[$keys]){
                    unset($infot['uid']);
                    $user = array_merge($user,$infot);
                    break;
                }else{
                    continue;
                }
            }
            //组装sns的信息
            foreach($infos as $info){
                if(!empty($info)&&$info['uid']==$user[$keys]){
                    unset($info['uid']);
                    $user = array_merge($user,$info);
                    break;
                }else{
                    continue;
                }
            }
        }
        return $users;
    }
}