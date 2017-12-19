<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 17:49
 */
class SnsDelModel{
    public function __construct(){
        $this->db = Ebh()->snsdb;
    }
    /**
     * 新增一条删除
     */
    public function add($param){
        if(!empty($param['toid'])){
            $setarr['toid'] = $param['toid'];
        }
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(!empty($param['type'])){
            $setarr['type'] = $param['type'];
        }
        if(!empty($param['dateline'])){
            $setarr['dateline'] = $param['dateline'];
        }
        return $this->db->insert("ebh_sns_dels",$setarr);
    }
    /**
     * 检测动态时候被删除
     */
    public function checkfeedsdelete($fid){
        $sql = "select count(*) count  from ebh_sns_dels where toid = $fid and type = 1 ";
        $row = $this->db->query($sql)->row_array();
        if($row['count']>0){
            return true;
        }else{
            return false;
        }
    }
}