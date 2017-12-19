<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 16:49
 */
class SnsOutboxModel{
    public function __construct(){
        $this->db = Ebh()->snsdb;
    }
    /**
     * outbox添加
     */
    public function add($param){
        $setarr = array();
        if(!empty($param['fid'])){
            $setarr['fid'] = $param['fid'];
        }
        if(!empty($param['uid'])){
            $setarr['uid'] = $param['uid'];
        }
        if(!empty($param['pfid'])){
            $setarr['pfid'] = $param['pfid'];
        }
        if(!empty($param['tfid'])){
            $setarr['tfid'] = $param['tfid'];
        }
        if(!empty($param['iszhuan'])){
            $setarr['iszhuan'] = $param['iszhuan'];
        }
        if(!empty($param['dateline'])){
            $setarr['dateline'] = $param['dateline'];
        }
        if(!empty($param['ip'])){
            $setarr['ip'] = $param['ip'];
        }
        return  $this->db->insert("ebh_sns_outboxs",$setarr);
    }


    /**
     * outbox修改动态数
     *
     */
    public function update($param,$fid,$type='add'){
        $setarr = array();
        if($type=='add'){
            $expression = '+';
        }else{
            $expression = '-';
        }
        if(!empty($param['upcount'])){
            $setarr = array('upcount'=>'upcount '.$expression.' 1');
        }
        if(!empty($param['cmcount'])){
            $setarr = array('cmcount'=>'cmcount '.$expression.' 1');
        }
        if(!empty($param['zhcount'])){
            $setarr = array('zhcount'=>'zhcount '.$expression.' 1');
        }

        return $this->db->update('ebh_sns_outboxs',array(),array("fid"=>$fid),$setarr);
    }

    /**
     * 查询发件箱
     */
    public function getoutboxlist($param){
        $where = array();
        $sql = "select outid,fid,pfid,tfid,iszhuan,uid,upcount,cmcount,zhcount,dateline from ebh_sns_outboxs ";
        if(empty($param['uid'])&&empty($param['uidarr'])){
            return array();
        }else{
            if(!empty($param['uid'])){
                $where[] = " uid = ".$param['uid'];
            }
            if(!empty($param['uidarr'])){
                $where[] = " uid in (  ".implode(",", $param['uidarr'])." )";;
            }
        }

        if(!empty($where)){
            $sql.=" WHERE ".implode(" AND ", $where);
        }
        //echo $sql;
        return $this->db->query($sql)->list_array();
    }

    /**
     * 获取一条记录
     */
    public function getoutboxbyfid($fid){
        $sql = "select outid,fid,pfid,tfid,iszhuan,uid,upcount,cmcount,zhcount,dateline from ebh_sns_outboxs where fid = $fid ";
        return $this->db->query($sql)->row_array();
    }
}