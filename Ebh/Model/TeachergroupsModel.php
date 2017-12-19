<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class TeachergroupsModel{

    /**
     * 查看指定教研组名称是否存在
     * @param $crid
     * @param $groupName
     * @return mixed
     */
    public function exists($crid,$groupName,$groupid){
        if($groupid > 0){
            $where = ' and groupid != '.$groupid;
        }
        $sql = 'select 1 from ebh_tgroups where crid='.$crid.' and groupname='."'".$groupName."'".$where.' limit 1';
        return Ebh()->db->query($sql)->row_array();
    }
    /**
     * 设置教研组教师
     * @param $param
     */
    public function setTeachers($param){
        if(!empty($param['groupid'])){
            $wherearr['groupid'] = $param['groupid'];
            Ebh()->db->delete('ebh_teachergroups',$wherearr);
        }
        foreach($param['tids'] as $tid){
            $tfarr = array('tid'=>$tid,'groupid'=>$param['groupid'],'crid'=>$param['crid']);
            Ebh()->db->insert('ebh_teachergroups',$tfarr);
        }
        return true;
    }

    /**
     *获取分组列表
     */
    public function getList($param = array()){
        $sql = 'select tg.groupid,tg.tid,tg.crid,u.username,u.realname from ebh_teachergroups tg left join ebh_users u on tg.tid = u.uid';
        $wherearr = array();
        if(!empty($param['groupid'])){
            $wherearr[] = 'tg.groupid='.$param['groupid'];
        }
        if(!empty($param['crid'])){
            $wherearr[] = 'tg.crid='.$param['crid'];
        }
        if(!empty($param['tid'])){
            $wherearr[] = 'tg.tid='.$param['tid'];
        }
        if(!empty($wherearr)){
            $sql .= ' WHERE '.implode(' AND ',$wherearr);
        }

        return Ebh()->db->query($sql)->list_array();
    }


    /**
     * 删除教研组内的教师
     * @param $param
     * @return int
     */
    public function del($param){
        if(empty($param)){
            return 0;
        }
        return Ebh()->db->delete('ebh_teachergroups',$param);
    }
}