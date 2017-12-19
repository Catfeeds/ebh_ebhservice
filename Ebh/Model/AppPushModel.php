<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:07
 */
class AppPushModel {

    /**
     * 添加
     * @param $parameters
     * @return mixed
     */
    public function add($parameters){
        $parameters['dateline'] = time();
        return Ebh()->db->insert('ebh_app_pushs',$parameters);
    }


    /**
     * 读取详情
     * @param $crid
     * @param $parameters
     */
    public function getDetail($parameters){
        $sql = 'select pid,crid,message,url,type,link_value,pushtime,dateline,status from ebh_app_pushs';


        $wherearr = array();

        if(!empty($parameters['pid'])){
            $wherearr[] = ' pid='.$parameters['pid'];
        }
        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        return Ebh()->db->query($sql)->row_array();
    }
    /**
     * 编辑
     * @param $parameters
     * @return bool
     */
    public function edit($parameters){
        $updateParams =  array();

        if(!isset($parameters['pid'])){
            return false;
        }
        if(isset($parameters['message'])){
            $updateParams['message'] = $parameters['message'];
        }
        if(isset($parameters['pushtime'])){
            $updateParams['pushtime'] = $parameters['pushtime'];
        }
        if(isset($parameters['type'])){
            $updateParams['type'] = $parameters['type'];
        }
        if(isset($parameters['url'])){
            $updateParams['url'] = $parameters['url'];
        }
        if(isset($parameters['link_value'])){
            $updateParams['link_value'] = $parameters['link_value'];
        }
        if(isset($parameters['status'])){
            $updateParams['status'] = $parameters['status'];
        }

        return Ebh()->db->update('ebh_app_pushs',$updateParams, array('pid' => intval($parameters['pid'])));

    }
    /**
     * 获取条数
     * @param $crid
     * @return mixed
     */
    public function getCount($crid,$parameters){
        $sql = 'select count(pid) as count from ebh_app_pushs';
        $wherearr = array();

        $wherearr[] = ' crid='.$crid;
        if(isset($parameters['status'])){
            $wherearr[] = ' status='.$parameters['status'];
        }
        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }

        $count = Ebh()->db->query($sql)->row_array();
        return $count['count'];
    }

    /**
     * 读取列表
     * @param $crid
     * @param $parameters
     */
    public function getList($crid,$parameters){
        $sql = 'select pid,crid,message,url,type,link_value,pushtime,dateline,status from ebh_app_pushs';


        $wherearr = array();

        $wherearr[] = ' crid='.$crid;
        if(isset($parameters['status'])){
            $wherearr[] = ' status='.$parameters['status'];
        }
        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }

        if(!empty($parameters['order'])){
            $sql.= ' order by '.$parameters['order'];
        }else{
            $sql.= ' order by pushtime desc,pid desc';
        }
        if(isset($parameters['limit'])){
            $sql .= ' limit '.$parameters['limit'];
        }

        return Ebh()->db->query($sql)->list_array();
    }


    /**
     * 删除记录
     * @param $id
     * @param $crid
     * @return bool
     */
    public function del($pid,$crid){
        if (empty($pid) || empty($crid)) {
            return false;
        }
        $where = array('`crid`='.intval($crid));
        $where = array('`pid`='.intval($pid));
        $sql = 'DELETE FROM `ebh_app_pushs` WHERE '.implode(' AND ', $where);
        Ebh()->db->query($sql, FALSE);
        if (Ebh()->db->is_fail() === true) {
            return false;
        }
        return Ebh()->db->affected_rows();
    }
}