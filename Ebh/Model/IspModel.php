<?php
/**
 * IspModel 网络提供商
 * Author: ycq
 */
class IspModel{
    public function __construct() {

    }

    /**
     * 获取网络提供商类型号
     * @param $ip
     * @return int
     */
	public function getIspType($ip) {
        if (is_numeric($ip)) {
            $ip = intval($ip);
        } else if(preg_match('/\d{1,3}(?:\.\d{1,3}){3}/', $ip)) {
            $ip = ip2long($ip);
        } else {
            return 0;
        }
        $sql = 'SELECT `isp` FROM `ebh_isps` WHERE `startiplong`=`masklong` & '.$ip.' LIMIT 1';
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return intval($ret['isp']);
        }
        return 0;
    }
}