<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:03
 */
class AppSlidersModel {

    /**
     * 添加
     * @param $parameters
     * @return mixed
     */
    public function add($parameters){
        return Ebh()->db->insert('ebh_app_sliders',$parameters);
    }

    /**
     * 编辑
     * @param $parameters
     * @return bool
     */
    public function edit($parameters){
        $updateParams =  array();

        if(!isset($parameters['id'])){
            return false;
        }
        if(isset($parameters['name'])){
            $updateParams['name'] = $parameters['name'];
        }
        if(isset($parameters['image_url'])){
            $updateParams['image_url'] = $parameters['image_url'];
        }
        if(isset($parameters['displayorder'])){
            $updateParams['displayorder'] = $parameters['displayorder'];
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

        return Ebh()->db->update('ebh_app_sliders',$updateParams, array('id' => intval($parameters['id'])));

    }

    /**
     * 获取轮播图数量
     * @param $crid
     * @return mixed
     */
    public function getCount($crid){
        $sql = 'select count(id) as count from ebh_app_sliders where crid = '.$crid;
        $count = Ebh()->db->query($sql)->row_array();
        return $count['count'];
    }

    /**
     * 读取列表
     * @param $crid
     * @param $parameters
     */
    public function getList($crid,$parameters){
        $sql = 'select id,crid,name,image_url,url,type,link_value,displayorder from ebh_app_sliders';


        $wherearr = array();

        $wherearr[] = ' crid='.$crid;
        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }

        if(!empty($parameters['order'])){
            $sql.= ' order by '.$parameters['order'];
        }else{
            $sql.= ' order by displayorder asc,id asc';
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
    public function del($id,$crid){
        if (empty($id) || empty($crid)) {
            return false;
        }
        $where = array('`crid`='.intval($crid));
        $where = array('`id`='.intval($id));
        $sql = 'DELETE FROM `ebh_app_sliders` WHERE '.implode(' AND ', $where);
        Ebh()->db->query($sql, FALSE);
        if (Ebh()->db->is_fail() === true) {
            return false;
        }
        return Ebh()->db->affected_rows();
    }
}