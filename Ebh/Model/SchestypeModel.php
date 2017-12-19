<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 13:09
 */
class SchestypeModel{
    /**
     *获取列表
     */
    public function getEstypeList($param) {
        $sql = 'select e.id,e.crid,e.estype,e.order from ebh_schexam_estype e';
        $wherearr = array();
        if(!empty($param['id'])) {
            $wherearr[] = 'e.id ='.$param['id'];
        }
        if(isset($param['crid'])){
            $wherearr[] = 'e.crid = '.$param['crid'];
        }
        if(isset($param['uid'])){
            $wherearr[] = 'e.uid = '.$param['uid'];
        }
        if(isset($param['dtag'])){
            $wherearr[] = 'e.dtag = '.$param['dtag'];
        } else {
            $wherearr[] = 'e.dtag = 0';
        }
        if(!empty($wherearr))
            $sql .= ' where '.implode(' and ',$wherearr);
        if(!empty($param['order']))
            $sql .= ' order by '.$param['order'];
        else
            $sql .= ' order by e.id desc';
        if(!empty($param['limit']))
            $sql .= ' limit '.$param['limit'];
        else
            $sql .= ' limit 0,10';
        return Ebh()->db->query($sql)->list_array();
    }
}