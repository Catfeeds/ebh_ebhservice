<?php
/**
 * ebhservice.
 * Author: ycq
 */
class StudentLinkModel{
    /**
     * 提高优先级
     */
    const RAISING_PRIORITY = 0;
    /**
     * 降低优先级
     */
    const DECREASE_PRIORITY = 1;
    /**
     * 链接列表
     * @param int $crid 网校ID
     * @param bool $foradmin 是否管理员端调用
     * @return array
     */
    public function getLinks($crid, $foradmin = false) {
        $wheres = array(
            '`crid`='.$crid,
            '`deldateline`=0'
        );
        if (!$foradmin) {
            $wheres[] = '`enabled`=1';
        }
        $sql = 'SELECT `lid`,`name`,`href`,`target`,`enabled`,`zindex`,`label`,`category` FROM `ebh_studentlinks` WHERE '.implode(' AND ', $wheres).' ORDER BY `zindex` DESC,`lid` ASC';
        $ret = Ebh()->db->query($sql)->list_array();
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }

    /**
     * 添加链接
     * @param array $params 链接属性集
     * @param int $crid 网校ID
     * @return mixed
     */
    public function add($params, $crid) {
        $sets = array('crid' => $crid);
        if (isset($params['name'])) {
            $sets['name'] = trim($params['name']);
        }
        if (isset($params['href'])) {
            $sets['href'] = trim($params['href']);
        }
        if (isset($params['target'])) {
            $sets['target'] = max(0,intval($params['target']));
        }
        if (isset($params['label'])) {
            $sets['label'] = trim($params['label']);
        }
        if (isset($params['enabled'])) {
            $sets['enabled'] = max(0,intval($params['enabled']));
        }
        if (isset($params['category'])) {
            $sets['category'] = min(3, max(0,intval($params['category'])));
        }
        $sets['dateline'] = SYSTIME;
        if (isset($params['zindex'])) {
            $sets['zindex'] = max(0,intval($params['zindex']));
        }
        return Ebh()->db->insert('ebh_studentlinks', $sets);
    }

    /**
     * 修改链接
     * @param int $lid 链接ID
     * @param array $params 链接属性集
     * @param int $crid 网校ID
     * @return mixed
     */
    public function update($lid, $params, $crid) {
        $sets = array();
        if (isset($params['name'])) {
            $sets['name'] = trim($params['name']);
        }
        if (isset($params['href'])) {
            $sets['href'] = trim($params['href']);
        }
        if (isset($params['label'])) {
            $sets['label'] = trim($params['label']);
        }
        if (isset($params['target'])) {
            $sets['target'] = max(0,intval($params['target']));
        }
        if (isset($params['enabled'])) {
            $sets['enabled'] = max(0,intval($params['enabled']));
        }
        if (isset($params['category'])) {
            $sets['category'] = min(3, max(0,intval($params['category'])));
        }
        $sets['updateline'] = SYSTIME;
        if (isset($params['zindex'])) {
            $sets['zindex'] = max(0,intval($params['zindex']));
        }
        return Ebh()->db->update('ebh_studentlinks', $sets, array('lid' => $lid, 'crid' => $crid));
    }

    /**
     * 逻辑删除链接
     * @param int $lid 链接主键
     * @param int $crid 网校ID
     * @return mixed
     */
    public function delete($lid, $crid) {
        return Ebh()->db->update('ebh_studentlinks', array('deldateline' => SYSTIME), array('lid' => $lid, 'crid' => $crid));
    }

    /**
     * 物理删除链接
     * @param int $lid 链接主键
     * @param int $crid 网校ID
     * @return mixed
     */
    public function remove($lid, $crid) {
        return Ebh()->db->delete('ebh_studentlinks', array('lid' => $lid, 'crid' => $crid));
    }

    /**
     * 移动调整链接的优先级
     * @param int $lid 移动链接ID
     * @param int $forword 移动方向
     * @param int $crid 网校ID
     * @return bool
     */
    public function sort($lid, $forword, $crid) {
        $sql = 'SELECT `lid`,`zindex` FROM `ebh_studentlinks` WHERE `crid`='.$crid.' AND `deldateline`=0 ORDER BY `zindex` DESC,`lid` ASC';
        $list = Ebh()->db->query($sql)->list_array();
        if (empty($list) || count($list) == 1) {
            return false;
        }
        $zindexs = array_column($list, 'zindex');
        $zindexs = array_unique($zindexs);
        $count = count($list);
        array_walk($list, function(&$sort) {
           $sort['reindex'] = $sort['zindex'];
        });
        if (count($zindexs) < $count) {
            //存在重复的zindex
            array_walk($list, function(&$sort, $index, $count) {
                $sort['reindex'] = $count - $index;
            }, $count);
        }
        $self = array_filter($list, function($sort) use($lid) {
            return $sort['lid'] == $lid;
        });
        if (empty($self)) {
            return false;
        }
        $selfIndex = key($self);
        if ($forword == self::RAISING_PRIORITY) {
            $changeIndex = $selfIndex - 1;
        } else {
            $changeIndex = $selfIndex + 1;
        }
        if (isset($list[$changeIndex])) {
            $swap = $list[$changeIndex]['reindex'];
            $list[$changeIndex]['reindex'] = $list[$selfIndex]['reindex'];
            $list[$selfIndex]['reindex'] = $swap;
        }
        $list = array_filter($list, function($sort) {
            return $sort['zindex'] != $sort['reindex'];
        });
        if (empty($list)) {
            return false;
        }
        $whenGroup = $whereGroup = array();
        foreach ($list as $sort) {
            $whenGroup[] = ' WHEN '.$sort['lid'].' THEN '.$sort['reindex'];
            $whereGroup[] = $sort['lid'];
        }
        $sql = 'UPDATE `ebh_studentlinks` SET `zindex`=CASE `lid`'.implode(' ', $whenGroup).' END WHERE `lid` IN('.implode(',', $whereGroup).') AND `crid`='.$crid;
        return Ebh()->db->query($sql, false);
    }
}