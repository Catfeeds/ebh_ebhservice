<?php
/**
 * 网校首页装扮配置Model
 * Author: eker
 * Email: eker-huang@outlook.com
 */
class DesignModel{
    private $db;
    public function __construct() {
        $this->db = Ebh()->db;
    }
    
    /**
     * 获取网校配置
     * @param unknown $crid
     * @param string $roomtype 网校类型
     * @param int $clientType 终端类型:0-桌面端，1-移动端
     * @param int $did 模板ID
     * @returns mixed
     */
    public function getDesignByCrid($crid,$roomtype,$clientType = 0,$did = 0){
        $wheres = array(
            '`crid`='.$crid,
            '`client_type`='.$clientType,
            '`roomtype`='.$this->db->escape($roomtype),
            '`status`=0'
        );
        if ($did > 0) {
            array_unshift($wheres, '`did`='.$did);
        } else {
            $wheres[] = '`checked`=1';
        }
        $sql = 'SELECT `did`,`uid`,`crid`,`roomtype`,`head`,`foot`,`body`,`settings`,`status`,`name`,`remark`,`checked` FROM `ebh_roomdesigns` WHERE '.implode(' AND ', $wheres).' LIMIT 1';

        $row = $this->db->query($sql)->row_array();
        return $row;
    }
    
    /**
     * 插入一条网校陪着
     * @param unknown $param
     * @return unknown|boolean
     */
    public function addDesign($param){
        $setarr = array();
        if(!empty($param['crid'])){
            $setarr['crid'] = intval($param['crid']);
        }
        if(!empty($param['roomtype'])){
            $setarr['roomtype'] = $param['roomtype'];
        }
        if(!empty($param['head'])){
            $setarr['head'] = $this->db->escape_str($param['head']);
        }
        if(!empty($param['foot'])){
            $setarr['foot'] = $this->db->escape_str($param['foot']);
        }
        if(!empty($param['body'])){
            $setarr['body'] = $this->db->escape_str($param['body']);
        }
        if(!empty($param['settings'])){
            $setarr['settings'] = $this->db->escape_str($param['settings']);
        }
        
        if(isset($param['uid'])){
            $setarr['uid'] = intval($param['uid']);
        }
        if(isset($param['status'])){
            $setarr['status'] = intval($param['status']);
        }
        if (isset($param['client_type'])) {
            $setarr['client_type'] = intval($param['client_type']);
        }

        $setarr['created_at'] = SYSTIME;
        if (isset($param['name'])) {
            $name = trim($param['name']);
            $setarr['name'] = $name == '' ? '未命名' : $name;
        }
        if (isset($param['remark'])) {
            $setarr['remark'] = trim($param['remark']);
        }
        if (isset($param['checked'])) {
            $setarr['checked'] = intval($param['checked']);
        }
        if(!empty($setarr)){
            return $this->db->insert('ebh_roomdesigns',$setarr);
        }
        
        return FALSE;
    }
    
    /**
     * 修改网校装扮配置
     * @param unknown $param
     * @param unknown $crid
     * @param int $clientType
     * @returns mixed
     */
    public function editDesign($param,$crid,$clientType = 0){
        $setarr = array();
        if(!empty($param['roomtype'])){
            $setarr['roomtype'] = $param['roomtype'];
        }
        if(!empty($param['head'])){
            $setarr['head'] = $this->db->escape_str($param['head']);
        }
        if(!empty($param['foot'])){
            $setarr['foot'] = $this->db->escape_str($param['foot']);
        }
        if(!empty($param['body'])){
            $setarr['body'] = $this->db->escape_str($param['body']);
        }
        if(!empty($param['settings'])){
            $setarr['settings'] = $this->db->escape_str($param['settings']);
        }
        if(isset($param['uid'])){
            $setarr['uid'] = intval($param['uid']);
        }
        if(isset($param['status'])){
            $setarr['status'] = intval($param['status']);
        }
        if (isset($param['name'])) {
            $name = trim($param['name']);
            $setarr['name'] = $name == '' ? '未命名' : $name;
        }
        if (isset($param['remark'])) {
            $setarr['remark'] = trim($param['remark']);
        }
        if (isset($param['checked'])) {
            $setarr['checked'] = intval($param['checked']);
        }
        $setarr['updated_at'] = SYSTIME;
        if(!empty($setarr)){
            $where = array('crid'=>intval($crid), 'client_type' => $clientType);
            if (!empty($param['did'])) {
                $where['did'] = intval($param['did']);
            }
            return $this->db->update('ebh_roomdesigns',$setarr,$where);
        }
        
        return FALSE;
    }

    /**
     * 获取网校装扮列表
     * @param int $crid
     * @param string $roomtype
     * @return array
     */
    public function getDesignList($crid, $roomtype) {
        $sql = 'SELECT `did`,`roomtype`,`status`,`client_type`,`created_at`,`updated_at`,`name`,`checked`,`remark` FROM `ebh_roomdesigns` WHERE `crid`='.$crid.' AND `status`=0 AND `roomtype`='.$this->db->escape($roomtype).' ORDER BY `did` ASC';
        $ret = Ebh()->db->query($sql)->list_array();
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }

    /**
     * 删除装扮
     * @param int $did 装扮ID
     * @param int $crid 网校ID
     * @param string $roomtype 网校类型：普通网校-edu，企业网校-com
     * @return mixed
     */
    public function deleteDesign($did, $crid, $roomtype) {
        //return Ebh()->db->delete('ebh_roomdesigns', array('did' => $did, 'crid' => $crid, 'roomtype' => $roomtype));
        return Ebh()->db->update('ebh_roomdesigns', array('status' => 1), array('did' => $did, 'crid' => $crid, 'roomtype' => $roomtype));
    }

    /**
     * 选择装扮
     * @param int $did 装扮ID,0为老版装扮
     * @param int $crid 网校ID
     * @param string $roomtype 网校类型：普通网校-edu，企业网校-com
     * @param int $clientType 客户端类型：0-PC端，1-移动端
     * @param bool $checked 是否选择
     * @return bool
     */
    public function chooseDesign($did, $crid, $roomtype, $clientType, $checked = true) {
        if ($checked) {
            if ($clientType == 1) {
                //启用移动端装扮
                $isdesign = '`isdesign` | 2';
            } else if ($did > 0) {
                //启用PC端扮装
                $isdesign = '`isdesign` | 1';
            } else {
                //启用Plate
                $isdesign = '`isdesign` & 2';
            }
        } else {
            if ($clientType == 1) {
                //取消移动端装扮
                $isdesign = '`isdesign` & 1';
            } else {
                //取消PC端扮装
                $isdesign = '`isdesign` & 2';
            }
        }

        Ebh()->db->begin_trans();
        Ebh()->db->update('ebh_roomdesigns', array('checked' => 0), array('crid' => $crid, 'roomtype' => $roomtype, 'client_type' => $clientType));
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->update('ebh_roomdesigns', array('checked' => $checked), array('did' => $did, 'crid' => $crid, 'roomtype' => $roomtype, 'client_type' => $clientType));
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->update('ebh_classrooms', array(), array('crid' => $crid), array('isdesign' => $isdesign));
        if (Ebh()->db->trans_status() === false) {
            Ebh()->db->rollback_trans();
            return false;
        }
        Ebh()->db->commit_trans();
        return true;
    }

    /**
     * 根据did获取网校装扮列表
     * @param array $param
     * @return array
     */
    public function getDesignByDid($param) {
        $did = !empty($param['did']) ? intval($param['did']) : 0;
        if(empty($did) || ($did<1)){
            return false;
        }
        $sql = 'SELECT `did`,`roomtype`,`status`,`client_type`,`created_at`,`updated_at`,`name`,`checked`,`remark` FROM `ebh_roomdesigns` WHERE `status`=0 AND `did`='.$did;
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 装扮模板列表
     * @param int $clientType 客户端类型：0-PC端，1-移动端
     * @param bool $istop 是否精选模板
     * @param int $num 获取数量，０为全部
     * @return array
     */
    public function getDesignTemplateList($clientType, $istop = true, $num = 10) {
        $wheres = array(
            '`a`.`crid`=0',
            '`a`.`issystem`=2',
            '`a`.`ishide`=0',
            '`a`.`del`=0',
            '`b`.`del`=0',
            '`b`.`ishide`=0',
            '`c`.`del`=0',
            '`c`.`ishide`=0',
            '`d`.`status`=0',
            '`d`.`client_type`='.$clientType
        );
        if ($istop) {
            $wheres[] = '`a`.`toptime`>0';
        }
        $sql = 'SELECT `a`.`path`,`a`.`width`,`a`.`height`,`a`.`aid`,`b`.`paid`,`b`.`alname`,`b`.`displayorder`,`c`.`displayorder` AS `pdisplayorder`,`c`.`alname` AS `palname`,`d`.`did`,`d`.`client_type`,`d`.`name`,`d`.`remark`  
                FROM `ebh_roomphotos` `a` 
                JOIN `ebh_roomalbums` `b` ON `b`.`aid`=`a`.`aid` 
                JOIN `ebh_roomalbums` `c` ON `c`.`aid`=`b`.`paid` 
                JOIN `ebh_roomdesigns` `d` ON `d`.`did`=`a`.`did` 
                WHERE '.implode(' AND ', $wheres).' ORDER BY `a`.`toptime` DESC';
        if ($num > 0) {
            $sql .= ' LIMIT '.$num;
        }
        $ret = Ebh()->db->query($sql)->list_array();
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }
}