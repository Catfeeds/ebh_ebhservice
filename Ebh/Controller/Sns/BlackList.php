<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:08
 */
class BlackListController extends Controller {
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'q'  =>  array('name'=>'q','default'=>''),
                'page'  =>  array('name'=>'page','type'=>'int','default'=>1),
                'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
            ),
            'addAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'fuid'  =>  array('name'=>'fuid','require'=>true,'type'=>'int'),
            ),
            'cancelAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'fuid'  =>  array('name'=>'fuid','require'=>true,'type'=>'int'),
            ),
        );
    }

    /**
     * 获取指定用户黑名单
     * @return array
     */
    public function listAction(){
        $blackListMolde = new SnsBlackListModel();
        $baseinfoModel = new SnsBaseinfoModel();
        $q = trim($this->q);
        $regex = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
        $q =  preg_replace($regex,"",$q);


        $total = $blackListMolde->getlistcount(array('fromuid'=>$this->uid,'state'=>0));
        if(empty($q)){
            $page = $this->page;
            $pagesize = $this->pagesize;
            $param = array(
                'fromuid'=>$this->uid,
                'state'=>0,
                'limit'=>max(0,($page-1)*$pagesize)." , $pagesize"
            );

            $blacklists = $blackListMolde->getlist($param);
            //组装个人信息
            if(!empty($blacklists)){
                $blacklists = $baseinfoModel->getUserinfo($blacklists,"touid");
            }
            $count = $total;
        }else{
            $param = array(
                'fromuid'=>$this->uid,
                'state'=>0,
            );
            $blacklists = $blackListMolde->getlist($param);
            if(!empty($blacklists)){
                $blacklists = $baseinfoModel->getUserinfo($blacklists,"touid");
                foreach($blacklists as $key=>$item){
                    if(!(preg_match("/$q/", $item['username'])
                        ||preg_match("/$q/", $item['realname'])
                        ||preg_match("/$q/", $item['nickname'])
                    )){
                        unset($blacklists[$key]);
                    }
                }
            }
            $count = count($blacklists);

        }

        return array(
            'total' =>  $total,
            'count' =>  $count,
            'blacklists'    =>  $blacklists
        );
    }

    /**
     * 添加黑名单
     * @return array
     */
    public function addAction(){
        $uid = $this->uid;
        $fuid = $this->fuid;
        //写入redis缓存
        $exist = -1;
        $key = 'blacklist_'.$uid.'_'.md5($uid);
        $cache = Ebh()->cache->getRedis();
        $data = $cache->lrange($key,0,-1);

        if(!empty($data)){
            //检测是否存在
            foreach ($data as $k=> $value){
                if($value == $fuid){
                    $exist = $k;
                    break;
                }
            }
        }
        if($exist == -1){
            $result = $cache->lpush($key,$fuid);
        }
        $blacklistModel = new SnsBlackListModel();
        $hascount = $blacklistModel->getlistcount(array('touid'=>$fuid,'fromuid'=>$uid));
        if($hascount > 0){
            $result = $blacklistModel->update(array('state'=>0),array('fromuid'=>$uid,'touid'=>$fuid));
        }else{
            $result = $blacklistModel->add(array('fromuid'=>$uid,'touid'=>$fuid,'dateline'=>time()));
        }


        if(!$result){
            return returnData(0,'添加失败');
        }
        return returnData(1,'添加成功');
    }

    /**
     * 解除黑名单
     * @return array
     */
    public function cancelAction(){
        $uid = $this->uid;
        $fuid = $this->fuid;

        //同步缓存操作
        $key = 'blacklist_'.$uid.'_'.md5($uid);
        $cache = Ebh()->cache->getRedis();
        $cache->lrem($key,$fuid);

        $blacklistModel = new SnsBlackListModel();

        $result = $blacklistModel->update(array('state'=>1),array('fromuid'=>$uid,'touid'=>$fuid));

        if(!$result){
            return returnData(0,'解除失败');
        }
        return returnData(1,'解除成功');
    }
}