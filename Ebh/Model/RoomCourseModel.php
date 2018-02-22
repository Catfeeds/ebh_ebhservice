<?php
/**
 * 网校课程类.
 * Author: ycq
 */
class RoomCourseModel{
    /**
     * 获取课件观看权限
     * @param $cwid
     * @param $crid
     * @return mixed
     */
    public function getClassidByCwid($cwid,$crid){
        $sql = 'select classids from ebh_roomcourses where cwid='.$cwid . ' and crid='.$crid;
        return Ebh()->db->query($sql)->row_array();
    }
    /**
     * 设置免费试听课件
     * @param array $cwids 课件ID集
     * @param int $crid 网校ID
     * @return int 设置成功数
     */
    public function setFreeCourseware($cwids, $crid) {
        $affectedRows = 0;
        /*foreach ($cwids as $cwid) {
            $affectedRows += Ebh()->db->update('ebh_roomcourses', array('isfree' => 1), '`cwid`='.$cwid.' AND `crid`='.$crid);
        }*/
        $affectedRows = Ebh()->db->update('ebh_roomcourses', array('isfree' => 1), '`cwid` IN('.implode(',', $cwids).')');
        return $affectedRows;
    }

    /**
     * 获取视频
     * @param int $cwid 课件ID
     * @param int $crid 网校ID
     * @return mixed
     */
    public function getVedio($cwid, $crid) {
        $wheres = array(
            //'`a`.`crid`='.$crid,
            '`a`.`cwid`='.$cwid,
            '`b`.`status`=1',
            '`b`.`ism3u8`=1'
        );
        $sql = 'SELECT `a`.`isfree`,`b`.`cwid`,`b`.`logo`,`b`.`thumb`,`b`.`cwurl`,`b`.`cwsize`,`b`.`summary` FROM `ebh_roomcourses` `a` JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` WHERE '.implode(' AND ', $wheres);
        return Ebh()->db->query($sql)->row_array();
    }
    /**
     * @describe:用课程id获取课件列表
     * @Author:tzq
     * @Date:2018/01/09
     * @param string $folderids 课程id，多个逗号隔开
     * @return  array
     */
    public function getCwList($param){
        if(empty($param['folderids']))
            return false;
        $where = [];
        $where[] = '`folderid` IN('.$param['folderids'].')';
        if(!empty($param['crid'])){
            $where[] = '`crid`='.$param['crid'];
        }
        $sql = 'SELECT `cwid`,`folderid` FROM `ebh_roomcourses` ';
        $sql .= 'WHERE '.implode(' AND ',$where);
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * @describe:用课件id获取课程id
     * @Author:tzq
     * @Date:2018/01/10
     * @param int $cwid 课程id
     * @return int 课程Id
     */
    public function getFoderid($cwid){
      if(empty($cwid)){
          return 0;
      }
      $sql = 'SELECT `folderid` FROM `ebh_roomcourses` where `cwid`='.$cwid.' limit 1';
      $ret =  Ebh()->db->query($sql)->row_array('folderid');
      return isset($ret['folderid'])&& $ret['folderid']>0?$ret['folderid']:0;
    }
}