<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 15:26
 * 动态控制器
 */
class FeedsController extends Controller {
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'lastfid'  =>  array('name'=>'lastfid','require'=>true,'type'=>'int'),
                'type'  =>  array('name'=>'type','require'=>true),
                'format'  =>  array('name'=>'format','default'=>'json'),
            ),
            'getOneFeedsAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'lastfid'  =>  array('name'=>'lastfid','require'=>true,'type'=>'int'),
                'format'  =>  array('name'=>'format','default'=>'json'),
            ),
            'publishAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'content'  =>  array('name'=>'content','require'=>true),
                'images'  =>  array('name'=>'images','require'=>false,'default'=>array(),'type'=>'array'),
                'ip'  =>  array('name'=>'ip','require'=>true),
                'format'  =>  array('name'=>'format','default'=>'json'),
            ),
            'transferAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'fid'  =>  array('name'=>'fid','require'=>true,'type'=>'int'),
                'content'  =>  array('name'=>'content','require'=>true),
                'ip'  =>  array('name'=>'ip','require'=>true),
                'format'  =>  array('name'=>'format','default'=>'json'),
            ),
            'upclickAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'fid'  =>  array('name'=>'fid','require'=>true,'type'=>'int'),
            ),
            'delAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'fid'  =>  array('name'=>'fid','require'=>true,'type'=>'int'),
            ),
            'pushCountAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'firstfid'  =>  array('name'=>'firstfid','require'=>true,'type'=>'int'),
                'type'  =>  array('name'=>'type','require'=>true),
                'newfidarr'  =>  array('name'=>'newfidarr','type'=>'array','default'=>array()),

            ),
            'getNewFeedsAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'firstfid'  =>  array('name'=>'firstfid','require'=>true,'type'=>'int'),
                'len'  =>  array('name'=>'len','require'=>true,'type'=>'int'),
                'type'  =>  array('name'=>'type','require'=>true),
                'format'  =>  array('name'=>'format','default'=>'json'),
            ),
        );
    }

    /**
     * 获取最新的动态
     * @return array|mixed|string
     */
    public function getNewFeedsAction(){
        $uid = $this->uid;
        $firstfid = $this->firstfid;
        $type = $this->type;
        $len = $this->len;
        $dynamicModel = new SnsDynamicModel();
        $list = $dynamicModel->getFeeds($uid,$len,'new',$firstfid,$type);
        $baseinfoModel = new SnsBaseinfoModel();
        if($this->format == 'html'){
            $result = '';
        }else{
            $result = $list;
        }
        if(!empty($list)){
            $list = $baseinfoModel->getUserinfo($list,"fromuid");
            if($this->format == 'html'){
                Ebh()->helper->load('feedhtml');
                $result = '';
                foreach($list as $feed){
                    if($feed['iszhuan']){
                        $result.=gettransferfeedlihtml($feed,$this->uid);
                    }else{
                        $result.=getfeedlihtml($feed,$this->uid);
                    }

                }
            }else{
                $result = $list;
            }

        }
        return $result;
    }

    /**
     * 动态消息条数
     */
    public function pushCountAction(){
        $uid = $this->uid;
        $firstfid = $this->firstfid;
        $type = $this->type;
        $newfidarr = $this->newfidarr;

        $dynamicModel = new SnsDynamicModel();
        $len = $dynamicModel->getNewFeedLen($uid,$firstfid,$newfidarr,$type);

        return array('count'=>$len);
    }

    /**
     * 获取动态列表
     * @return array|mixed
     */
    public function listAction(){
        $dynamicModel = new SnsDynamicModel();

        $list = $dynamicModel->getFeeds($this->uid,10,'old',$this->lastfid,$this->type);
        $baseinfoModel = new SnsBaseinfoModel();
        if($this->format == 'html'){
            $result = '';
        }else{
            $result = $list;
        }
        if(!empty($list)){
            $list = $baseinfoModel->getUserinfo($list,"fromuid");
            if($this->format == 'html'){
                Ebh()->helper->load('feedhtml');
                $result = '';
                foreach($list as $feed){
                    if($feed['iszhuan']){
                        $result.=gettransferfeedlihtml($feed,$this->uid);
                    }else{
                        $result.=getfeedlihtml($feed,$this->uid);
                    }

                }
            }else{
                $result = $list;
            }

        }
        return $result;
    }

    /**
     * 获取他人动态
     * @return mixed|string|unknown
     */
    public function getOneFeedsAction(){
        $dynamicModel = new SnsDynamicModel();
        $baseinfoModel = new SnsBaseinfoModel();
        $list = $dynamicModel->getOnesFeeds($this->uid,10,$this->lastfid);
        if($this->format == 'html'){
            $result = '';
        }else{
            $result = $list;
        }
        if(!empty($list)){
            $list = $baseinfoModel->getUserinfo($list,"fromuid");
            if($this->format == 'html'){
                Ebh()->helper->load('feedhtml');
                $result = '';
                foreach($list as $feed){
                    if($feed['iszhuan']){
                        $result.=gettransferfeedlihtml($feed,$this->uid);
                    }else{
                        $result.=getfeedlihtml($feed,$this->uid);
                    }

                }
            }else{
                $result = $list;
            }

        }
        return $result;
    }

    /**
     * 发布动态
     */
    public function publishAction(){
        $parameters['content'] = $this->content;
        $parameters['uid'] = $this->uid;

        if(!empty($this->images)){
            $parameters['images'] = implode(',',$this->images);
        }
        $parameters['dateline'] = SYSTIME;
        $parameters['ip'] = $this->ip;

        $moodModel = new SnsMoodsModel();
        $mid = $moodModel->add($parameters);
        $success = $mid > 0 ? true : false;

        if(!$success){
            return returnData(0,'发布失败');
        }
        //发布消息
        $feeds = array(
            'touid'=>$this->uid,
            'fromuid'=>	$this->uid,
            'message'=>json_encode(array(
                'content'=>$this->content,
                'images'=>!empty($parameters['images']) ? $parameters['images'] : '',
                'type'=>'mood',
            )),
            'category'=>1,
            'toid'=>$mid,
            'dateline'=>$parameters['dateline'],
            'ip'    =>  $this->ip
        );


        $dynamicModel = new SnsDynamicModel();
        $fid = $dynamicModel->publish($feeds,$this->uid);


        $newfeed = array_merge($feeds,
            array('fid'=>$fid,
                'message'=>json_decode($feeds['message'],true),
                'upcount'=>0,
                'cmcount'=>0,
                'zhcount'=>0,
                'refer_top_delete'=>false,
            ));
        $baseinfoModel = new SnsBaseinfoModel();
        $newfeed = $baseinfoModel->getUserinfo(array(0=>$newfeed),"fromuid");


        if($this->format == 'html'){
            Ebh()->helper->load('feedhtml');
            $result = getfeedlihtml($newfeed[0],$this->uid);
        }else{
            $result = $newfeed[0];
        }

        return returnData(1,'发布成功',$result);
    }

    /**
     * 转发一条动态
     * @return array
     */
    public function transferAction(){
        $uid  = $this->uid;
        $fid = $this->fid;
        $content = $this->content;
        $dynamicModel = new SnsDynamicModel();
        $feedsModel = new SnsFeedsModel();
        $baseinfoModel = new SnsBaseinfoModel();
        //先校验转载引用顶级是否被删除
        $delModel = new SnsDelModel();
        $outboxModel = new SnsOutboxModel();
        $outbox = $outboxModel->getoutboxbyfid($fid);

        $topisdel = false;
        $isdel = $delModel->checkfeedsdelete($fid);
        if(!empty($outbox['tfid'])){
            $topisdel = $delModel->checkfeedsdelete($outbox['tfid']);
        }

        if($isdel||$topisdel||empty($outbox)){
            return returnData(0,'抱歉，此动态已经被删除或不存在，无法进行转发哦。');
        }
        $nfid = $dynamicModel->transfer($fid,$uid,$content);

        $newfeed = $feedsModel->getFeedsByFid($nfid);
        $newfeed['refer_top_delete'] = false;
        $newfeed = $baseinfoModel->getUserinfo(array(0=>$newfeed),"fromuid");
        $newfeed[0]['message'] = json_decode($newfeed[0]['message'],true);

        if($this->format == 'html'){
            Ebh()->helper->load('feedhtml');
            $result = gettransferfeedlihtml($newfeed[0],$this->uid);
        }else{
            $result = $newfeed[0];
        }
        return returnData(1,'',$result);
    }

    /**
     * 动态点赞
     * @return array
     */
    public function upclickAction(){
        $uid = $this->uid;
        $fid = $this->fid;
        $upclickModel = new SnsUpclickModel();
        $checked = $upclickModel->checkclicked($uid,$fid);
        if($checked==true){
            return returnData(0,'您已经赞过了');
        }
        $data = array(
            'uid'=>$uid,
            'fid'=>$fid,
            'dateline'=>time()
        );
        $upck = $upclickModel->addredislist($data);
        if($upck){

            //获取一条动态详情
            $feedModel = new SnsFeedsModel();
            $feed = $feedModel->getFeedsByFid($fid);
            //自己点赞自己不通知
            if($feed['fromuid'] != $uid){
                $ntModel = new SnsNoticeModel();
                //发布一条通知
                $notice = array(
                    'fromuid'=>	$uid,
                    'touid'=>$feed['fromuid'],
                    'message'=>json_encode(
                        array(
                            'fid'=>$fid
                        )
                    ),
                    'type'=>3,
                    'category'=>1,
                    'toid'=>$feed['toid'],
                    'dateline'=>time(),
                );
                $ntModel->add($notice);
                //更新通知数
                $baseModel = new SnsBaseinfoModel();
                $baseModel->updateone(array(),$feed['fromuid'],array('nzcount'=>'nzcount + 1'));


            }
            return returnData(1,'点赞成功');
        }else{
            return returnData(0,'点赞失败');
        }

    }


    /**
     * 删除动态
     * @return array
     */
    public function delAction(){
        $uid = $this->uid;
        $fid = $this->fid;
        $dynamicModel = new SnsDynamicModel();
        $check = $dynamicModel->delfeed(array('fid'=>$fid,'fromuid'=>$uid));

        if($check){
            return returnData(1,'删除成功');
        }else{
            return returnData(0,'删除失败');
        }
    }
}