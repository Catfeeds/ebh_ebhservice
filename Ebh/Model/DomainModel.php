<?php

/**
 * 网校域名
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/16
 * Time: 13:35
 */
class DomainModel
{
    private $db;
    function __construct()
    {
        $this->db = Ebh()->db;
    }

    /**
     * 域名申请结果
     * @param $crid
     * @return array
     */
    function getDomainCheck($crid)
    {
        $crid = (int) $crid;
        //查看ebh_classrooms表中的fulldomain,有值就说明以设置自定义域名
        $classroominfo = Ebh()->db->query('SELECT `fulldomain`,`icp`,`domain` FROM `ebh_classrooms` WHERE `crid`='.$crid)->row_array();
        if (empty($classroominfo)) {
            return false;
        }
        if (!empty($classroominfo['fulldomain'])) {
            $classroominfo['status'] = 1;
            return $classroominfo;
        }

        $fulldomain = $this->db->query('SELECT `fulldomain` FROM `ebh_domainchecks` WHERE `crid`='.$crid)->row_array();
        if (empty($fulldomain['fulldomain'])) {
            //未操作
            return array('status' => -1);
        }
        $check_result = $this->db->query(
            "SELECT `admin_status`,`teach_status`,`admin_remark` FROM `ebh_billchecks` WHERE `toid`=$crid AND `type`=13 AND `del`=0")
            ->row_array();
        if (empty($check_result)) {
            //申请中
            return array('status' => 0, 'fulldomain' => $fulldomain['fulldomain'], 'domain' => $classroominfo['domain']);
        }
        if ($check_result['admin_status'] == 1 || $check_result['teach_status'] == 1) {
            //审核通过
            $status = 1;
        } else if ($check_result['admin_status'] == 0 && $check_result['teach_status'] == 0) {
            //申请中
            $status = 0;
        } else {
            //审核通不过
            $status = 2;
            $ret['remark'] = $check_result['admin_remark'];
        }
        $ret['fulldomain'] = $fulldomain['fulldomain'];
        $ret['domain'] = $classroominfo['domain'];
        $ret['status'] = $status;
        return $ret;
    }

    /**
     * 判断域名是否存在
     * @param $fulldomain
     * @return bool
     */
    function domainExists($fulldomain) {
        $sql = 'SELECT `fulldomain` FROM `ebh_domainchecks` WHERE `fulldomain`='.Ebh()->db->escape($fulldomain).' LIMIT 1';
        $ret = $this->db->query($sql)->row_array();
        if (!empty($ret)) {
            return true;
        }
        return false;
    }

    /**
     * 判断网校的独立域名是否生效
     * @param $crid
     * @return bool
     */
    public function hasFullDomain($crid) {
        $sql = 'SELECT `fulldomain` FROM `ebh_classrooms` WHERE `crid`='.intval($crid);
        $roominfo = Ebh()->db->query($sql)->row_array();
        if (!empty($roominfo['fulldomain'])) {
            return true;
        }
        return false;
    }

    /**
     * 申请独立域名
     * @param $crid
     * @param $fulldomain
     * @param $crname
     * @return bool
     */
    public function applyDomain($crid, $fulldomain, $crname){
        /**
         * 保存域名的信息和提交时间
         */
        if (empty($fulldomain)) {
            return false;
        }
        $cridresult = $this->checkCrid($crid);
        if($cridresult) {
            //修改域名信息
            $this->db->begin_trans();
            $setarr['fulldomain'] = $fulldomain;
            $setarr['domain_time'] = SYSTIME;
            $setarr['icp'] = '';
            $this->db->update('ebh_domainchecks', $setarr, '`crid`='.intval($crid));
            if ($this->db->trans_status() === false) {
                $this->db->rollback_trans();
                return false;
            }
            $this->db->update('ebh_billchecks', array(
                'admin_status' => '',
                'teach_status' => '',
                'admin_remark' => '',
                'teach_remark' => ''), '`toid`='.$crid.' AND `type`=13');
            if ($this->db->trans_status() === false) {
                $this->db->rollback_trans();
                return false;
            }
            $this->db->commit_trans();
            return true;
        }
        //增加域名信息
        if (empty($crname)) {
            return false;
        }
        $setarr['fulldomain'] = $fulldomain;
        $setarr['domain_time'] = SYSTIME;
        $setarr['crname'] = $crname;
        $setarr['crid'] = $crid;
        return $this->db->insert('ebh_domainchecks', $setarr);
    }

    /**
     * 检查网校是否存在域名信息
     * @param $crid
     * @return bool
     */
    public function checkCrid($crid){
        $sql='SELECT `crid` FROM `ebh_domainchecks` WHERE `crid`='.intval($crid);
        $ret = $this->db->query($sql)->row_array();
        if (empty($ret)) {
            return false;
        }
        return true;
    }

    /**
     * 解绑独立域名
     * @param $crid
     * @return bool
     */
    function unbindDomain($crid)
    {
        $crid = (int) $crid;
        $this->db->begin_trans();
        $this->db->update('ebh_classrooms', array(
            'fulldomain' => '',
            'icp' => ''
        ), '`crid`='.$crid);
        if ($this->db->trans_status() === false) {
            $this->db->rollback_trans();
            return false;
        }
        $this->db->update('ebh_domainchecks', array('fulldomain' => '', 'icp' => ''), '`crid`='.$crid);
        if ($this->db->trans_status() === false) {
            $this->db->rollback_trans();
            return false;
        }
        $this->db->update('ebh_billchecks', array(
            'admin_status' => '',
            'teach_status' => '',
            'admin_remark' => '',
            'teach_remark' => ''), '`toid`='.$crid.' AND `type`=13');
        if ($this->db->trans_status() === false) {
            $this->db->rollback_trans();
            return false;
        }
        $this->db->commit_trans();
        return true;
    }
}