<?php

/**
 * 系统设置
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/16
 * Time: 16:53
 */
class SystemSettingModel
{
    /////增加一个副标题字段subtitle
    private $db;
    public function __construct()
    {
        $this->db = Ebh()->db;
    }

    /*
     * 获取设置
     */
    public function getModel($crid)
    {
        $crid = (int) $crid;
        $sql = "SELECT `creditrule`,`iscollect`,`discounts`,`favicon`,`faviconimg`,`metakeywords`,`metadescription`,`analytics`,`subtitle` 
                FROM `ebh_systemsettings` 
                WHERE `crid`=$crid LIMIT 1";
        return $this->db->query($sql)->row_array();
    }

    /**
     * 更新seo
     * @param $crid
     * @param $update_params
     * @return mixed
     */
    public function update($crid, $update_params)
    {
        $params = array();
        if (isset($update_params['favicon'])) {
            $params['favicon'] = $update_params['favicon'];
        }
        if (isset($update_params['faviconimg'])) {
            $params['faviconimg'] = $update_params['faviconimg'];
        }
        if (isset($update_params['metakeywords'])) {
            $params['metakeywords'] = $update_params['metakeywords'];
        }
        if (isset($update_params['metadescription'])) {
            $params['metadescription'] = $update_params['metadescription'];
        }
        if (isset($update_params['ipbanlist'])) {
            $params['ipbanlist'] = $update_params['ipbanlist'];
        }
        if (isset($update_params['analytics'])) {
            $params['analytics'] = $update_params['analytics'];
        }
        if (isset($update_params['limitnum'])) {
            $params['limitnum'] = $update_params['limitnum'];
        }
        if (isset($update_params['service'])) {
            $params['service'] = $update_params['service'];
        }
        if (isset($update_params['opservicetime'])) {
            $params['opservicetime'] = $update_params['opservicetime'];
        }
        if (isset($update_params['opserviceuid'])) {
            $params['opserviceuid'] = $update_params['opserviceuid'];
        }
        if (isset($update_params['subtitle'])) {
            $params['subtitle'] = $update_params['subtitle'];
        }
        if (isset($update_params['iscollect'])) {
            $params['iscollect'] = intval($update_params['iscollect']);
        }
        if (!empty($update_params['discounts'])) {
            $params['discounts'] = $update_params['discounts'];
        }
        if (isset($update_params['refuse_stranger'])) {
            $params['refuse_stranger'] = !empty($update_params['refuse_stranger']) ? 1 : 0;
        }
        if (isset($update_params['mobile_register'])) {
            $params['mobile_register'] = !empty($update_params['mobile_register']) ? 1 : 0;
        }
        if (isset($update_params['review_interval'])) {
            $params['review_interval'] = intval($update_params['review_interval']);
        }
        if (isset($update_params['post_interval'])) {
            $params['post_interval'] = intval($update_params['post_interval']);
        }
        if (isset($update_params['creditrule'])) {
            $params['creditrule'] = $update_params['creditrule'];
        }
        if (isset($update_params['showlink'])) {
            $params['showlink'] = intval($update_params['showlink']);
        }
        if (isset($update_params['showmodule'])) {
            $params['showmodule'] = intval($update_params['showmodule']);
        }
        if (isset($update_params['ebhbrowser'])) {
            $params['ebhbrowser'] = intval($update_params['ebhbrowser']);
        }
        if (empty($update_params)) {
            return 0;
        }
        return $this->db->update('ebh_systemsettings', $params, "`crid`=$crid");
    }

    public function add($crid, $update_params) {
        $params = array();
        if (!empty($update_params['favicon'])) {
            $params['favicon'] = $update_params['favicon'];
        }
        if (!empty($update_params['faviconimg'])) {
            $params['faviconimg'] = $update_params['faviconimg'];
        }
        if (!empty($update_params['metakeywords'])) {
            $params['metakeywords'] = $update_params['metakeywords'];
        }
        if (!empty($update_params['metadescription'])) {
            $params['metadescription'] = $update_params['metadescription'];
        }
        if (!empty($update_params['ipbanlist'])) {
            $params['ipbanlist'] = $update_params['ipbanlist'];
        }
        if (!empty($update_params['analytics'])) {
            $params['analytics'] = $update_params['analytics'];
        }
        if (!empty($update_params['limitnum'])) {
            $params['limitnum'] = $update_params['limitnum'];
        }
        if (!empty($update_params['service'])) {
            $params['service'] = $update_params['service'];
        }
        if (!empty($update_params['opservicetime'])) {
            $params['opservicetime'] = $update_params['opservicetime'];
        }
        if (!empty($update_params['opserviceuid'])) {
            $params['opserviceuid'] = $update_params['opserviceuid'];
        }
        if (!empty($update_params['subtitle'])) {
            $params['subtitle'] = $update_params['subtitle'];
        }
        if (isset($update_params['iscollect'])) {
            $params['iscollect'] = intval($update_params['iscollect']);
        }
        if (!empty($update_params['discounts'])) {
            $params['discounts'] = $update_params['discounts'];
        }
        if (!empty($update_params['refuses_tranger'])) {
            $params['refuses_tranger'] = !empty($update_params['refuses_tranger']) ? 1 : 0;
        }
        if (!empty($update_params['mobile_register'])) {
            $params['mobile_register'] = $update_params['mobile_register'];
        }
        if (!empty($update_params['interval'])) {
            $params['interval'] = intval($update_params['interval']);
        }
        if (isset($update_params['showlink'])) {
            $params['showlink'] = intval($update_params['showlink']);
        }
        if (isset($update_params['showmodule'])) {
            $params['showmodule'] = intval($update_params['showmodule']);
        }
        if (isset($update_params['creditrule'])) {
            $params['creditrule'] = $update_params['creditrule'];
        }
        if (isset($update_params['ebhbrowser'])) {
            $params['ebhbrowser'] = intval($update_params['ebhbrowser']);
        }
        $params['crid'] = intval($crid);
        return $this->db->insert('ebh_systemsettings', $params);
    }

    /**
     * 系统其它设置
     * @param $crid
     * @return mixed
     */
    public function getOtherSetting($crid) {
        $crid = (int) $crid;
        $sql = "SELECT `refuse_stranger`,`mobile_register`,`review_interval`,`post_interval`,`limitnum`,`creditrule`,`showlink`,`showmodule`,`ebhbrowser` 
                FROM `ebh_systemsettings` 
                WHERE `crid`=$crid LIMIT 1";
        return $this->db->query($sql)->row_array();
    }

    public function exists($crid) {
        $sql = 'SELECT `crid` FROM `ebh_systemsettings` WHERE `crid`='.intval($crid);
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return true;
        }
        return false;
    }


    /**
     * 获取系统设置信息
     */
    public function getSetting($crid) {
        $sql = 'SELECT * FROM ebh_systemsettings WHERE crid=' . intval($crid);
        $row = $this->db->query($sql)->row_array();
        if (empty($row)) {
            $row = array(
                'crid' => $crid,
                'metakeywords' => '',
                'metadescription' => '',
                'favicon' => '',
                'faviconimg' => '',
                'ipbanlist' => '',
                'analytics' => '',
                'limitnum' => 0,
                'creditrule' => '',
                'service' => 0,
                'opservicetime' => 0,
                'opserviceuid' => 0,
                'iscollect' => 0,
                'discounts' => '',
                'showlink' => 0,
                'showmodule' => 1
            );
        }
        return $row;
    }
}