<?php
/**
 * ebhservice.
 * Author: ycq
 */
class IntroModel{
    /**
     * 添加开场内容
     * @param $folderid 课程ID
     * @param $params 参数
     * @return type
     */
    public function add($folderid, $params) {
        $introtype = isset($params['introtype']) ? 0 : intval($params['introtype']);
        if ($introtype < 1) {
            return false;
        }
        $sets = array(
            'folderid' => intval($folderid),
            'introtype' => $introtype
        );
        if (isset($params['attid'])) {
            $sets['attid'] = intval($params['attid']);
        }
        if (isset($params['slides'])) {
            $sets['slides'] = $params['slides'];
        }
        return Ebh()->db->insert('ebh_folder_intros', $sets);
    }

    /**
     * 设置课程下课件开场内容
     * @param $folderid
     * @param $params
     * @return bool
     */
    public function set($folderid, $params) {
        $folderid = intval($folderid);
        $introtype = isset($params['introtype']) ? intval($params['introtype']) : 0;
        if ($introtype < 0) {
            return false;
        }
        $fields = array('`folderid`', '`introtype`');
        $values = array($folderid, $introtype);
        $updates = array(
            '`introtype`='.$introtype
        );
        if (!empty($params['attid'])) {
            $fields[] = '`attid`';
            $values[] = intval($params['attid']);
            $updates[] = '`attid`='.intval($params['attid']);
        } else {
            $fields[] = '`attid`';
            $values[] = 0;
            $updates[] = '`attid`=0';
        }

        if (!empty($params['slides'])) {
            $fields[] = '`slides`';
            $values[] = Ebh()->db->escape($params['slides']);
            $updates[] = '`slides`='.Ebh()->db->escape($params['slides']);
        } else {
            $updates[] = '`slides`=\'\'';
        }

        $sql = 'INSERT INTO `ebh_folder_intros`('.implode(',', $fields).') VALUES('.implode(',', $values).') ON DUPLICATE KEY UPDATE '.implode(',', $updates);
        return Ebh()->db->query($sql, false);
    }
}