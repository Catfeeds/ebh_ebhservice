<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 13:17
 */
class SnsUpclickModel{
    private $outboxmodel = NULL;
    private $redis = null;
    const LIST_LEN = 50;
    public function __construct(){
        $this->db = Ebh()->snsdb;
        $this->outboxmodel = new SnsOutboxModel();
        $this->redis = Ebh()->cache->getRedis();
    }


    //添加一条
    public function add($param){
        $setarr = array();
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(!empty($param['fid'])){
            $setarr['fid'] = $param['fid'];
        }

        if(!empty($param['dateline'])){
            $setarr['dateline'] = $param['dateline'];
        }

        $upid =  $this->db->insert("ebh_sns_ups",$setarr);
        if($upid>0){
            $this->outboxmodel->update(array('upcount'=>true),$param['fid']);
        }
        return $upid;
    }

    /**
     * 验证是否点过赞
     */
    public function checkclicked($uid,$fid){
        $key = $this->getrediskey("up_list_".$fid);
        $list = $this->redis->lrange($key,0,-1);
        foreach($list as $uparr){
            $uparrs = unserialize($uparr);
            if(!empty($uparrs)){
                if(($uparrs['uid']==$uid)&&($uparrs['fid']==$fid)){
                    return true;
                }
            }
        }
        $sql = "select count(*) count from ebh_sns_ups where uid = $uid and fid = $fid";
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
        $key = $this->getrediskey("up_list_".$param['fid']);
        $listlen = $this->redis->llen($key);
        //dump($listlen);
        if(($listlen>=0) && ($listlen<self::LIST_LEN)){
            //存链表
            $updata = array(
                'uid'=>$param['uid'],
                'fid'=>$param['fid'],
                'dateline'=>$param['dateline']
            );
            $ret = $this->redis->rpush($key,serialize($updata));

        }else{
            //先量表同步mysql 后存链表
            $listdata =$this->redis->lrange($key,0,self::LIST_LEN);
            $msql = "INSERT INTO `ebh_sns_ups`(`uid`,`fid`,`dateline`)VALUES";
            foreach($listdata as $data){
                $ldata =  unserialize($data);
                $uid = $ldata['uid'];
                $fid = $ldata['fid'];
                $dateline = $ldata['dateline'];
                $msql.="($uid,$fid,$dateline),";
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
                'fid'=>$param['fid'],
                'dateline'=>$param['dateline']
            );
            $ret = $this->redis->rpush($key,serialize($updata));
        }

        if($ret>0){
            $this->outboxmodel->update(array('upcount'=>true),$param['fid']);
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