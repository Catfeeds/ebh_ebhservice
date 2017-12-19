<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 17:44
 */
class SnsBlackListModel{
    public function __construct(){
        $this->db = Ebh()->snsdb;
    }

    //获取某一用户黑名单列表
    public function getlist($param){
        $sql = "select fromuid, touid from `ebh_sns_blacklists` b";
        if(!empty($param['fromuid'])){
            $where[] = 'b.fromuid = '. $param['fromuid'];
        }
        if(!empty($param['touid'])){
            $where[] = 'b.touid = '.$param['touid'];
        }
        if(isset($param['state'])){
            $where[] = 'b.state='. $param['state'];
        }
        if(!empty($where)){
            $sql.=" where ".implode(" AND ",$where);
        }
        if(!empty($param['orderbby'])){
            $sql.=" ORDER BY ".$param['orderbby'];
        }else{
            $sql.=" ORDER BY b.dateline DESC";
        }
        if(!empty($param['limit'])){
            $sql.=" LIMIT ".$param['limit'];
        }
        return $this->db->query($sql)->list_array();
    }
    //获取黑名单总数
    public function getlistcount($param){
        $sql = "select count(*) count from `ebh_sns_blacklists` b";
        if(!empty($param['fromuid'])){
            $where[] = 'b.fromuid = '. $param['fromuid'];
        }
        if(!empty($param['touid'])){
            $where[] = 'b.touid = '.$param['touid'];
        }
        if(isset($param['state'])){
            $where[] = 'b.state='. $param['state'];
        }
        if(!empty($where)){
            $sql.=" where ".implode(" AND ",$where);
        }
        $row =  $this->db->query($sql)->row_array();
        if(empty($row['count'])){
            $row['count'] = 0;
        }
        return  $row['count'];
    }
    //添加一条
    public function add($param){
        $setarr = array();
        if(!empty($param['fromuid'])){
            $setarr['fromuid'] = $param['fromuid'];
        }
        if(!empty($param['touid'])){
            $setarr['touid'] = $param['touid'];
        }
        if(!empty($param['dateline'])){
            $setarr['dateline'] = $param['dateline'];
        }
        return $this->db->insert("ebh_sns_blacklists",$setarr);
    }
    //更新一条
    public function update($param,$where,$sparam=array()){
        $setarr = array();
        if(isset($param['state'])){
            $setarr['state'] = $param['state'];
        }
        if(!empty($where['touid'])){
            $wherearr['touid'] = $where['touid'];
        }
        if(!empty($where['fromuid'])){
            $wherearr['fromuid'] = $where['fromuid'];
        }
        return $this->db->update("ebh_sns_blacklists",$setarr,$wherearr);
    }
}