<?php

/**
 * 模块参数
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/30
 * Time: 0:36
 */
class ComponentItemOptionModel {
    /**
     * 更新二维码图片
     * @param $crid
     * @param $img
     */
    public function qcode($crid, $img) {
        $sql = 'SELECT `oid` FROM `ebh_component_item_options` WHERE `crid`='.intval($crid).' AND `status`=0 AND `mid`=13 ORDER BY `oid` DESC';
        $ret = Ebh()->db->query($sql)->row_array();
        if (empty($ret)) {
            return;
        }
        Ebh()->db->update('ebh_component_item_options', array('image' => $img), '`oid`='.intval($ret['oid']));
    }

    /**
     * 获取模块设置二维码
     * @param $crid
     * @return string
     */
    public function getQcode($crid) {
        $sql = 'SELECT `image` FROM `ebh_component_item_options` WHERE `crid`='.intval($crid).' AND `status`=0 AND `mid`=13 ORDER BY `oid` DESC';
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return $ret['image'];
        }
        return '';
    }
}