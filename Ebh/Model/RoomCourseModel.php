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

    /**
     * 获取直播课件
     * @param array $params 筛选条件
     * @param int $crid 网校ID
     * @param null $limit 限量条件
     * @return array
     */
    public function getCoursewareList($params, $crid, $limit = null) {
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`b`.`status`=1',
            '`b`.`islive`=1'
        );
        if (!empty($params['classid'])) {
            $wheres[] = '(`a`.`classids`=\'\' OR `a`.`classids`=\'0\' OR FIND_IN_SET('.$params['classid'].',`a`.`classids`))';
        }
        if (isset($params['s']) && $params['s'] != '') {
            $wheres[] = '`b`.`title` LIKE '.Ebh()->db->escape('%'.$params['s'].'%');
        }
        if (isset($params['start'])) {
            $wheres[] = 'IF(`b`.`truedateline`=0,`b`.`submitat`,`b`.`truedateline`)>='.$params['start'];
        }
        if (isset($params['end'])) {
            $wheres[] = 'IF(`b`.`truedateline`=0,`b`.`submitat`,`b`.`truedateline`)<='.$params['end'];
        }
        if (!empty($params['folderids']) && !empty($params['cwids'])) {
            $wheres[] = '(`a`.`folderid` IN('.implode(',', $params['folderids']).') OR `a`.`cwid` IN('.implode(',', $params['cwids']).'))';
        } else if (!empty($params['folderids'])) {
            $wheres[] = '`a`.`folderid` IN('.implode(',', $params['folderids']).')';
        } else if (!empty($params['cwids'])) {
            $wheres[] = '`a`.`cwid` IN('.implode(',', $params['cwids']).')';
        }
        $sql = 'SELECT `a`.`folderid`,`b`.`title`,`b`.`cwid`,IF(`b`.`truedateline`=0,`b`.`submitat`,`b`.`truedateline`) AS `truedateline`,IF(TO_DAYS(FROM_UNIXTIME(IF(`b`.`truedateline`=0,`b`.`submitat`,`b`.`truedateline`)))=TO_DAYS(NOW()),1,0) AS `t` FROM `ebh_roomcourses` `a` JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` WHERE '.implode(' AND ', $wheres).' ORDER BY `t` DESC,`truedateline` DESC,`b`.`cwid` DESC';
        if (!empty($limit)) {
            $top = $offset = 0;
            if (is_array($limit)) {
                $top = max(1, isset($limit['pagesize']) ? intval($limit['pagesize']) : 1);
                $offset = (max(1,isset($limit['page']) ? intval($limit['page']) : 1) - 1) * $top;
            } else if (is_numeric($limit)) {
                $top = max(1, intval($limit));
            }
            $sql .= ' LIMIT '.$offset.','.$top;
        }
        $ret = Ebh()->db->query($sql)->list_array('cwid');
        if (!empty($ret)) {
            return $ret;
        }
        return array();
    }

    /**
     * 获取直播课件数量
     * @param array $params 筛选条件
     * @param $crid 网校ID
     * @return int
     */
    public function getCoursewareCount($params, $crid) {
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`b`.`status`=1',
            '`b`.`islive`=1'
        );
        if (!empty($params['classid'])) {
            $wheres[] = '(`a`.`classids`=\'\' OR `a`.`classids`=\'0\' OR FIND_IN_SET('.$params['classid'].',`a`.`classids`))';
        }
        if (isset($params['s']) && $params['s'] != '') {
            $wheres[] = '`b`.`title` LIKE '.Ebh()->db->escape('%'.$params['s'].'%');
        }
        if (isset($params['start'])) {
            $wheres[] = 'IF(`b`.`truedateline`=0,`b`.`submitat`,`b`.`truedateline`)>='.$params['start'];
        }
        if (isset($params['end'])) {
            $wheres[] = 'IF(`b`.`truedateline`=0,`b`.`submitat`,`b`.`truedateline`)<='.$params['end'];
        }
        if (!empty($params['folderids']) && !empty($params['cwids'])) {
            $wheres[] = '(`a`.`folderid` IN('.implode(',', $params['folderids']).') OR `a`.`cwid` IN('.implode(',', $params['cwids']).'))';
        } else if (!empty($params['folderids'])) {
            $wheres[] = '`a`.`folderid` IN('.implode(',', $params['folderids']).')';
        } else if (!empty($params['cwids'])) {
            $wheres[] = '`a`.`cwid` IN('.implode(',', $params['cwids']).')';
        }
        $sql = 'SELECT COUNT(1) AS `c` FROM `ebh_roomcourses` `a` JOIN `ebh_coursewares` `b` ON `b`.`cwid`=`a`.`cwid` WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret['c'])) {
            return intval($ret['c']);
        }
        return 0;
    }
}