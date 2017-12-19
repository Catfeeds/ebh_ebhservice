<?php

/**
 * 积分日志
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/4/25
 * Time: 13:35
 */
class CreditlogModel
{
    private $db;
    function __construct()
    {
        $this->db = Ebh()->db;
    }

    /**
     * 签到分析
     * @param $crid 网校ID
     * @param $params 过滤条件：开始时间、结束时间
     * @param bool $setKey 是否以日期为键
     * @return mixed
     */
    public function getSignLogs($crid, $params, $setKey = false, $baseTime = 0) {
        $today = !empty($baseTime) ? $baseTime : strtotime(date('Y-m-d'));
        $starttime = isset($params['starttime']) ? intval($params['starttime']) : $today;
        $whereArr = array(
            'IFNULL(`b`.`uid`,0)>0',
            '`a`.`crid`='.intval($crid),
            'IFNULL(`c`.`credit`,0)>0',
            '`c`.`ruleid`=22',
            '`c`.`dateline`>='.$starttime
        );
        if (isset($params['endtime'])) {
            $whereArr[] = '`c`.`dateline`<='.intval($params['endtime']);
        }
        if ($starttime >= $today || isset($params['endtime']) && $params['endtime'] < $starttime + 86400) {
            $format = '\'%Y-%m-%d %H:00:00\'';
        } else {
            $format = '\'%Y-%m-%d\'';
        }
        $sql = 'SELECT FROM_UNIXTIME(`c`.`dateline`,'.$format.') AS `date`,COUNT(1) AS `signs`
             FROM `ebh_roomusers` `a` LEFT JOIN `ebh_users` `b` ON `a`.`uid`=`b`.`uid`
             LEFT JOIN `ebh_creditlogs` `c` ON `a`.`uid`=`c`.`toid`
             WHERE '.implode(' AND ', $whereArr).' GROUP BY `date`';
        return $this->db->query($sql)->list_array($setKey ? 'date' : '');
    }

    /**
     * 按时间降序取网校积分日志
     * @param int $crid 网校ID
     * @param int $num 获取数量
     * @return mixed
     */
    public function getLogs($crid, $num = 5) {
        $sql = 'SELECT `c`.`face`,`c`.`sex`,`c`.`username`,`c`.`realname`,`b`.`action`,`b`.`description`,`a`.`dateline`,`a`.`credit`,`a`.`detail` FROM `ebh_creditlogs` `a` 
                JOIN `ebh_creditrules` `b` ON `b`.`ruleid`=`a`.`ruleid` 
                JOIN `ebh_users` `c` ON `c`.`uid`=`a`.`toid` 
                JOIN `ebh_roomusers` `d` ON `d`.`uid`=`c`.`uid` 
                WHERE `d`.`crid`='.$crid.' AND `c`.`groupid`=6 ORDER BY `a`.`logid` DESC LIMIT '.$num;
        return Ebh()->db->query($sql)->list_array();
    }
}