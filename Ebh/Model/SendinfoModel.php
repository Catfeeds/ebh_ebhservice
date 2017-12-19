<?php

/**
 * 公告
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/21
 * Time: 16:32
 */
class SendinfoModel {
    /**
     * 发布公告
     * @param $crid 网校ID
     * @param $message 公告内容
     * @return mixed
     */
    public function addNotice($crid, $message,$ip) {
        return Ebh()->db->insert('ebh_sendinfo', array(
            'toid' => intval($crid),
            'type' => 'announcement',
            'status' => 0,
            'message' => $message,
            'dateline' => SYSTIME,
            'ip'=>$ip
        ));
    }

    /**
     * 当前的公告
     * @param $crid 网校ID
     * @return string
     */
    public function getSingleModel($crid) {
        $sql = 'SELECT `infoid`,`message` FROM `ebh_sendinfo` WHERE `toid`='.intval($crid).' AND `type`=\'announcement\' ORDER BY `infoid` DESC LIMIT 1';
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return $ret['message'];
        }
        return '';
    }
}