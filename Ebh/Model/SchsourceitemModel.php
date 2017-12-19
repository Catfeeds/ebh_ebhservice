<?php
/**
 * 企业选课，服务项关联表
 */
class SchsourceitemModel{
    /**
     * 获取企业选课课程分类ID集
     * @param int $crid 网校ID
     * @return array
     */
    public function getCategoryIds($crid) {
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`del`=0',
            '`b`.`status`=0',
            '`c`.`del`=0',
            '`c`.`power`=0',
            '`c`.`folderlevel`>1'
        );
        $sql = 'SELECT `a`.`sourcecrid`,`b`.`pid`,`b`.`sid` FROM `ebh_schsourceitems` `a` JOIN `ebh_pay_items` `b` ON `b`.`itemid`=`a`.`itemid` JOIN `ebh_folders` `c` ON `c`.`folderid`=`b`.`folderid` WHERE '.implode(' AND ', $wheres);
        $ret = Ebh()->db->query($sql)->list_array();
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }

    public function getItemByFolderid($folderid, $crid) {
        $wheres = array(
            '`a`.`crid`='.$crid,
            '`a`.`del`=0',
            '`b`.`status`=0',
            '`c`.`folderid`='.$folderid,
            '`c`.`del`=0',
            '`c`.`folderlevel`>1',
            '`c`.`power`=0',
            '`d`.`status`=1',
            'IFNULL(`e`.`ishide`,0)=0'
        );
        $sql = 'SELECT `a`.`price`,`a`.`sourcecrid`,`b`.`itemid`,`c`.`img` 
                FROM `ebh_schsourceitems` `a` 
                JOIN `ebh_pay_items` `b` ON `b`.`itemid`=`a`.`itemid` 
                JOIN `ebh_folders` `c` ON `c`.`folderid`=`b`.`folderid` 
                JOIN `ebh_pay_packages` `d` ON `d`.`pid`=`b`.`pid`
                LEFT JOIN `ebh_pay_sorts` `e` ON `e`.`sid`=`b`.`sid` 
                WHERE '.implode(' AND ', $wheres). ' ORDER BY `b`.`itemid` DESC LIMIT 1';
        return Ebh()->db->query($sql)->row_array();
    }
}
