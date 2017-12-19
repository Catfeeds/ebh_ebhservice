<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:22
 */
class SnsBlogUpClick{
    private $blogmodel = NULL;
    private $redis = null;
    const LIST_LEN = 50;
    public function __construct(){
        $this->db = Ebh()->snsdb;
        $this->blogmodel = new SnsBlogModel();
        $this->redis = Ebh()->cache->getRedis();
    }

    //添加一条
    public function add($param){
        $setarr = array();
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(!empty($param['bid'])){
            $setarr['bid'] = $param['bid'];
        }
        if(!empty($param['dateline'])){
            $setarr['dateline'] = $param['dateline'];
        }
        $upid =  $this->db->insert("ebh_sns_blogups",$setarr);
        if($upid>0){
            $where['bid'] = $param['bid'];
            $sparam['upcount'] = 'upcount + 1';
            $this->blogmodel->update(array(),$where,$sparam);
        }
        return $upid;
    }

    /**
     * 验证是否点过赞
     */
    public function checkclicked($uid,$bid){
        $key = $this->getrediskey("blogup_list_".$bid);
        $list = $this->redis->lrange($key,0,-1);
        foreach($list as $uparr){
            $uparrs = unserialize($uparr);
            if(!empty($uparrs)){
                if(($uparrs['uid']==$uid)&&($uparrs['bid']==$bid)){
                    return true;
                }
            }
        }
        $sql = "select count(*) count from ebh_sns_blogups where uid = $uid and bid = $bid";
        $row = $this->db->query($sql)->row_array();
        if($row['count']>0){
            return true;
        }
        return false;
    }
    /**
     * 从链表添加
     * @param unknown $param
     */
    public function addredislist($param){
        $key = $this->getrediskey("blogup_list_".$param['bid']);
        $listlen = $this->redis->llen($key);
        //dump($listlen);
        if(($listlen>=0) && ($listlen<self::LIST_LEN)){
            //存链表
            $updata = array(
                'uid'=>$param['uid'],
                'bid'=>$param['bid'],
                'dateline'=>$param['dateline']
            );
            $ret = $this->redis->rpush($key,serialize($updata));
        }else{
            //先量表同步mysql 后存链表
            $listdata =$this->redis->lrange($key,0,self::LIST_LEN);
            $msql = "INSERT INTO `ebh_sns_blogups` (`uid`,`fid`,`dateline`) VALUES ";
            foreach($listdata as $data){
                $ldata =  unserialize($data);
                $uid = $ldata['uid'];
                $bid = $ldata['bid'];
                $dateline = $ldata['dateline'];
                $msql .= "($uid,$bid,$dateline),";
            }
            $msql = rtrim($msql,",");
            //echo $msql;
            $mk = $this->db->simple_query($msql);
            if($mk){
                $this->redis->del($key);
            }
            //存链表
            $updata = array(
                'uid'=>$param['uid'],
                'bid'=>$param['bid'],
                'dateline'=>$param['dateline']
            );
            $ret = $this->redis->rpush($key,serialize($updata));
        }

        if($ret>0){
            $where['bid'] = $param['bid'];
            $sparam['upcount'] = 'upcount + 1';
            $this->blogmodel->update(array(),$where,$sparam);
        }

        return $ret;
    }

    /**
     * 获取存储键
     * @param unknown $key
     * @return string
     */
    public function getrediskey($key){
        $hashCode = $key.'_'.md5($key);
        return   $hashCode;
    }
}