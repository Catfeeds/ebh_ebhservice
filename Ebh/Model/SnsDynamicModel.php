<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 16:47
 */
class SnsDynamicModel{
    private $feedsModel = null;
    private $outboxModel = null;
    private $baseinfoModel = null;
    private $classroomFeedsModel = null;
    private $blackModel = null;
    private $commentModel = null;
    private $delModel = null;
    private $followModel  = null;
    public function __construct(){
        $this->db = Ebh()->snsdb;
        $this->feedsModel = new SnsFeedsModel();
        $this->outboxModel = new SnsOutboxModel();
        $this->baseinfoModel = new SnsBaseinfoModel();
        $this->classroomFeedsModel = new SnsClassroomFeedsModel();
        $this->blackModel = new SnsBlackListModel();
        $this->commentModel = new SnsCommentModel();
        $this->delModel = new SnsDelModel();
        $this->followModel = new SnsFollowModel();
    }



    //获取用户黑名单列表
    private function getblacklist($uid){
        $key = 'blacklist_'.$uid.'_'.md5($uid);
        $cache = Ebh()->cache->getRedis();
        $blacklist = array();
        $data = $cache->lrange($key,0,-1);
        if(!empty($data)){
            $blacklist = $data;
        }else{
            $blacklistmodel = $this->blackModel;
            $blacklists = $blacklistmodel->getlist(array('fromuid'=>$uid,'state'=>0));
            if(!empty($blacklists)){
                foreach ($blacklists as $item){
                    $blacklist[] = $item['touid'];
                }
            }
        }
        return $blacklist;
    }

    /**
     * 获取动态
     * @param $uid
     * @param int $len
     * @param string $flag
     * @param int $start
     * @param string $type
     * @return array
     */
    public function getFeeds($uid,$len=10,$flag="old",$start=0,$type="all"){
        $feedsList = array();
        switch ($type){
            /*case 'all':
                $feedsList = $this->getAllFeeds($uid,$len,$flag,$start);
                break;*/
            case 'myindex':
                $feedsList = $this->getMyFeeds($uid,$len,$flag,$start);
                break;
            case 'myfollow':
                $feedsList = $this->getFollowFeeds($uid,$len,$flag,$start);
                break;
            case 'myclass':
                $feedsList = $this->getClassFeeds($uid,$len,$flag,$start);
                break;
            case 'myschool':
                $feedsList = $this->getRoomFeeds($uid,$len,$flag,$start);
                break;
        }
        return  $feedsList;
    }


    /**
     * 获取我的动态
     */
    public function getMyFeeds($uid,$len=10,$flag="old",$start=0){
        if($flag=="old"){//查看以前的n条记录
            $uidin = " o.uid  = $uid ";
            $condition = ($start!=0)? " and o.fid < $start and f.status = 0 " : " and f.status = 0 ";
            $orderby = " order by o.fid DESC ";
            $limit = " limit $len ";

            $sql = "select o.outid,o.fid,o.pfid,o.tfid,o.iszhuan,o.uid,o.upcount,o.cmcount,o.zhcount,o.dateline,f.fromuid,f.message,f.category,f.toid from  ebh_sns_outboxs o
						join ebh_sns_feeds f on o.fid = f.fid
						where {$uidin}{$condition}{$orderby}{$limit}
					";

        }elseif($flag=="new"){//查看最新的n条记录
            $uidin = " o.uid  = $uid ";
            $condition = " and o.fid > $start and f.status = 0 " ;
            $orderby = " order by o.fid DESC ";
            $limit = " limit $len ";

            $sql = "select o.outid,o.fid,o.pfid,o.tfid,o.iszhuan,o.uid,o.upcount,o.cmcount,o.zhcount,o.dateline,f.fromuid,f.message,f.category,f.toid from  ebh_sns_outboxs o
						join ebh_sns_feeds f on o.fid = f.fid
						where {$uidin}{$condition}{$orderby}{$limit}
					";
        }
        //echo $sql;
        //查询符合条件的feeslist
        $feeds = $this->db->query($sql)->list_array();

        //组装评论
        $feeds = $this->getFeedsWithReplys($feeds);

        //dump($feeds);exit;
        return $feeds;
    }
    /**
     * 获取关注动态
     */
    public function getFollowFeeds($uid,$len=10,$flag="old",$start=0){
        $blacklist = $this->getblacklist($uid);
        $fuidarr =$this->getFollowUidArr($uid);
        if($flag=="old"){//查看以前的n条记录
            $uidin = empty($fuidarr)?" o.fid <0 ":" o.uid in ( ".implode(",", $fuidarr)." ) ";
            $condition = ($start!=0)? " and o.fid < $start and f.status = 0 " : " and f.status = 0 ";
            if(!empty($blacklist)){
                $condition .= " and o.uid not in (".implode(',',$blacklist).")";
            }
            $orderby = " order by o.fid DESC ";
            $limit = " limit $len ";

            $sql = "select o.outid,o.fid,o.pfid,o.tfid,o.iszhuan,o.uid,o.upcount,o.cmcount,o.zhcount,o.dateline,f.fromuid,f.message,f.category,f.toid from  ebh_sns_outboxs o
						join ebh_sns_feeds f on o.fid = f.fid
						where {$uidin}{$condition}{$orderby}{$limit}
					";

        }elseif($flag=="new"){//查看最新的n条记录
            $uidin = empty($fuidarr)?" o.fid <0  ":" o.uid in ( ".implode(",", $fuidarr)." ) ";
            $condition = " and o.fid > $start and f.status = 0 " ;
            if(!empty($blacklist)){
                $condition .= " and o.uid not in (".implode(',',$blacklist).")";
            }
            $orderby = " order by o.fid DESC ";
            $limit = " limit $len ";

            $sql = "select o.outid,o.fid,o.pfid,o.tfid,o.iszhuan,o.uid,o.upcount,o.cmcount,o.zhcount,o.dateline,f.fromuid,f.message,f.category,f.toid from  ebh_sns_outboxs o
						join ebh_sns_feeds f on o.fid = f.fid
						where {$uidin}{$condition}{$orderby}{$limit}
					";
        }
        //var_dump($fuidarr);
        //echo $sql;
        //查询符合条件的feeslist
        $feeds = $this->db->query($sql)->list_array();

        //组装评论
        $feeds = $this->getFeedsWithReplys($feeds);

        //dump($feeds);exit;
        return $feeds;
    }

    /**
     * 我的网校动态
     * @param $uid
     * @param int $len
     * @param string $flag
     * @param int $start
     */
    public function getRoomFeeds($uid,$len=10,$flag="old",$start=0){
        $blacklist = $this->getblacklist($uid);
        $cidarr = $this->baseinfoModel->getUserCrid($uid);
        if($flag=="old"){//查看以前的n条记录
            $cridin = empty($cidarr)?" r.fid < 0 ":" r.crid in ( ".implode(",", $cidarr)." ) ";
            $condition = ($start!=0)? " and r.fid< $start and r.status = 0 " : " and r.status = 0 ";
            if(!empty($blacklist)){
                $condition .= " and r.uid not in (".implode(',', $blacklist).")";
            }
            $orderby = " order by r.fid DESC ";
            $limit = " limit $len ";

            $sql = "select distinct r.fid,o.outid,o.fid,o.pfid,o.tfid,o.iszhuan,o.uid,o.upcount,o.cmcount,o.zhcount,o.dateline,f.fromuid,f.message,f.category,f.toid from ebh_sns_roomfeeds r 
			 		   join ebh_sns_feeds f on r.fid = f.fid
			 		   join ebh_sns_outboxs o on o.fid = f.fid
					   where {$cridin}{$condition}{$orderby}{$limit}
					";
        }elseif($flag=="new"){//查看最新的n条记录
            $cridin = empty($cidarr)?" r.fid < 0 ":" r.crid in ( ".implode(",", $cidarr)." ) ";
            $condition = " and r.fid > $start and r.status = 0 " ;
            if(!empty($blacklist)){
                $condition .= " and r.uid not in (".implode(',', $blacklist).")";
            }
            $orderby = " order by r.fid DESC ";
            $limit = " limit $len ";

            $sql = "select distinct r.fid,o.outid,o.fid,o.pfid,o.tfid,o.iszhuan,o.uid,o.upcount,o.cmcount,o.zhcount,o.dateline,f.fromuid,f.message,f.category,f.toid from ebh_sns_roomfeeds r 
			 		    join ebh_sns_feeds f on r.fid = f.fid
			 		   join ebh_sns_outboxs o on o.fid = f.fid
					   where {$cridin}{$condition}{$orderby}{$limit}
					";
        }
        //查询符合条件的feeslist
        //echo $sql;
        $feeds = $this->db->query($sql)->list_array();

        //组装评论
        $feeds = $this->getFeedsWithReplys($feeds);
        //dump($feeds);exit;
        return $feeds;
    }

    /**
     * 获取我的班级
     */
    public function getClassFeeds($uid,$len=10,$flag="old",$start=0){
        $blacklist = $this->getblacklist($uid);
        $classidarr = $this->baseinfoModel->getUserClassId($uid);
        if($flag=="old"){//查看以前的n条记录
            $classidin = empty($classidarr)?" c.fid < 0 ":" c.classid in ( ".implode(",", $classidarr)." ) ";
            $condition = ($start!=0)? " and c.fid< $start and c.status = 0 " : " and c.status = 0 ";
            if(!empty($blacklist)){
                $condition .= " and c.uid not in (".implode(',',$blacklist).")";
            }
            $orderby = " order by c.fid DESC ";
            $limit = " limit $len ";

            $sql = "select distinct c.fid,o.outid,o.fid,o.pfid,o.tfid,o.iszhuan,o.uid,o.upcount,o.cmcount,o.zhcount,o.dateline,f.fromuid,f.message,f.category,f.toid from ebh_sns_classfeeds c
						join ebh_sns_feeds f on c.fid = f.fid
						join ebh_sns_outboxs o on o.fid = f.fid
						where {$classidin}{$condition}{$orderby}{$limit}
						";

        }elseif($flag=="new"){//查看最新的n条记录
            $classidin = empty($classidarr)?" c.fid < 0 ":" c.classid in ( ".implode(",", $classidarr)." ) ";
            $condition = " and c.fid > $start and c.status = 0 " ;
            if(!empty($blacklist)){
                $condition .= " and c.uid not in (".implode(',',$blacklist).")";
            }
            $orderby = " order by c.fid DESC ";
            $limit = " limit $len ";

            $sql = "select distinct c.fid,o.outid,o.fid,o.pfid,o.tfid,o.iszhuan,o.uid,o.upcount,o.cmcount,o.zhcount,o.dateline,f.fromuid,f.message,f.category,f.toid from  ebh_sns_classfeeds c
						join ebh_sns_feeds f on c.fid = f.fid
						join ebh_sns_outboxs o on o.fid = f.fid
						where {$classidin}{$condition}{$orderby}{$limit}
					";
        }
        //查询符合条件的feeslist
        //echo $sql;
        $feeds = $this->db->query($sql)->list_array();
        //组装评论
        $feeds = $this->getFeedsWithReplys($feeds);
        //dump($feeds);exit;
        return $feeds;

    }








    /**
     * 获取关注的好友uid
     * @param unknown $uid
     * @return unknown|multitype:
     */
    public function  getFollowUidArr($uid,$hasmyself=false){
        //查看用户的关注好友
        $follows = $this->followModel->getmyfollows($uid);
        $uidarr = array();
        if(!empty($follows)){
            $uidarr = array_map(function($arr){return $arr['uid']; }, $follows);
        }
        if($hasmyself){
            $uidarr[] = $uid;
        }
        return  $uidarr;
    }

    /**
     * 产生一条动态信息
     * @param $feeds
     * @param $uid
     * @return mixed
     */
    public function publish($feeds,$uid){
        $fid  = $this->feedsModel->add($feeds);

        //写入发件箱outbox
        if($fid>0){
            $outbox = array(
                'fid'=>$fid,
                'uid'=>	$uid,
                'ip'=>$feeds['ip'],
                'dateline'=>SYSTIME
            );
            $this->outboxModel->add($outbox);
            //插入网校/班级动态表
            $this->addClassAndRoomFeeds($uid,$fid);

            //动态数+1
            $this->baseinfoModel->updateone(array(),$uid,array('feedsnum'=>'feedsnum + 1'));
        }

        return $fid;
    }

    /**
     * 转发  产生一条新动态
     * @param $fid
     * @param $uid
     * @param $content
     * @return mixed
     */
    public function transfer($fid,$uid,$content){
        $feeds  = $this->feedsModel->getFeedsByFid($fid);
        $feeds = $this->baseinfoModel->getUserinfo(array(0=>$feeds),"fromuid");
        $feeds = $feeds[0];
        if($feeds['iszhuan']){
            $refermessage = json_decode($feeds['message'],true);
            $refer =  $refermessage['refer'];
            $refer_nickname = $refermessage['referuser']['realname'];
            $refer_face = $refermessage['referuser']['face'];
            $refer_uid = $refermessage['referuser']['uid'];
        }else{
            $refer_nickname = !empty($feeds['realname'])?$feeds['realname']:$feeds['username'];
            $refer_face = $feeds['face'];
            $refer_uid = $feeds['fromuid'];
            $refer = json_decode($feeds['message'],true);
        }
        $ip = getip();
        $newfeeds = array(
            'fromuid'=>$uid,
            'message'=>json_encode(array(
                'content'=>$content,
                'images'=>'',
                'type'=>'mood',
                'refer'=>$refer,
                'referuser'=>array(
                    'realname'=>$refer_nickname,
                    'face'=>$refer_face,
                    'uid'=>$refer_uid,
                ),
            )),
            'category'=>$feeds['category'],
            'toid'=>$feeds['toid'],
            'dateline'=>time(),
            'ip'=>$ip
        );

        //写入feeds
        $newfid  = $this->feedsModel->add($newfeeds);
        //写入发件箱outbox
        if($newfid>0){
            $outbox = array(
                'fid'=>$newfid,
                'uid'=>	$uid,
                'pfid'=>$fid,
                'tfid'=>($feeds['tfid']>0)?$feeds['tfid']:$fid,
                'iszhuan'=>1,
                'dateline'=>time(),
                'ip'=>$ip
            );
            $outid = $this->outboxModel->add($outbox);

            //插入网校/班级动态表
            $this->addClassAndRoomFeeds($uid,$newfid);

            if($outid>0){
                //转发成功 更新转发次数
                $this->outboxModel->update(array('zhcount'=>true),$outbox['tfid']);//顶级
                if($outbox['tfid']!=$fid){
                    $this->outboxModel->update(array('zhcount'=>true),$fid);//父级
                }
                //动态数+1
                $this->baseinfoModel->updateone(array(),$uid,array('feedsnum'=>'feedsnum + 1'));

            }
            //自己转发自己的不通知
            if($uid != $feeds['fromuid']){
                $noticeModel = new SnsNoticeModel();
                //发布一条通知
                $notice = array(
                    'fromuid'=>	$uid,
                    'touid'=>$feeds['fromuid'],
                    'message'=>json_encode(
                        array(
                            'fid'=>$feeds['fid'],
                            'content'=>$content
                        )
                    ),
                    'type'=>4,
                    'category'=>1,
                    'toid'=>$feeds['toid'],
                    'dateline'=>time(),
                );
                $noticeModel->add($notice);

                $this->baseinfoModel->updateone(array(),$feeds['fromuid'],array('nfcount'=>'nfcount + 1'));
            }
        }
        return $newfid;
    }
    /**
     * 获取某一个人发表的动态
     * @param unknown $uid
     * @param number $len
     * @param number $start
     * @return unknown
     */
    public function getOnesFeeds($uid,$len=10,$start=0){
        $blacklist = $this->getblacklist($uid);
        $condition = ($start!=0)? " and o.fid < $start and f.status = 0 " : " and f.status = 0 ";
        if(!empty($blacklist)){
            $condition .= " and o.uid not in (".implode(',', $blacklist).")";
        }
        $orderby = " order by o.fid DESC ";
        $limit = " limit $len ";
        $sql = "select o.outid,o.fid,o.pfid,o.tfid,o.iszhuan,o.uid,o.upcount,o.cmcount,o.zhcount,o.dateline,f.fromuid,f.message,f.category,f.toid from  ebh_sns_outboxs o
					left join ebh_sns_feeds f on o.fid = f.fid
					where o.uid  = $uid
					{$condition}{$orderby}{$limit}";

        //查询符合条件的feeslist
        $feeds = $this->db->query($sql)->list_array();

        //组装评论
        $feeds = $this->getfeedswithreplys($feeds);

        return $feeds;
    }
    /**
     * 分别向所在网校插入动态
     * 分别向所在班级插入动态
     * @param $uid
     * @param $fid
     */
    public function addClassAndRoomFeeds($uid,$fid){
        //写入网校动态
        $cridarr = $this->baseinfoModel->getUserCrid($uid);
        if(!empty($cridarr)){
            foreach($cridarr as $crid){
                $roomfeeds = array(
                    'uid'=>$uid,
                    'fid'=>$fid,
                    'crid'=>$crid,
                    'dateline'=>SYSTIME
                );
                $this->classroomFeedsModel->addRoomFeeds($roomfeeds);
            }
        }

        //写入班级动态
        $classidarr = $this->baseinfoModel->getUserClassId($uid);
        if(!empty($classidarr)){
            foreach($classidarr as $classid){
                $classfeeds = array(
                    'uid'=>$uid,
                    'classid'=>$classid,
                    'fid'=>$fid,
                    'dateline'=>SYSTIME
                );
                $this->classroomFeedsModel->addClassFeeds($classfeeds);
            }
        }
    }




    /**
     * 组装评论,返回动态
     * @param  $feeds 动态list
     * @return feeds
     */
    public function getFeedsWithReplys($feeds){
        //合并feeds
        $fidarr = array_map(function($arr){return $arr['fid'];}, $feeds);

        //查询动态关联的评论
        $replylists = $this->commentModel->getfeedscomments($fidarr);

        if(!empty($feeds)){
            foreach($feeds as &$feed ){
                //校验转发的父级是否被删除
                if($feed['iszhuan']==1){
                    $checkdel = $this->delModel->checkfeedsdelete($feed['tfid']);
                    $feed['refer_top_delete'] = $checkdel;
                }else{
                    $feed['refer_top_delete'] = false;
                }

                //组装comments
                foreach($replylists as $key=>$replys){
                    if($feed['fid']  == $key){
                        $replys['replys'] = array_map(
                            function($arr){
                                if(!empty($arr)){
                                    $arr['message'] = json_decode($arr['message'],true);
                                    return $arr;
                                }
                            }, $replys['replys']);
                        $feed['replys'] = $replys['replys'];
                        $feed['replycount'] = $replys['count'];
                    }
                }
                //json解析
                //var_dump($feed['message']);
                $feed['message'] = json_decode($feed['message'],true);
            }
        }
        return $feeds;
    }


    /**
     * 删除一条动态
     */
    public function delfeed($arr){
        //先删除feeds
        $check = $this->feedsModel->delete($arr);
        if($check>0){
            //新增一条删除记录
            $param = array(
                'toid'=>$arr['fid'],
                'uid' =>$arr['fromuid'],
                'type'=>1,
                'dateline'=>time(),
            );
            $this->delModel->add($param);
            //网校/班级动态标记
            $this->classroomFeedsModel->delroomandclassfeeds($arr['fid'],$arr['fromuid']);

            //动态数减1
            $this->baseinfoModel->updateone(array(),$arr['fromuid'],array('feedsnum'=>'feedsnum - 1'));
        }
        return ($check>0)?true:false;
    }



    /**
     * 获取最新动态数
     * @param unknown $uid 用户uid
     * @param number $start 最近一条fid
     * @param unknown $newfidarr 要过滤的fid
     * @param string $type 用户好友类型
     * @return len 长度
     */
    public function getNewFeedLen($uid,$start=0,$newfidarr = array(),$type='all'){
        $blacklist = $this->getblacklist($uid);
        if($type=='all'){
            $uidarr =$this->getFollowUidArr($uid,true);
            $cidarr = $this->baseinfoModel->getUserCrid($uid);

            $uidin = empty($uidarr)?" f.fid <0  ":" f.fromuid in ( ".implode(",", $uidarr)." ) ";
            $cridin = empty($cidarr)?" r.fid < 0 ":" r.crid in ( ".implode(",", $cidarr)." ) ";
            $notin = empty($newfidarr) ? " ":" and f.fid not in ( ".implode(",", $newfidarr)." ) ";
            $condition = " and f.fid > $start and f.status = 0 " ;
            if(!empty($blacklist)){
                $condition .= " and f.fromuid not in (".implode(',', $blacklist).")";
            }

            $sql = " select count(*) count  from (
								(select distinct r.fid from ebh_sns_roomfeeds r
										left join ebh_sns_feeds f on r.fid = f.fid
										where {$cridin}{$notin}{$condition} )
								union
								(select o.fid from  ebh_sns_outboxs o
										left join ebh_sns_feeds f on o.fid = f.fid
										where {$uidin}{$notin}{$condition})
								) a
				";
            //log_message($sql);

        }elseif($type=='myindex'){
            $uidin = " f.fromuid = $uid  ";
            $notin = empty($newfidarr) ? " ":" and f.fid not in ( ".implode(",", $newfidarr)." ) ";
            $condition = " and f.fid > $start and f.status = 0 " ;
            if(!empty($blacklist)){
                $condition .= " and f.fromuid not in (".implode(',', $blacklist).")";
            }

            $sql = "select count( * ) count  from  ebh_sns_feeds f
						where {$uidin}{$notin}{$condition}
						";
        }elseif($type=='myfollow'){
            $fuidarr =$this->getfollowuidarr($uid);
            $fuidin = empty($fuidarr)?" f.fid <0  ":" f.fromuid in ( ".implode(",", $fuidarr)." ) ";
            $notin = empty($newfidarr) ? " ":" and f.fid not in ( ".implode(",", $newfidarr)." ) ";
            $condition = " and f.fid > $start and f.status = 0 " ;
            if(!empty($blacklist)){
                $condition .= " and f.fromuid not in (".implode(',', $blacklist).")";
            }

            $sql = "select count( * ) count  from  ebh_sns_feeds f
						where {$fuidin}{$notin}{$condition}
					";
        }elseif($type=='myclass'){
            $classidarr = $this->baseinfoModel->getUserClassId($uid);
            $classidin = empty($classidarr)?" c.fid < 0 ":" c.classid in ( ".implode(",", $classidarr)." ) ";
            $notin = empty($newfidarr) ? " ":" and f.fid not in ( ".implode(",", $newfidarr)." ) ";
            $condition = " and f.fid > $start and f.status = 0 " ;
            if(!empty($blacklist)){
                $condition .= " and c.uid not in (".implode(',', $blacklist).")";
            }
            $sql = "select count( distinct c.fid ) count from  ebh_sns_classfeeds c
						left join ebh_sns_feeds f on c.fid = f.fid
						where {$classidin}{$notin}{$condition}
					";

        }elseif($type=='myschool'){
            $cidarr = $this->baseinfoModel->getUserCrid($uid);
            $cridin = empty($cidarr)?" r.fid < 0 ":" r.crid in ( ".implode(",", $cidarr)." ) ";
            $notin = empty($newfidarr) ? " ":" and f.fid not in ( ".implode(",", $newfidarr)." ) ";
            $condition = " and f.fid > $start and f.status = 0 " ;
            //count(*) count
            if(!empty($blacklist)){
                $condition .= " and r.uid not in (".implode(',', $blacklist).")";
            }

            $sql = "select  count( distinct r.fid ) count from  ebh_sns_roomfeeds r
						left join ebh_sns_feeds f on r.fid = f.fid
						where {$cridin}{$notin}{$condition}
						
					";
        }


        $row = $this->db->query($sql)->row_array();
        $len = $row['count'];

        return $len;
    }
}