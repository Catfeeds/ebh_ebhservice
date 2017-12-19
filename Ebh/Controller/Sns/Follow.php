<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 13:17
 */
class FollowController extends Controller {
    public function parameterRules(){
        return array(
            'followAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),//当前用户的UID
                'target'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),//获取目标UID
                'q'  =>  array('name'=>'q','default'=>''),
                'page'  =>  array('name'=>'page','type'=>'int','default'=>1),
                'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
            ),
            'fansAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'target'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),//获取目标UID
                'q'  =>  array('name'=>'q','default'=>''),
                'page'  =>  array('name'=>'page','type'=>'int','default'=>1),
                'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
            ),
            'addFollowAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'fuid'  =>  array('name'=>'fuid','require'=>true,'type'=>'int'),
            ),
            'cancelAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'fuid'  =>  array('name'=>'fuid','require'=>true,'type'=>'int'),
                'type'  =>  array('name'=>'type','require'=>true),
            )
        );
    }

    /**
     * 获取用户的关注
     * @return array
     */
    public function followAction(){
        $followModel = new SnsFollowModel();
        $baseinfoModel = new SnsBaseinfoModel();
        $q = trim($this->q);
        $regex = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
        $q =  preg_replace($regex,"",$q);
        if(empty($q)){
            $page = $this->page;
            $pagesize = $this->pagesize;
            $param = array(
                'uid'=>$this->target,
                'limit'=>max(0,($page-1)*$pagesize)." , $pagesize"
            );

            $follows = $followModel->getfollowlist($param);
            $count = $followModel->getfollowcount($param);
            //组装个人信息
            if(!empty($follows)){

                $follows = $baseinfoModel->getUserinfo($follows,"fuid");
                //dump($follows);
                //检测互相关注
                $follows = $followModel->checkfollow($follows,$this->uid,'fuid');
                //检测是否关注
                $follows = $followModel->checkfollowed($follows,$this->uid,'fuid');
            }
        }else{
            $param = array(
                'uid'=>$this->target
            );
            $follows = $followModel->getfollowlist($param);
            if(!empty($follows)){
                $follows = $baseinfoModel->getUserinfo($follows,"fuid");
                foreach($follows as $key=>$follow){
                    if(!(preg_match("/$q/", $follow['username'])
                        ||preg_match("/$q/", $follow['realname'])
                        ||preg_match("/$q/", $follow['nickname'])
                    )){
                        unset($follows[$key]);
                    }
                }

                //检测互相关注
                $follows = $followModel->checkfollow($follows,$this->uid,'fuid');

                //检测是否关注
                $follows = $followModel->checkfollowed($follows,$this->uid,'fuid');

                //dump($follows);
            }
            $count = count($follows);
        }

        return array(
            'count' =>  $count,
            'follows'    =>  $follows
        );

    }

    /**
     * 获取粉丝列表
     * @return array
     */
    public function fansAction(){
        $followModel = new SnsFollowModel();
        $baseinfoModel = new SnsBaseinfoModel();
        $q = trim($this->q);
        $regex = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
        $q =  preg_replace($regex,"",$q);
        if(empty($q)){
            $page = $this->page;
            $pagesize = $this->pagesize;
            $param = array(
                'fuid'=>$this->target,
                'limit'=>max(0,($page-1)*$pagesize)." , $pagesize"
            );

            $fans = $followModel->getfollowlist($param);
            $count = $followModel->getfollowcount($param);
            if(!empty($fans)){

                $fans = $baseinfoModel->getUserinfo($fans,"uid");
                //检测互相关注
                $fans = $followModel->checkfollow($fans,$this->uid,'uid');
                //检测是否关注
                $fans = $followModel->checkfollowed($fans,$this->uid,'uid');
                //dump($fans);
            }
        }else{
            $param = array(
                'fuid'=>$this->target,
            );
            $fans = $followModel->getfollowlist($param);
            if(!empty($fans)){
                $fans = $baseinfoModel->getUserinfo($fans,"uid");
                //dump($fans);
                foreach($fans as $key=>$fan){
                    if(!(preg_match("/$q/", @$fan['username'])
                        ||preg_match("/$q/", @$fan['realname'])
                        ||preg_match("/$q/", @$fan['nickname'])
                    )){
                        unset($fans[$key]);
                    }
                }
                if(!empty($fans)){
                    //检测互相关注
                    $fans = $followModel->checkfollow($fans,$this->uid,'uid');
                    //检测是否关注
                    $fans = $followModel->checkfollowed($fans,$this->uid,'uid');
                }
                //dump($fans);
            }
            $count = count($fans);
        }

        return array(
            'count' =>  $count,
            'fans'    =>  $fans
        );
    }


    /**
     * 添加关注
     */
    public function addFollowAction(){
        $followModel = new SnsFollowModel();
        $fuid = $this->fuid;
        $uid = $this->uid;
        $param = array(
            'uid'=>$uid,
            'fuid'=>$fuid,
        );
        $ck = $followModel->addone($param);

        if($ck){
            $ntModel = new SnsNoticeModel();
            //发布一条通知
            $notice = array(
                'fromuid'=>	$uid,
                'touid'=>$fuid,
                'message'=>json_encode(
                    array(
                        'topic'=>'',
                        'comment'=>'关注了你'
                    )
                ),
                'type'=>5,
                'category'=>5,
                'toid'=>0,
                'dateline'=>time(),
            );
            $ntModel->add($notice);
            //更新通知数
            $baseModel = new SnsBaseinfoModel();
            $baseModel->updateone(array(),$fuid,array('ngcount'=>'ngcount + 1'));
            return returnData(1,'关注成功');
        }else{
            return returnData(0,'关注失败');
        }
    }

    /**
     * 取消关注
     * @return array
     */
    public function cancelAction(){
        $followModel = new SnsFollowModel();
        $fuid = $this->fuid;
        $uid = $this->uid;
        $type = $this->type;
        if($type=="follow"){//取消关注
            $ck = $followModel->cancelone($uid,$fuid);
        }elseif($type=="fans"){//取消粉丝
            $ck = $followModel->cancelone($fuid,$uid);
        }
        if($ck){
            return returnData(1,'取消成功');
        }else{
            return returnData(0,'取消失败');
        }
    }



}