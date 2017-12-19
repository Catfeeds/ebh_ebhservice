<?php
/**
 * 折扣Model类
 * Author: songpeng
 * Date: 2017/04/28
 */
class DiscountModel  {

    /**
     * 检查网校是否存在
     * @param $crid
     * @return bool
     */
    public function exists($crid) {
        $sql = 'SELECT `crid` FROM `ebh_systemsettings` WHERE `crid`='.intval($crid);
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return true;
        }
        return false; 
    }

    /**
     * 检查网校开关状态
     * @param $crid
     * @return bool
     */
    public function checkSwitch($crid){
        $sql='SELECT `iscollect` FROM `ebh_systemsettings` WHERE `crid`='.intval($crid);
        $ret = Ebh()->db->query($sql)->row_array();
        if ($ret['iscollect'] ==0) {
            return false;
        }
        return true;
    }

    /**
     * 添加网校
     * @param $crid
     * @return bool
     */
    public function add($crid){
        $sql='INSERT INTO `ebh_systemsettings` (crid) VALUES ($crid)';
        return Ebh()->db->query($sql);       
    }

    /**
     * 更新网校开关
     * @param $crid,$flag
     * @return bool
     */
    public function updateSwitch($crid,$flag){
        $param['iscollect'] = $flag;
        $wherearr['crid'] = $crid;
        return Ebh()->db->update('ebh_systemsettings',$param,$wherearr);   
        
    }
 
    /**
     * 更新网校折扣
     * @param $crid,$param(折扣信息json)
     * @return bool
     */
    public function update($crid,$param){
        $sql="UPDATE ebh_systemsettings SET discounts= '$param' WHERE crid= $crid";
        Ebh()->db->query($sql);
        return true;
        
    }
    
    /**
     * 获得网校折扣列表
     * @param $crid
     * @return json 折扣信息
     */
    public function getlist($crid){
        $sql='SELECT `discounts` FROM `ebh_systemsettings` WHERE `crid`='.$crid;
        return Ebh()->db->query($sql)->row_array();     
    }
     



}

