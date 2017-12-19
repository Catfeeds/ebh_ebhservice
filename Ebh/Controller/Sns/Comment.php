<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:18
 */
class CommentController extends Controller {


    public function parameterRules(){
        return array(
            'feedsAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'fid'  =>  array('name'=>'fid','require'=>true,'type'=>'int'),
                'content'  =>  array('name'=>'content','require'=>true),
                'touid'  =>  array('name'=>'touid','type'=>'int','default'=>0),
                'pcid'  =>  array('name'=>'pcid','type'=>'int','default'=>0),
                'ip'  =>  array('name'=>'ip','require'=>true),
                'format'  =>  array('name'=>'format','default'=>'json'),
            ),
            'getFeedsListAction'    =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'fid'  =>  array('name'=>'fid','require'=>true,'type'=>'int'),
                'lastcid'  =>  array('name'=>'lastcid','require'=>true,'type'=>'int'),
                'format'  =>  array('name'=>'format','default'=>'json'),
            ),
            'getBlogListAction'    =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'bid'  =>  array('name'=>'bid','require'=>true,'type'=>'int'),
                'lastcid'  =>  array('name'=>'lastcid','require'=>true,'type'=>'int'),
                'format'  =>  array('name'=>'format','default'=>'json'),
            ),
            'delFeedsAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'cid'  =>  array('name'=>'cid','require'=>true,'type'=>'int'),
            ),
            'blogAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'bid'  =>  array('name'=>'bid','require'=>true,'type'=>'int'),//日志ID
                'content'  =>  array('name'=>'content','require'=>true),
                'touid'  =>  array('name'=>'touid','type'=>'int','default'=>0),
                'pcid'  =>  array('name'=>'pcid','type'=>'int','default'=>0),
                'ip'  =>  array('name'=>'ip','require'=>true),
                'format'  =>  array('name'=>'format','default'=>'json'),
            ),
            'delBlogAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'cid'  =>  array('name'=>'cid','require'=>true,'type'=>'int'),
            ),
        );
    }

    /**
     * 动态评论
     */
    public function feedsAction(){
        $fid = $this->fid;
        $uid = $this->uid;
        $content = $this->content;
        $touid = $this->touid > 0 ? $this->touid : $this->uid;
        $pcid = $this->pcid;
        $isreply = $pcid > 0 ? true : false;

        $commentModel = new SnsCommentModel();
        $baseinfoModel = new SnsBaseinfoModel();
        $feedsModel = new SnsFeedsModel();
        $outboxModel = new SnsOutboxModel();


        $feeds = $feedsModel->getFeedsByFid($fid);
        $user = $baseinfoModel->getUserinfo(array(0=>array('uid'=>$uid)));
        $user = $user[0];
        $touser = $baseinfoModel->getUserinfo(array(0=>array('uid'=>$touid)));
        $touser = $touser[0];
        $fnickname = !empty($user['realname'])?$user['realname']:$user['username'];
        $tnickname = !empty($touser['realname'])?$touser['realname']:$touser['username'];
        $ip = $this->ip;

        $data = array(
            'pcid'=>$pcid,
            'fid'=>	$fid,
            'fromuid'=>	$uid,
            'touid'=>$touid,
            'message'=>json_encode(array(
                'content'=>$content,
                'fromuser'=>array(
                    'uid'=>$uid,
                    'realname'=>$fnickname,
                    'face'=> $user['face'],
                    'sex'=> $user['sex'],
                    'groupid'=>$user['groupid']
                ),
                'touser'=>array(
                    'uid'=>$touid,
                    'realname'=>$tnickname,
                    'face'=> $touser['face'],
                    'sex'=> $touser['sex'],
                    'groupid'=>$touser['groupid']
                ),
            )),
            'category'=>$feeds['category'],
            'toid'=>	$feeds['toid'],
            'dateline'=>time(),
            'ip'=>$ip

        );
        $cid = $commentModel->add($data);
        //评论成功 评论数加1
        if($cid>0){
            $outboxModel->update(array('cmcount'=>true),$fid);
            $comment  = $commentModel->getcommentbycid($cid);
            $comment['message'] = json_decode($comment['message'],true);

            if($this->format == 'html'){
                Ebh()->helper->load('feedhtml');
                $result = getreplylihtml($comment,$uid);
                $result = str_replace(array("\r\n", "\r", "\n","\t"), "", $result);
            }else{
                $result = $comment;
            }

            if($touid != $uid){
                $noticeModel = new SnsNoticeModel();
                //获取原评论内容用于通知显示
                if($isreply){
                    $oricomment = $commentModel->getcommentbycid($pcid);
                    $ocomment = json_decode($oricomment['message'],1);
                }else{
                    $ocomment = array();
                }
                //发布一条通知
                $notice = array(
                    'fromuid'=>	$uid,
                    'touid'=>$touid,
                    'message'=>json_encode(
                        array(
                            'fid'=>$fid,
                            'isreply'=>$isreply,
                            'content'=>$comment['message']['content'],
                            'orimsg'=>$ocomment
                        )
                    ),
                    'type'=>2,
                    'category'=>1,
                    'toid'=>$feeds['toid'],
                    'dateline'=>time(),
                );
                $noticeModel->add($notice);
                //更新通知数

                $baseinfoModel->updateone(array(),$touid,array('npcount'=>'npcount + 1'));
            }

            return returnData(1,'回复成功',$result);
        }else{
            return returnData(0,'回复失败');
        }
    }

    /**
     * 删除动态评论
     */
    public function delFeedsAction(){
        $commentModel = new SnsCommentModel();
        $outboxModel = new SnsOutboxModel();
        $comment  = $commentModel->getcommentbycid($this->cid);
        if($comment['fromuid'] != $this->uid){
            return returnData(0,'不能删除不属于自己的评论');
        }
        $param = array(
            'status'=>1,
        );
        $ck = $commentModel->edit($param,$this->cid);
        if($ck>0){
            //删除成功 评论数减1
            $outboxModel->update(array('cmcount'=>true),$comment['fid'],'reduce');
        }
        return returnData(1,'删除成功');
    }

    /**
     * 获取动态评论列表
     * @return mixed|string
     */
    public function getFeedsListAction(){
        $uid = $this->uid;
        $fid = $this->fid;
        $lastcid = $this->lastcid;
        $param = array(
            'fid'=>$fid,
            'condition'=>"cid > $lastcid",
            'limit'=>"10"
        );
        $commentModel = new SnsCommentModel();
        $replys = $commentModel->getcommentlist($param);

        if($this->format == 'html'){
            $result = '';
            Ebh()->helper->load('feedhtml');
            if(!empty($replys)){
                foreach($replys as $reply){
                    $reply['message'] = json_decode($reply['message'],true);
                    $result.=getreplylihtml($reply,$uid);
                }
                $result = str_replace(array("\r\n", "\r", "\n","\t"), "", $result);
            }
        }else{
            $result = $replys;
        }

        return $result;
    }

    /**
     * 获取日志更多评论
     * @return mixed|string
     */
    public function getBlogListAction(){
        $uid = $this->uid;
        $bid = $this->bid;
        $lastcid = $this->lastcid;

        $blogModel = new SnsBlogModel();
        $blog = $blogModel->getlist(array('bid'=>$bid));
        $param = array(
            'toid'=>$bid,
            'category' => $blog[0]['iszhuan'] == 1 ? 4 : 2,
            'fid' => 0,
            'condition'=>"cid > $lastcid",
            'limit'=>"10"
        );
        $commentModel = new SnsCommentModel();
        $replys = $commentModel->getcommentlist($param);
        if($this->format == 'html'){
            $result = '';
            Ebh()->helper->load('feedhtml');
            if(!empty($replys)){
                foreach($replys as $reply){
                    $reply['message'] = json_decode($reply['message'],true);
                    $result.=getreplylihtml($reply,$uid);
                }
                $result = str_replace(array("\r\n", "\r", "\n","\t"), "", $result);
            }
        }else{
            $result = $replys;
        }

        return $result;
    }
    /**
     * 日志评论
     * @return array
     */
    public function blogAction(){
        $bid = $this->bid;
        $uid = $this->uid;
        $content = $this->content;
        $touid = $this->touid > 0 ? $this->touid : $this->uid;
        $pcid = $this->pcid;
        $isreply = $pcid > 0 ? true : false;

        $blogModel = new SnsBlogModel();
        $blog = $blogModel->getlist(array('bid'=>$bid));
        $touid = $touid > 0 ? $touid : $blog[0]['uid'];
        $commentModel = new SnsCommentModel();
        $baseinfoModel = new SnsBaseinfoModel();

        $user = $baseinfoModel->getUserinfo(array(0=>array('uid'=>$uid)));
        $user = $user[0];
        $touser = $baseinfoModel->getUserinfo(array(0=>array('uid'=>$touid)));
        $touser = $touser[0];
        $fnickname = !empty($user['realname'])?$user['realname']:$user['username'];
        $tnickname = !empty($touser['realname'])?$touser['realname']:$touser['username'];
        $ip = $this->ip;
        $data = array(
            'pcid'=>$pcid,
            'fromuid'=>	$uid,
            'touid'=>$touid,
            'message'=>json_encode(array(
                'content'=>$content,
                'fromuser'=>array(
                    'uid'=>$uid,
                    'realname'=>$fnickname,
                    'face'=>$user['face'],
                    'sex'=>$user['sex'],
                    'groupid'=>$user['groupid']
                ),
                'touser'=>array(
                    'uid'=>$touid,
                    'realname'=>$tnickname,
                    'face'=>$touser['face'],
                    'sex'=>$touser['sex'],
                    'groupid'=>$touser['groupid']
                ),
            )),
            'type'=>2,
            'category'=>$blog[0]['iszhuan'] == 1 ? 4 :2,
            'toid'=>$bid,
            'dateline'=>time(),
            'ip'=>$ip
        );

        $cid = $commentModel->add($data);


        //评论成功 评论数加1
        if($cid>0){
            //更新日志评论数
            $blogModel = new SnsBlogModel();
            $where['bid'] = $bid;
            $sparam['cmcount'] = 'cmcount + 1';
            $blogModel->update(array(),$where,$sparam);

            $comment  = $commentModel->getcommentbycid($cid);
            $comment['message'] = json_decode($comment['message'],true);

            if($this->format == 'html'){
                Ebh()->helper->load('feedhtml');
                $result = getreplylihtml($comment,$uid);
                $result = str_replace(array("\r\n", "\r", "\n","\t"), "", $result);
            }else{
                $result = $comment;
            }

            if($touid != $uid){
                $ntModel = new SnsNoticeModel();
                if($isreply){
                    $oricomment = $commentModel->getcommentbycid($pcid);
                    $ocomment = json_decode($oricomment['message'],1);
                }else{
                    $ocomment = array();
                }

                //发布一条通知
                $notice = array(
                    'fromuid'=>	$uid,
                    'touid'=>$touid,
                    'message'=>json_encode(
                        array(
                            'fid'=>0,
                            'isreply'=>$isreply,
                            'content'=>$comment['message']['content'],
                            'orimsg'=>$ocomment
                        )
                    ),
                    'type'=>2,
                    'category'=>$blog[0]['iszhuan'] == 1 ? 4 :2,
                    'toid'=>$bid,
                    'dateline'=>time(),
                );
                $ntModel->add($notice);

                //更新通知数

                $baseinfoModel->updateone(array(),$touid,array('npcount'=>'npcount + 1'));
            }
            return returnData(1,'评论成功',$result);
        }else{
            return returnData(0,'评论失败');
        }
    }

    /**
     * 删除日志评论
     */
    public function delBlogAction(){
        $uid = $this->uid;
        $cid = $this->cid;
        $commentModel = new SnsCommentModel();
        $comment  = $commentModel->getcommentbycid($cid);
        if($comment['fromuid']!=$uid){
            return returnData(0,'不能删除不属于自己的评论');
        }
        $param = array(
            'status'=>1,
        );
        $ck = $commentModel->edit($param,$cid);
        if($ck>0){
            //更新日志评论数
            $blogModel = new SnsBlogModel();
            $where['bid'] = $comment['toid'];
            $sparam['cmcount'] = 'cmcount - 1';
             $blogModel->update(array(),$where,$sparam);
             return returnData(1,'删除成功');
        }else{
            return returnData(0,'删除失败');
        }
    }
}