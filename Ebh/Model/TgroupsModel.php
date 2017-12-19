<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class TgroupsModel{
    /**
     * 读取列表
     * @param array $param
     * @return mixed
     */
    public function getList($param = array()){
        $sql = 'select t.groupid,t.upid,t.groupname,t.crid,t.uid,t.displayorder,t.summary from ebh_tgroups t';
        $wherearr = array();
        if(!empty($param['crid'])){
            $wherearr[] = 't.crid='.$param['crid'];
        }
        if(!empty($param['upid'])){
            $wherearr[] = 't.upid='.$param['upid'];
        }
        if(!empty($param['uid'])){
            $wherearr[] = 't.uid='.$param['uid'];
        }
        if(!empty($wherearr)){
            $sql .= ' WHERE '.implode(' AND ',$wherearr);
        }
        if(!empty($param['order'])){
            $sql.= ' order by '.$param['order'];
        }else{
            $sql.= ' order by t.displayorder asc';
        }
        if(isset($param['limit'])){
            $sql .= ' limit '.$param['limit'];
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 读取记录数
     * @param array $param
     * @return mixed
     */
    public function getCount($param = array()){
        $sql = 'select count(t.groupid) count from ebh_tgroups t';
        $wherearr = array();
        if(!empty($param['crid'])){
            $wherearr[] = 't.crid='.$param['crid'];
        }
        if(!empty($param['upid'])){
            $wherearr[] = 't.upid='.$param['upid'];
        }
        if(!empty($param['uid'])){
            $wherearr[] = 't.uid='.$param['uid'];
        }
        if(!empty($wherearr)){
            $sql .= ' WHERE '.implode(' AND ',$wherearr);
        }
        $res = Ebh()->db->query($sql)->row_array();
        return $res['count'];
    }

    /**
     * 读取教研组详细信息
     * @param $groupid
     * @return mixed
     */
    public function getDetail($groupid){
        $sql = 'select t.groupid,t.upid,t.groupname,t.crid,t.uid,t.displayorder,t.summary from ebh_tgroups t where t.groupid='.$groupid;
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 添加教研组
     * @param $param
     * @return int
     */
    public function add($param){
        if(empty($param)){
            return 0;
        }
        return Ebh()->db->insert('ebh_tgroups',$param);
    }

    /**
     * 修改信息
     * @param array $param
     * @param array $where
     * @return int
     */
    public function edit($param = array(),$where = array()){
        if(empty($param)||empty($where)){
            return 0;
        }
        return Ebh()->db->update('ebh_tgroups',$param,$where);
    }

    /**
     * 删除教研组
     * @param $param
     * @return int
     */
    public function del($param){
        if(empty($param)){
            return 0;
        }
        return Ebh()->db->delete('ebh_tgroups',$param);
    }
}