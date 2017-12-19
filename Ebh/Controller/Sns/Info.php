<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:28
 */
class InfoController extends Controller {
    public function parameterRules(){
        return array(
            'detailAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),//查询的用户ID
                'userid'  =>  array('name'=>'userid','require'=>true,'type'=>'int'),//登录的用户ID
            ),
        );
    }
    public function detailAction(){
        $userModel = new UserModel();
        $user = $userModel->getUserByUid($this->uid);
        $baseinfoModel = new SnsBaseinfoModel();
        $param[0]['uid']=$user['uid'];
        $userinfo =  $baseinfoModel->getUserinfo($param);
        $user = $userinfo[0] ? array_merge($user,$userinfo[0]) : $user;
        if(!isset($user['nzcount'])){
            $user['nzcount'] = 0;
        }
        if(!isset($user['npcount'])){
            $user['npcount'] = 0;
        }
        if(!isset($user['ngcount'])){
            $user['ngcount'] = 0;
        }
        if(!isset($user['nfcount'])){
            $user['nfcount'] = 0;
        }
        if(!isset($user['fansnum'])){
            $user['fansnum'] = 0;
        }
        if(!isset($user['followsnum'])){
            $user['followsnum'] = 0;
        }
        if(!isset($user['viewsnum'])){
            $user['viewsnum'] = 0;
        }
        if(!isset($user['feedsnum'])){
            $user['feedsnum'] = 0;
        }
        if(!isset($user['blogsnum'])){
            $user['blogsnum'] = 0;
        }

        $blacklistModel = new SnsBlackListModel();
        $user['blacklist_count'] = $blacklistModel->getlistcount(array('fromuid'=>$this->uid,'state'=>0));
        $imageModel = new SnsImageModel();
        $user['photo_count'] = $imageModel->getImgCount(array('uid'=>$this->uid,'status'=>0));


        //检测是否黑名单
        $blackModel = new SnsBlackListModel();
        $isblack = $blackModel->getlistcount(array('touid'=>$user['uid'],'fromuid'=>$this->userid,'state'=>0));
        $isblack = $isblack > 0 ? true : false;
        $user['is_black'] = $isblack;


        $result[0] = $user;
        //检测是否关注
        $followModel = new SnsFollowModel();
        $result = $followModel->checkfollowed($result,$this->userid,'uid');

        //检测是否互为关注
        $allfollowed = false;
        if($result[0]['followed']){
            $temp[0]['uid'] = $this->userid;
            $temp = $followModel->checkfollowed($temp,$result[0]['uid'],'uid');
            if($temp[0]['followed']){
                $allfollowed = true;
            }
        }
        $result[0]['allfollowed'] = $allfollowed;

        //访客
        $visitorModel = new SnsVisitorModel();
        $visitor = $visitorModel->getvisitor($this->uid);
        if($visitor['visitornum']){
            $result[0]['viewsnum'] += $visitor['visitornum'];
        }
        $result[0]['visitor'] = $visitor;
        //如果用户ID和获取的信息不同 设置访客信息
        if($this->uid != $this->userid){
            $myUser[0]['uid'] = $this->userid;
            $myUser =  $baseinfoModel->getUserinfo($myUser);
            $visitorModel->visitor($myUser[0],$result[0]);
        }
        return $result[0];
    }
}