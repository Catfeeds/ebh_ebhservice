<?php

class ProjectfeedbackModel{

    private $db;
    public function __construct() {
        $this->db = Ebh()->db;
    }
    /**
     * 反馈添加
     * @param $param
     * @return mixed
     */
    public function add($param){
        $list = array();
        if(!empty($param['dateline']) && intval($param['dateline']) > 0){
            $list['dateline'] = intval($param['dateline']);
        }
        if(!empty($param['uid']) && intval($param['uid']) > 0){
            $list['uid'] = intval($param['uid']);
        }
        if(!empty($param['loginip']) && intval($param['loginip']) > 0){
            $list['loginip'] = $param['loginip'];
        }
        if(!empty($param['feedback'])){
            $list['feedback'] = $param['feedback'];
        }
        if(!empty($param['email'])){
            $list['email'] = $param['email'];
        }
        if(!empty($param['schoolname'])){
            $list['schoolname'] = $param['schoolname'];
        }
        if(!empty($param['urole']) && intval($param['urole']) > 0){
            $list['urole'] = intval($param['urole']);
        }
        $result = $this->db->insert('ebh_projectfeedbacks', $list);
        return $result;
    }

    /**
     * 获取一个用户一段时间内的反馈次数
     * @param $param
     * @return mixed
     */
    public function getFeedbackCount($param){
        $sql = 'select count(*) count from ebh_projectfeedbacks';
        if(!empty($param['uid']) && intval($param['uid']) > 0){
            $wherearr[] = 'uid='.intval($param['uid']);
        }
        if(!empty($param['datelinemax'])){
            $wherearr[] = 'dateline<'.intval($param['datelinemax']);
        }
        if(!empty($param['datelinemin'])){
            $wherearr[] = 'dateline>'.intval($param['datelinemin']);
        }
        if(!empty($wherearr)){
            $sql .= ' WHERE '.implode(' AND ', $wherearr);
        }
        $result = $this->db->query($sql)->row_array();
        return $result['count'];
    }
} 