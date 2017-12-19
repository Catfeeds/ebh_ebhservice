<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 16:43
 */
class SnsFeedsModel{


    public function __construct(){
        $this->db = Ebh()->snsdb;
    }

    /**
     * 更新一条动态
     */
    public function update($param,$where){
        if(empty($where['fid']) || empty($param['message'])){
            return false;
        }
        if(!empty($param['ip'])){
            $setarr['ip'] = $param['ip'];
        }
        $wherearr['fid'] = $where['fid'];
        $setarr['message'] = $param['message'];
        return $this->db->update("ebh_sns_feeds",$setarr,$wherearr);
    }
    /**
     * 查询feeds
     */
    public function getfeedslist($param){
        $where = array();
        $sql = "select fid,fromuid,message,category,toid,dateline from ebh_sns_feeds";
        if(!empty($param['fid'])){
            $where[] = " fid = ".$param['fid'];
        }
        if(!empty($param['fidarr'])){
            $where[] = " fid in( ".implode(",", $param['fidarr'])." )";
        }
        if(!empty($param['condition'])){
            $where[] = $param['condition'];
        }

        //过滤删除 不符合要求的动态
        $where[] = " status = 0";

        if(!empty($where)){
            $sql.=" where ".implode(" AND ",$where);
        }

        if(!empty($param['orderbby'])){
            $sql.=" ORDER BY ".$param['orderbby'];
        }else{
            $sql.=" ORDER BY  fid DESC";
        }

        if(!empty($param['limit'])){
            $sql.=" LIMIT ".$param['limit'];
        }else{
            $sql.=" LIMIT 10 ";
        }
        //echo $sql;
        return $this->db->query($sql)->list_array();

    }

    /**
     * 添加动态
     * @param $param
     * @return mixed
     */
    public function add($param){
        $setarr = array();

        if(!empty($param['fromuid'])){
            $setarr['fromuid'] = $param['fromuid'];
        }
        if(!empty($param['message'])){
            $setarr['message'] = $param['message'];
        }
        if(!empty($param['category'])){
            $setarr['category'] = $param['category'];
        }
        if(!empty($param['toid'])){
            $setarr['toid'] = $param['toid'];
        }
        if(!empty($param['dateline'])){
            $setarr['dateline'] = $param['dateline'];
        }
        if(!empty($param['ip'])){
            $setarr['ip'] = $param['ip'];
        }
        return  $this->db->insert("ebh_sns_feeds",$setarr);
    }

    /**
     * 获取一条动态信息
     */
    public function getFeedsByFid($fid){
        $sql = "select f.fid,f.fromuid,f.message,f.category,f.toid,f.dateline,b.outid,b.pfid,b.tfid,b.upcount,b.cmcount,b.zhcount,b.iszhuan from ebh_sns_feeds f left join ebh_sns_outboxs b on f.fid = b.fid where f.fid = $fid and f.status = 0 ";
        return $this->db->query($sql)->row_array();
    }


    /**
     * 删除一条动态(逻辑删除)
     */
    public function delete($param){
        $where = '1';
        if(!empty($param['fid'])){
            $where .= ' AND fid = '.$param['fid'];
        }
        if(!empty($param['fromuid'])){
            $where .= ' AND fromuid = '.$param['fromuid'];
        }
        if(!empty($param['toid'])){
            $where .= ' AND toid = '.$param['toid'];
        }
        if(!empty($param['category'])){
            $where .= ' AND category IN ('.implode(',', $param['category']).')';
        }
        return $this->db->update("ebh_sns_feeds",array("status"=>1),$where);
    }
}