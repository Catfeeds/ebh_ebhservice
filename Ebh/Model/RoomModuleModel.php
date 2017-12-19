<?php

/**
 * 网校模块设置
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/18
 * Time: 16:31
 */
class RoomModuleModel
{
    /**
     * 网校模块配置列表
     * @param $crid 网校ID
     * @param int $tor 模块类型:0学生模块，1教师模块
     * @return mixed
     */
    public function getList($crid, $tor = 0) {
        $crid = (int) $crid;
        if ($tor == 0) {
            //for学生
            $sql = 'SELECT `a`.`moduleid`,IF(`a`.`nickname`=\'\',`b`.`modulename`,`a`.`nickname`) AS `nickname`,`a`.`available`,`a`.`displayorder`,`a`.`ismore`,`b`.`classname`,`b`.`remark`,`b`.`modulecode`
                FROM `ebh_roommodules` `a` LEFT JOIN `ebh_appmodules` `b` ON `a`.`moduleid`=`b`.`moduleid` 
                WHERE `a`.`crid`='.$crid.' AND `a`.`tors`=0 AND `b`.`tors` IN(0,2) AND `b`.`showmode`=0';
        } else if($tor == 1) {
            //for教师
            $sql = 'SELECT `a`.`moduleid`,(CASE WHEN `a`.`nickname`<>\'\' THEN `a`.`nickname` WHEN `b`.`modulename_t`<>\'\' THEN `b`.`modulename_t` ELSE `b`.`modulename` END) AS `nickname`,`a`.`available`,`a`.`displayorder`,`a`.`ismore`,`b`.`classname`,`b`.`remark_t` AS `remark`,`b`.`modulecode`
                FROM `ebh_roommodules` `a` LEFT JOIN `ebh_appmodules` `b` ON `a`.`moduleid`=`b`.`moduleid` 
                WHERE `a`.`crid`='.$crid.' AND `a`.`tors`=1 AND `b`.`tors` IN(1,2) AND `b`.`showmode`=0';
        } else {
            //for管理员
            $sql = 'SELECT `a`.`moduleid`,IF(`a`.`nickname`=\'\',`b`.`modulename`,`a`.`nickname`) AS `nickname`,`a`.`available`,`a`.`displayorder`,`a`.`ismore`,`b`.`classname`,`b`.`remark`,`b`.`modulecode`
                FROM `ebh_roommodules` `a` LEFT JOIN `ebh_appmodules` `b` ON `a`.`moduleid`=`b`.`moduleid` 
                WHERE `a`.`crid`='.$crid.' AND `b`.`showmode`=0';
        }
        return Ebh()->db->query($sql)->list_array('moduleid');
    }

    /**
     * 设置模块
     * @param $crid 网校ID
     * @param $tor 模块类型：0-学生模块，1-教师模块
     * @param $params 模块参数
     * @return bool|int
     */
    public function setModules($crid, $tor, $params) {
        if (empty($params['moduleid'])) {
            return false;
        }
        $crid = (int) $crid;
        $tor = (int) $tor;
        $moduleid = intval($params['moduleid']);
        $formatParams = array();
        if (isset($params['nickname'])) {
            $formatParams['nickname'] = $params['nickname'];
        }
        if (isset($params['available'])) {
            if (is_numeric($params['available'])) {
                $formatParams['available'] = $params['available'] == 1 ? 1 : 0;
            } else {
                $formatParams['available'] = strtolower($params['available']) == 'true' ? 1 : 0;
            }
        }
        if (isset($params['displayorder'])) {
            $formatParams['displayorder'] = intval($params['displayorder']);
        }
        if (isset($params['ismore'])) {
            if (is_numeric($params['ismore'])) {
                $formatParams['ismore'] = $params['ismore'] == 1 ? 1 : 0;
            } else {
                $formatParams['ismore'] = strtolower($params['ismore']) == 'true' ? 1 : 0;
            }

        }
        if (empty($formatParams)) {
            return 0;
        }
        $sql = 'SELECT `moduleid` FROM `ebh_roommodules` WHERE `crid`='.$crid.' AND `moduleid`='.$moduleid.' AND `tors`='.$tor;
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret)) {
            $appmodule = Ebh()->db->query(
                'SELECT `system`,`tors`,`ismore`,`modulename`,`modulename_t` FROM `ebh_appmodules` WHERE `moduleid`='.$moduleid)
                ->row_array();
            if (empty($appmodule) || empty($appmodule['system'])) {
                return 0;
            }
            $formatParams['moduleid'] = $moduleid;
            $formatParams['crid'] = $crid;
            if ($appmodule['tors'] == 2) {
                $otherParams = $formatParams;
                if ($tor == 1) {
                    $otherParams['tors'] = 0;
                    $otherParams['available'] = 1;
                    $otherParams['nickname'] = $appmodule['modulename'];
                } else {
                    $otherParams['tors'] = 1;
                    $otherParams['available'] = 1;
                    $otherParams['nickname'] = !empty($appmodule['modulename_t']) ? $appmodule['modulename_t'] : $appmodule['modulename'];
                }
                $otherParams['ismore'] = $appmodule['ismore'];
                Ebh()->db->insert('ebh_roommodules', $otherParams);
            }
            $formatParams['tors'] = $tor;
            return Ebh()->db->insert('ebh_roommodules', $formatParams);
        } else {
            $whereStr = '`crid`='.$crid.' AND `moduleid`='.$moduleid.' AND `tors`='.$tor;
            return Ebh()->db->update('ebh_roommodules', $formatParams, $whereStr);
        }
    }

    /**
     * [getModuleList 获取当前网校的模块]
     * @param  [int] $crid [当前网校的ID]
     * @return [array]      
     */
    public function getModuleList($crid){
        $sql = "select distinct a.modulecode from ebh_appmodules a left join ebh_roommodules r on a.moduleid=r.moduleid where r.crid=$crid and r.available=1";
        $res = Ebh()->db->query($sql)->list_array();
        $moduleList = array();
        foreach($res as $value){
            foreach($value as $v){
                $moduleList[] = $v;
            }
        }
        return $moduleList ? $moduleList : false;
    }
}