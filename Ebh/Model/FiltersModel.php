<?php

/**
 * 关键词过滤
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/23
 * Time: 13:21
 */
class FiltersModel {
    /**
     * 添加关键词过滤
     * @param $keyword 关键词
     * @param $replace 替换字符串
     * @param $crid 网校ID
     * @param $uid 添加用户ID
     * @return bool|int
     */
    public function add($keyword, $replace, $crid, $uid) {
        $params = array(
            'keyword' => strval($keyword),
            'replace' => strval($replace),
            'crid' => intval($crid),
            'uid' => intval($uid),
            'dateline' => SYSTIME
        );
        return Ebh()->db->insert('ebh_filters', $params);
    }

    /**
     * 关键词过滤列表
     * @param $crid 网校ID
     * @param null $k 搜索关键字
     * @param int $orderType 排序方式，默认0，0-日期降序，1-日期升序
     * @param int $limit 分页参数
     * @return mixed
     */
    public function getList($crid, $k = NULL, $orderType = 0, $limit = 20, $setKey = false) {
        $k = strval($k);
        $whereArr[] = '`crid`='.intval($crid);
        if (!empty($k)) {
            $whereArr[] = '`keyword` LIKE '.Ebh()->db->escape('%'.$k.'%');
        }
        $offset = 0;
        $pagesize = 20;
        if (empty($limit)) {
            $pagesize = 20;
        } else if (is_array($limit)) {
            if (isset($limit['pagesize'])) {
                $pagesize = max(1, intval($limit['pagesize']));
            }
            $page = 1;
            if (isset($limit['page'])) {
                $page = max(1, intval($limit['page']));
            }
            $offset = ($page - 1) * $pagesize;
        } else {
            $pagesize = max(1, intval($limit));
        }
        $orderArr = array(
            0 => '`dateline` DESC',
            1 => '`dateline` ASC'
        );
        $orderStr = isset($orderArr[$orderType]) ? $orderArr[$orderType] : '`dateline` DESC';
        $sql = 'SELECT `fid`,`keyword`,`dateline`,`replace`,`uid` FROM `ebh_filters` WHERE '.
            implode(' AND ', $whereArr).' ORDER BY '.$orderStr.' LIMIT '.$offset.','.$pagesize;
        if ($setKey) {
            return Ebh()->db->query($sql)->list_array('fid');
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 关键词过滤统计
     * @param $crid 网校ID
     * @param null $k 搜索关键字
     * @return int
     */
    public function getCount($crid, $k = NULL) {
        $k = strval($k);
        $whereArr[] = '`crid`='.intval($crid);
        if (!empty($k)) {
            $whereArr[] = '`keyword` LIKE '.Ebh()->db->escape('%'.$k.'%');
        }
        $sql = 'SELECT COUNT(1) AS `c` FROM `ebh_filters` WHERE '.implode(' AND ', $whereArr);
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return $ret['c'];
        }
        return 0;
    }

    /**
     * 删除关键词过滤
     * @param $crid 网校ID
     * @param $fids id参数
     * @return int
     */
    public function remove($crid, $fids) {
        if (empty($fids)) {
            return 0;
        }
        $where = array('`crid`='.intval($crid));
        if (is_array($fids)) {
            $fids = array_map(function($fid) {
                return intval($fid);
            }, $fids);
            $where[] = '`fid` IN('.implode(',',$fids).')';
        } else {
            $where[] = '`fid`='.intval($fids);
        }
        $sql = 'DELETE FROM `ebh_filters` WHERE '.implode(' AND ', $where);
        Ebh()->db->query($sql, FALSE);
        if (Ebh()->db->is_fail() === true) {
            return false;
        }
        return Ebh()->db->affected_rows();
    }
}