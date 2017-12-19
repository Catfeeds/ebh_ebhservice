<?php
/**
 * ebhservice.
 * Author: ycq
 */
class ClassTeacherModel{
    /**
     * 获取教师任课班级列表
     * @param int $uid 教师ID
     * @param int $crid 网校ID
     * @return mixed
     */
    public function getClassesForTeacher($uid, $crid) {
        $sql = 'SELECT `b`.`classid`,`b`.`classname` 
                FROM `ebh_classteachers` `a` JOIN `ebh_classes` `b` ON `b`.`classid`=`a`.`classid` 
                WHERE `a`.`uid`='.$uid.' AND `b`.`crid`='.$crid.' ORDER BY `b`.`displayorder` DESC,`b`.`classid` DESC';
        return Ebh()->db->query($sql)->list_array('classid');
    }
}