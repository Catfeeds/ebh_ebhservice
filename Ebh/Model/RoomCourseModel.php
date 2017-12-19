<?php
/**
 * 网校课程类.
 * Author: ycq
 */
class RoomCourseModel{
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
}