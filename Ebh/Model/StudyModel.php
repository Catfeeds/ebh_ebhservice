<?php

/**
 * @describe:学生相关模型
 * @User:tzq
 * @Class StudyModel
 * @CreateTime 2017/11/21
 */
class StudyModel
{
    private $db;

    public function __construct()
    {
        $this->db = Ebh()->db;
    }

    /**
     * @describe:获取统计全网校学生学分和评论
     * @User:tzq
     * @Date:2017/11/21
     * @param int $type 1第一次获取用户信息2获取统计数据
     * @param int $beginTime 查询时间开始时间戳
     * @param int $endTime   查询时间结束时间戳
     * @param int $crid   网校id
     * @param int $curr  当前页
     * @param int $listRows 每页显示条数
     * @param int $newlecture 讲座课程id
     * @param int $newtransaction 业务纵览课程id
     * @param int $newregulations  政治法规课程id
     * @return array 返回结果数组/错误返回null
     */
    public function getCreditCount($params)
    {

        if ($params['type'] == 1) {
            //第一次获取用户信息
            //要获取的字段
            $field    = array(
            '`u`.`username`'
            , '`u`.`realname`'
            , '`u`.`sex`'
            , '`u`.`face`'
            , '`u`.`uid`'
            , '`ca`.`classname`'
            , '`ca`.`path`'
            );
            //获取符合条件的用户
            $count    = ' COUNT(*) ebh_count ';
            $sql      = 'SELECT ' . $count. ' FROM     `ebh_users` `u`
                LEFT JOIN `ebh_classstudents` `cl` ON `cl`.`uid`=`u`.`uid`  
                LEFT JOIN `ebh_classes` `ca`  ON `cl`.`classid`=`ca`.`classid`  WHERE `ca`.`crid`='.$params['crid'] ;
            //统计记录总数
            $row      = $this->db->query($sql)->row_array();
            $total    = isset($row['ebh_count'])?$row['ebh_count']:0;
            $page     = getPage($total, $params['listRows'], $params['curr']);
            $startNum = ($page['curr'] - 1) * $page['listRows'];
            $listRows = $page['listRows'];
            //查询sql
            $sql      = str_replace($count,implode(',',$field),$sql);
            $sql      .= ' LIMIT ' . $startNum . ', ' . $listRows;

           // log_message($sql);
            $list = $this->db->query($sql)->list_array();
            //处理多级班级
            array_walk($list, function (&$item) {
                if (!empty($item['path'])) {
                    $temp = trim($item['path']);
                    $temp = preg_replace('/.+\//U', '', $temp, 1);
                    if (!empty($temp)) {
                        $item['classname'] = $temp;
                    }
                }
            });


            return array('list' => $list, 'page' => $page,'attach'=>implode(',',array_column($list,'uid')));

        } elseif ($params['type'] == 2) {

            if(empty($params['attach'])){
                return ;
            }
            //网校id
            $folderid = $params['newlecture'] . ',' . $params['newtransaction'] . ',' . $params['newregulations'];
            //获取要查询的用户
            $uidArr = $params['attach'];
            $where   = '  `u`.`uid` IN (' .  $uidArr . ')';
            //要查询的字段信息（
            // newlecture_comment 讲座学分统计
            // newlecture_credit  讲座评论
            // newtransaction_comment 业务纵览学分统计
            // newtransaction_credit  业务纵览评论统计
            // newregulations_comment 政治法规学分统计
            // newregulations_credit  政治法规评分统计
            // communication_comment  文章发布统计
            // communication_credit   文章阅读统计
            // commentScore           得分评论数
            // cx                     学分总数
            $field  = array(
                '`u`.`uid`',
                "SUM(if(`scl`.`folderid`={$params['newlecture']},if(`scl`.`type`=3,`scl`.`score`,0),0)) as `newlecture_comment`",
                "SUM(if(`scl`.`folderid`={$params['newlecture']},if(`scl`.`type` in(0,1,4),`scl`.`score`,0),0)) as `newlecture_credit`",
                "SUM(if(`scl`.`folderid`={$params['newtransaction']},if(`scl`.`type`=3,`scl`.`score`,0),0)) as `newtransaction_comment`",
                "SUM(if(`scl`.`folderid`={$params['newtransaction']},if(`scl`.`type` in(0,1,4),`scl`.`score`,0),0)) as `newtransaction_credit`",
                "SUM(if(`scl`.`folderid`={$params['newregulations']},if(`scl`.`type`=3,`scl`.`score`,0),0)) as `newregulations_comment`",
                "SUM(if(`scl`.`folderid`={$params['newregulations']},if(`scl`.`type` in(0,1,4),`scl`.`score`,0),0)) as `newregulations_credit`",
                "SUM(if(`scl`.`folderid`=0,if(`scl`.`type`=5,`scl`.`score`,0),0)) as `communication_comment`",
                "SUM(if(`scl`.`folderid`=0,if(`scl`.`type`=2,`scl`.`score`,0),0)) as `communication_credit`",
//                "SUM(if(`scl`.`type`=3,1,0)) as cp",
                "SUM(if(`scl`.`type`=3,1,0)) as `commentScore`",
                "SUM(if(`scl`.`score`>0,`scl`.`score`,0)) as `cx`"
            );
            //拼装sql语句
            $sql
                    = 'SELECT ' . implode(',', $field) .

                ' FROM
                    `ebh_users` `u`
                LEFT JOIN `ebh_studycreditlogs` `scl` ON `scl`.`uid`=`u`.`uid` AND   (`scl`.`dateline` >=' . $params['beginTime'] . ' AND ' . '`scl`.`dateline` <=' . $params['endTime'] . ") AND  `scl`.`del`=0 AND `scl`.`crid`=" . $params['crid'] .' 
         
  
                LEFT JOIN `ebh_roomcourses` `ro` ON `ro`.`cwid`=`scl`.`cwid` '." AND (`ro`.`folderid` in({$folderid}) OR `scl`.`type` in(2,5)) AND `ro`.`crid`={$params['crid']}".'
             
                WHERE ' . $where . ' GROUP BY `uid` ORDER BY NULL';


            //log_message('查询数据:' . $sql);


            $list = $this->db->query($sql)->list_array();


            //评论统计
            $field = array(
                '`r`.`uid`',
                'sum(1) as `cp`',

            );
            $where = ' (`r`.`dateline` >=' . $params['beginTime'] . ' AND `r`.`dateline` <=' . $params['endTime'] . ') AND `r`.`crid`=' . $params['crid'];

            $where .= ' AND `r`.`uid` IN (' . $uidArr . ')';
            $sql = 'SELECT ' . implode(',', $field) .
                ' FROM `ebh_reviews` `r`  WHERE ' . $where . '  GROUP BY `uid` ORDER BY NULL';
            //log_message($sql);
            $res = $this->db->query($sql)->list_array();
            //资讯统计
            $sql = 'SELECT ' . implode(',', $field) .
                ' FROM `ebh_news_reviews` `r`  WHERE ' . $where . ' AND `r`.`del`=0 GROUP BY `uid` ORDER BY NULL';
            //log_message($sql);
            $new = $this->db->query($sql)->list_array();


            //将数据库返回的数据组装成以uid为key的数组
            $data = array();
            foreach ($list as $v) {
                $data[$v['uid']]       = $v;
                $data[$v['uid']]['cp'] = 0;//总评论数据赋初始值
            }
            foreach ($res as $val) {
                $data[$val['uid']]['cp'] += $val['cp'];//总评论数加上评论数
            }
            foreach ($new as $vv) {
                $data[$vv['uid']]['cp'] += $vv['cp'];//总评论数据加上资讯评论数
            }

            return array('list' => $data);
        }
    }

    /**
     * @describe:学生导出统计
     * @User:tzq
     * @Date:2017/11/18
     * @param $params
     * @param int $newlecture 讲座课程id
     * @param int $newtransaction 业务纵览课程id
     * @param int $newregulations  政治法规课程id
     * @param int $beginTime 查询开始时间戳
     * @param int $endTime    查询时间结束时间戳
     * @param int $crid       当前网校id
     * @return array/null;
     */
    public function  outExclgetCreditCount($params){
       //查询sql
        $field = array(
            '`u`.`realname`',
            '`u`.`username`',
            '`ca`.`classname`',
            '`ca`.`path`',
            '`u`.`uid`'
        );

        $sql
            = 'SELECT '.implode(',',$field)." 
                  
                 FROM
                    `ebh_users` `u`
                LEFT JOIN `ebh_classstudents` `cl` ON `cl`.`uid`=`u`.`uid` 
                LEFT JOIN `ebh_classes` `ca`  ON `cl`.`classid`=`ca`.`classid`  

            
                WHERE  `ca`.`crid` = {$params['crid']} 
              
                  
                GROUP BY `uid` 
                ORDER BY 
                    NULL ";
        $list  = $this->db->query($sql)->list_array();
        //获取课程对应的课件id
        $folderids = $params['newlecture'] . ',' . $params['newtransaction'] . ',' . $params['newregulations'];

       // $s1    = microtime(true);
        //log_message('查询主数据耗时：'.($s1-$start));
        //将数据库返回的数据组装成以uid为key的数组
        $data   = array();
        $uidStr = '';
        if ($list) {
            foreach ($list as $v) {
                $data[$v['uid']] = $v;
                $uidStr          .= $v['uid'] . ',';//拼接in 条件字符
            }
            $uidStr = rtrim($uidStr,',');
        } else {
            return;
        }
        //统计学分
        $where   = [];
        $where[] = '`crid`=' . $params['crid'];
        $where[] = '`uid` IN (' . $uidStr . ')';
        $where[] = '(`dateline` >=' . $params['beginTime'] . ' AND `dateline`<=' . $params['endTime'] . ')';
        $where[] = '`del`=0';
        //要查询的字段信息（
        // newlecture_comment 讲座学分统计
        // newlecture_credit  讲座评论
        // newtransaction_comment 业务纵览学分统计
        // newtransaction_credit  业务纵览评论统计
        // newregulations_comment 政治法规学分统计
        // newregulations_credit  政治法规评分统计
        // communication_comment  文章发布统计
        // communication_credit   文章阅读统计
        // commentScore           得分评论数
        // cx                     学分总数
        // cp                     评论数（占位字段/导出时不用再考虑排序问题）
        $creditField = [
            'SUM(if(`score`>0,`score`,0)) as cx',
            "SUM(if(`folderid`={$params['newlecture']},if(`type` in(0,1,4),`score`,0),0)) as newlecture_comment",
            "SUM(if(`folderid`={$params['newlecture']},if(`type` =3,`score`,0),0)) as newlecture_credit",
            "SUM(if(`folderid`={$params['newtransaction']},if(`type` in(0,1,4),`score`,0),0)) as newtransaction_comment",
            "SUM(if(`folderid`={$params['newtransaction']},if(`type` =3,`score`,0),0)) as newtransaction_credit",
            "SUM(if(`folderid`={$params['newregulations']},if(`type` in(0,1,4),`score`,0),0)) as newregulations_comment",
            "SUM(if(`folderid`={$params['newregulations']},if(`type` = 3,`score`,0),0)) as newregulations_credit",
            "SUM(if(`folderid`=0,if(`type`=2,`score`,0),0)) as communication_credit",
            "SUM(if(`folderid`=0,if(`type`=5,`score`,0),0)) as communication_comment",
            '0 as `cp`',
            "SUM(if(`type`=3,if(`score`>0,1,0),0)) as commentScore",
            'uid'
        ];
        $creditSql   = 'SELECT ' . implode(',', $creditField) . ' FROM ebh_studycreditlogs ';
        $creditSql   .= ' WHERE ' . implode(' AND ', $where);
        $creditSql   .= ' GROUP BY uid ORDER BY NULL';
        //学分获取语句
        //log_message('学分获取语句：'.$creditSql);
        $creditList  = $this->db->query($creditSql)->list_array('uid');

        foreach ($data as $uid => $item) {
            $data[$uid]['cx']                     = isset($creditList[$uid]['cx']) && $creditList[$uid]['cx'] > 0 ? $creditList[$uid]['cx'] : 0;
            $data[$uid]['newlecture_comment']     = isset($creditList[$uid]['newlecture_comment']) && $creditList[$uid]['newlecture_comment'] > 0 ? $creditList[$uid]['newlecture_comment'] : 0;
            $data[$uid]['newlecture_credit']      = isset($creditList[$uid]['newlecture_credit']) && $creditList[$uid]['newlecture_credit'] > 0 ? $creditList[$uid]['newlecture_credit'] : 0;
            $data[$uid]['newtransaction_comment'] = isset($creditList[$uid]['newtransaction_comment']) && $creditList[$uid]['newtransaction_comment'] > 0 ? $creditList[$uid]['newtransaction_comment'] : 0;
            $data[$uid]['newtransaction_credit']  = isset($creditList[$uid]['newtransaction_credit']) && $creditList[$uid]['newtransaction_credit'] > 0 ? $creditList[$uid]['newtransaction_credit'] : 0;
            $data[$uid]['newregulations_comment'] = isset($creditList[$uid]['newregulations_comment']) && $creditList[$uid]['newregulations_comment'] > 0 ? $creditList[$uid]['newregulations_comment'] : 0;
            $data[$uid]['newregulations_credit']  = isset($creditList[$uid]['newregulations_credit']) && $creditList[$uid]['newregulations_credit'] > 0 ? $creditList[$uid]['newregulations_credit'] : 0;
            $data[$uid]['communication_credit']   = isset($creditList[$uid]['communication_credit']) && $creditList[$uid]['communication_credit'] > 0 ? $creditList[$uid]['communication_credit'] : 0;
            $data[$uid]['communication_comment']  = isset($creditList[$uid]['communication_comment']) && $creditList[$uid]['communication_comment'] > 0 ? $creditList[$uid]['communication_comment'] : 0;
            $data[$uid]['cp']                     = 0;
            $data[$uid]['commentScore']           = isset($creditList[$uid]['commentScore']) && $creditList[$uid]['commentScore'] > 0 ? $creditList[$uid]['commentScore'] : 0;
        }

        //统计评论
        //log_message(json_encode($data, JSON_UNESCAPED_UNICODE));
        $field = array(
            '`r`.`uid`',
            'COUNT(*) as `cp`',

        );

        $where = ' (`r`.`dateline` >=' . $params['beginTime'] . ' AND `r`.`dateline` <=' . $params['endTime'] . ') AND `r`.`crid`=' . $params['crid'];

        $where .= ' AND `r`.`uid` IN (' . $uidStr . ')';
        //评论统计
        $sql = 'SELECT ' . implode(',', $field) .
            ' FROM `ebh_reviews` `r`  WHERE ' . $where . '  GROUP BY `uid` ORDER BY NULL';
        //log_message($sql);
        //$s1  = microtime(true);
        $res = $this->db->query($sql)->list_array();
       // $s2  = microtime(true);
        //log_message('查询评论耗时：'.($s2-$s1));
        //资讯统计
        $sql = 'SELECT ' . implode(',', $field) .
            ' FROM `ebh_news_reviews` `r`  WHERE ' . $where . ' AND `r`.`del`=0 GROUP BY `uid` ORDER BY NULL';
        //log_message($sql);
        $new = $this->db->query($sql)->list_array();
        //$s3  = microtime(true);
        // log_message('查询资讯耗时：'.($s3-$s2));
        //将评论组装到数组
        foreach ($res as $v) {

            $data[$v['uid']]['cp'] += $v['cp'];//将评论数加到总评论

        }
        foreach ($new as $vv) {


            $data[$vv['uid']]['cp'] += $vv['cp'];//将资讯评论加到总评论

        }
        return $data;
    }

}