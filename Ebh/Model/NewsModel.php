<?php

/**
 * 新闻资讯类
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/20
 * Time: 13:45
 */
class NewsModel {
    /**
     * 发布新闻
     * @param $crid 网校ID
     * @param $uid 新闻发布者ID
     * @param $params 新闻参数
     * @return bool
     */
    public function add($crid, $uid, $params) {
        $crid = (int) $crid;
        $uid = (int) $uid;
        if ($crid < 1 || $uid < 1 || empty($params['subject']) || empty($params['note']) || empty($params['message'])) {
            return false;
        }
        $formatParams = array();
        $formatParams['crid'] = $crid;
        $formatParams['uid'] = $uid;
        $formatParams['subject'] = $params['subject'];
        $formatParams['note'] = $params['note'];
        $formatParams['message'] = $params['message'];
        $formatParams['navcode'] = !empty($params['navcode']) ? $params['navcode'] : 'news';
        if (isset($params['thumb'])) {
            $formatParams['thumb'] = $params['thumb'];
        }
        if (isset($params['viewnum'])) {
            $formatParams['viewnum'] = intval($params['viewnum']);
        }
        if (isset($params['type'])) {
            $formatParams['type'] = intval($params['type']);
        }
        if (isset($params['ip'])) {
            $formatParams['ip'] = $params['ip'];
        }
		if (isset($params['attid'])) {
            $formatParams['attid'] = $params['attid'];
        }
        if (isset($params['status'])) {
            $status = intval($params['status']);
            $formatParams['status'] = min(1, max(0, $status));
        } else {
            $formatParams['status'] = 0;
        }
        $formatParams['dateline'] = SYSTIME;
        if (isset($params['displayorder'])) {
            $formatParams['displayorder'] = intval($params['displayorder']);
        }
        $res = Ebh()->db->insert('ebh_news', $formatParams);
        //发布资讯成功,更新其分类的排序号(prank,rank)
        if(!empty($res) && is_numeric($res) && empty($params['type'])){
            $data = array();
            $data['rank'] = $res;   //新发布的资讯排序号等于其itemid
            $data['prank'] = $res;
            $whereStr = '`itemid`='.$res.' AND `crid`='.$crid;
            Ebh()->db->update('ebh_news', $data, $whereStr);
        }
        return $res;
    }

    /**
     * 更新新闻
     * @param $itemid 新闻ID
     * @param $crid 网校ID
     * @param $params 新闻参数
     * @return int
     */
    public function update($itemid, $crid, $params) {
       $itemid = (int) $itemid;
       $crid= (int) $crid;
       $formatParams = array();
        if (isset($params['navcode'])) {
            $formatParams['navcode'] = $params['navcode'];
        }
        if (isset($params['subject'])) {
            $formatParams['subject'] = $params['subject'];
        }
        if (isset($params['thumb'])) {
            $formatParams['thumb'] = $params['thumb'];
        }
        if (isset($params['message'])) {
            $formatParams['message'] = $params['message'];
        }
        if (isset($params['note'])) {
            $formatParams['note'] = $params['note'];
        }
        if (isset($params['viewnum'])) {
            $formatParams['viewnum'] = intval($params['viewnum']);
        }
        if (isset($params['displayorder'])) {
            $formatParams['displayorder'] = intval($params['displayorder']);
        }
        if (isset($params['ip'])) {
            $formatParams['ip'] = $params['ip'];
        }
        if (isset($params['status'])) {
            $status = intval($params['status']);
            $formatParams['status'] = min(2, max(-1, $status));
        }
		if (isset($params['attid'])) {
            $formatParams['attid'] = $params['attid'];
        }
        if (empty($formatParams)) {
            return 0;
        }
        $whereStr = '`itemid`='.$itemid.' AND `crid`='.$crid;
        return Ebh()->db->update('ebh_news', $formatParams, $whereStr);
    }

    /**
     * 删除新闻
     * @param $itemid 新闻ID
     * @param $crid 网校ID
     * @return mixed
     */
    public function remove($itemid, $crid) {
        $itemid = (int) $itemid;
        $crid = (int) $crid;
        $whereStr = '`itemid`='.$itemid.' AND `crid`='.$crid;
        return Ebh()->db->delete('ebh_news', $whereStr);
    }

    /**
     * 新闻详情
     * @param $itemid 新闻ID
     * @return mixed
     */
    public function getModel($itemid) {
        $itemid = (int) $itemid;
        $sql = 'SELECT `itemid`,`navcode`,`subject`,`message`,`note`,`thumb`,`crid`,`uid`,`viewnum`,`dateline`,`displayorder`,`status`,`attid` FROM `ebh_news` WHERE `itemid`='.$itemid;
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 新闻列表
     * @param $filterParams 过滤条件
     * @param int $limit 限制条件
     * @param bool $setKey 是否以itemid作为键值
     * @return mixed
     */
    public function getList($filterParams, $limit = 20, $setKey = true) {
        $params = array();
        //获取资讯分类navcode的集合
        if (isset($filterParams['navcode']) && is_string($filterParams['navcode'])) {
            $navcode = Ebh()->db->escape($filterParams['navcode']);
            $params[] = '`navcode` in ('.implode('\',\'',explode(',',$navcode)).')';
        }
        if (isset($filterParams['crid'])) {
            $params[] = '`crid`='.intval($filterParams['crid']);
        }
        if (isset($filterParams['uid'])) {
            $params[] = '`uid`='.intval($filterParams['uid']);
        }
        if (isset($filterParams['status'])) {
            $params[] = '`status`='.intval($filterParams['status']);
        }
        if (isset($filterParams['early'])) {
            $params[] = '`dateline`>='.intval($filterParams['early']);
        }
        if (isset($filterParams['latest'])) {
            $params[] = '`dateline`<='.intval($filterParams['latest']);
        }
       if (isset($filterParams['type'])) {
            $params[] = '`type`='.intval($filterParams['type']);
        } else {
            $params[] = '`type`=0 ';
        }
        if (isset($filterParams['q'])) {
            $params[] = '`subject` LIKE '.Ebh()->db->escape('%'.$filterParams['q'].'%');
        }
        $sql = 'SELECT `itemid`,`navcode`,`subject`,`note`,`thumb`,`viewnum`,`displayorder`,`dateline`,`prank`,`rank` FROM `ebh_news`';
        if (!empty($params)) {
            $sql .= ' WHERE '.implode(' AND ', $params);
        }
        $offset = 0;
        $pagesize = 20;
        if (is_array($limit)) {
            if (isset($limit['pagesize'])) {
                $pagesize = max(1, intval($limit['pagesize']));
            }
            if (isset($limit['page'])) {
                $page = max(1, intval($limit['page']));
                $offset = ($page - 1) * $pagesize;
            }
        } else {
            $pagesize = max(1, intval($limit));
        }
        //在主类或子类下,按照资讯分类排序号排序
        if(!empty($filterParams['ranktype'])){
            $ranktype = trim(Ebh()->db->escape($filterParams['ranktype']),'\'');
            $sql .= ' ORDER BY `'.$ranktype.'` DESC, `itemid` DESC LIMIT '.$offset.','.$pagesize;
        }else{
            $sql .= ' ORDER BY `itemid` DESC LIMIT '.$offset.','.$pagesize;
        }
        if ($setKey) {
            return Ebh()->db->query($sql)->list_array('itemid');
        }
        return Ebh()->db->query($sql)->list_array();
    }

     /**
     * 新闻列表
     * @param $filterParams 过滤条件
     * @param int $limit 限制条件
     * @param bool $setKey 是否以itemid作为键值
     * @return mixed
     */
    public function getListAndTotalPage($filterParams, $limit = 20, $setKey = true) {
        $params = array();
        if (isset($filterParams['navcode'])) {
            $params[] = '`navcode`='.Ebh()->db->escape($filterParams['navcode']);
        }
        if (isset($filterParams['crid'])) {
            $params[] = '`crid`='.intval($filterParams['crid']);
        }
        if (isset($filterParams['uid'])) {
            $params[] = '`uid`='.intval($filterParams['uid']);
        }
        if (isset($filterParams['status'])) {
            $params[] = '`status`='.intval($filterParams['status']);
        }
        if (isset($filterParams['notdel'])) {
            $params[] = '`status`> -1 ';
        }
        if (isset($filterParams['early'])) {
            $params[] = '`dateline`>='.intval($filterParams['early']);
        }
        if (isset($filterParams['latest'])) {
            $params[] = '`dateline`<='.intval($filterParams['latest']);
        }
        if (isset($filterParams['type'])) {
            $params[] = '`type`='.intval($filterParams['type']);
        } else {
            $params[] = '`type`=0 ';
        }
        if (isset($filterParams['q'])) {
            $params[] = '`subject` LIKE '.Ebh()->db->escape('%'.$filterParams['q'].'%');
        }
        $sql = 'SELECT `itemid`,`status`,`message`,`uid`,`crid`,`navcode`,`subject`,`note`,`thumb`,`viewnum`,`displayorder`,`dateline` FROM `ebh_news`';
        $countsql = 'SELECT count(1) as c FROM `ebh_news`';
        if (!empty($params)) {
            $sql .= ' WHERE '.implode(' AND ', $params);
            $countsql .= ' WHERE '.implode(' AND ', $params);
        }
        //算出总的数量
        $count = Ebh()->db->query($countsql)->row_array();

        $offset = 0;
        $pagesize = 20;
        if (is_array($limit)) {
            if (isset($limit['pagesize'])) {
                $pagesize = max(1, intval($limit['pagesize']));
            }
            if (isset($limit['page'])) {
                $page = max(1, intval($limit['page']));
                $offset = ($page - 1) * $pagesize;
            }
        } else {
            $pagesize = max(1, intval($limit));
        }
        $sql .= ' ORDER BY `itemid` DESC LIMIT '.$offset.','.$pagesize;
        if ($setKey) {
            return Ebh()->db->query($sql)->list_array('itemid');
        }
        $res['list'] = Ebh()->db->query($sql)->list_array();
        $res['totalpage'] = $count['c'];
        return $res;
    }

    /**
     * 新闻统计
     * @param $filterParams 筛选条件
     * @return bool
     */
    public function getCount($filterParams) {
        $params = array();
        //获取资讯分类navcode的集合
        if (isset($filterParams['navcode']) && is_string($filterParams['navcode'])) {
            $navcode = Ebh()->db->escape($filterParams['navcode']);
            $params[] = '`navcode` in ('.implode('\',\'',explode(',',$navcode)).')';
        }
        if (isset($filterParams['crid'])) {
            $params[] = '`crid`='.intval($filterParams['crid']);
        }
        if (isset($filterParams['uid'])) {
            $params[] = '`uid`='.intval($filterParams['uid']);
        }
        if (isset($filterParams['status'])) {
            $params[] = '`status`='.intval($filterParams['status']);
        }
        if (isset($filterParams['type'])) {
            $params[] = '`type`='.intval($filterParams['type']);//原创文章类型为1，其他为0
        } else {
            $params[] = '`type`=0';
        }
        if (isset($filterParams['early'])) {
            $params[] = '`dateline`>='.intval($filterParams['early']);
        }
        if (isset($filterParams['latest'])) {
            $params[] = '`dateline`<='.intval($filterParams['latest']);
        }
        if (isset($filterParams['q'])) {
            $params[] = '`subject` LIKE '.Ebh()->db->escape('%'.$filterParams['q'].'%');
        }
        $sql = 'SELECT COUNT(1) AS `c` FROM `ebh_news`';
        if (!empty($params)) {
            $sql .= ' WHERE '.implode(' AND ', $params);
        }
        $ret = Ebh()->db->query($sql)->row_array();
        if (!empty($ret)) {
            return $ret['c'];
        }
        return false;
    }

     /**
     * 按文章id统计文章评论数
     * @param $articleids str ,文章id str
     */
    public function getReviewsByArticleId($articleids,$status=0) {
        $status = intval($status);
        if (!$articleids) {
            return array();
        }
        if ($status) {
            $sql = 'select itemid,count(1) as count from ebh_news_reviews where del=0 and itemid in('.$articleids.') and status ='.$status.' group by itemid';
        } else {
            $sql = 'select itemid,count(1) as count from ebh_news_reviews where del=0 and itemid in('.$articleids.') group by itemid';
        }
       
        return Ebh()->db->query($sql)->list_array();
    }

     /**
     * 添加原创文章的评论
     * @param $crid 网校ID
     * @param $uid 评论者者ID
     * @param $articleid 原创文章ID
     * @param $params 新闻参数
     * @return bool
     */
    public function addReview($crid, $uid, $params) {
        $crid = (int) $crid;
        $uid = (int) $uid;
        if ($crid < 1 || $uid < 1 || empty($params['message']) || empty($params['articleid']) ) {
            return false;
        }
        $formatParams = array();
        $formatParams['crid'] = $crid;
        $formatParams['uid'] = $uid;
        $formatParams['comment'] = $params['message'];
        $formatParams['itemid'] = $params['articleid'];
        if (isset($params['status'])) {
            $status = intval($params['status']);
            $formatParams['status'] = min(1, max(0, $status));
        } else {
            $formatParams['status'] = 1;
        }
        $formatParams['dateline'] = SYSTIME;
        return Ebh()->db->insert('ebh_news_reviews', $formatParams);
    }

    /**
     * 获取评论列表
     * @param $crid 网校ID
     * @param $uid 评论者者ID
     * @return arr
     */
    public function getReviews($filterParams, $limit = 20, $setKey = false) {
        $params = array();
        if (isset($filterParams['crid'])) {
            $params[] = '`r`.`crid`='.intval($filterParams['crid']);
        }
        if (isset($filterParams['del'])) {
            $params[] = '`r`.`del`='.intval($filterParams['del']);
        }
        $params[] = '`a`.`status`> -1 ';
        if (!empty($filterParams['uid'])) {
            $params[] = '`r`.`uid`='.intval($filterParams['uid']);
        }
        if (!empty($filterParams['articleid'])) {
            $params[] = '`r`.`itemid`='.intval($filterParams['articleid']);
        }
        if (isset($filterParams['status'])) {
            $params[] = '`r`.`status`='.intval($filterParams['status']);
        }
        if (isset($filterParams['early'])) {
             $params[] = '`r`.`dateline`>='.intval($filterParams['early']);
        }
        if (isset($filterParams['latest'])) {
            $params[] = '`r`.`dateline`<='.intval($filterParams['latest']);
        }
        if (isset($filterParams['q'])) {
            $params[] = '`r`.`comment` LIKE '.Ebh()->db->escape('%'.$filterParams['q'].'%');
        }
        $sql = 'SELECT `r`.`rwid`,`r`.`status`,`r`.`uid`,`r`.`itemid`,`r`.`comment`,`r`.`dateline` as `rdateline`,`a`.`subject`,`a`.`uid` as `auid` ,`a`.`viewnum`,`a`.`itemid`,`a`.`dateline` FROM `ebh_news_reviews` `r` LEFT JOIN `ebh_news` `a` ON `r`.`itemid`= `a`.`itemid`';

         if (!isset($filterParams['articleid']) && empty($filterParams['allreviews'])) {//有articleid说明，单个文章下所有评论，不去重
            $countsql = 'SELECT count(distinct `r`.`itemid`) as c FROM `ebh_news_reviews` `r` LEFT JOIN `ebh_news` `a` ON `r`.`itemid`= `a`.`itemid`';
         } else {
              $countsql = 'SELECT count(1) as c FROM `ebh_news_reviews` `r` LEFT JOIN `ebh_news` `a` ON `r`.`itemid`= `a`.`itemid`';
         }
        if (!empty($params)) {
            $sql .= ' WHERE '.implode(' AND ', $params);
            $countsql .= ' WHERE '.implode(' AND ', $params);
         }
        //算出总的数量
        $count = Ebh()->db->query($countsql)->row_array();

        $offset = 0;
        $pagesize = 20;
        if (is_array($limit)) {
            if (isset($limit['pagesize'])) {
                $pagesize = max(1, intval($limit['pagesize']));
            }
            if (isset($limit['page'])) {
                $page = max(1, intval($limit['page']));
                $offset = ($page - 1) * $pagesize;
            }
        } else {
            $pagesize = max(1, intval($limit));
        }
        if (!isset($filterParams['articleid']) && empty($filterParams['allreviews'])) {//有articleid说明，单个文章下所有评论，不去重
            $sql .= ' GROUP BY `r`.`itemid` ORDER BY `r`.`rwid` DESC LIMIT '.$offset.','.$pagesize;
        } else {
            $sql .= ' ORDER BY `r`.`rwid` DESC LIMIT '.$offset.','.$pagesize;
        }
        
        if ($setKey) {
            return Ebh()->db->query($sql)->list_array('itemid');
        }
        $res['list'] = Ebh()->db->query($sql)->list_array();
        $res['totalpage'] = $count['c'];
        return $res;
    }

    /**
     * 更新新闻
     * @param $itemid 新闻ID
     * @param $crid 网校ID
     * @param $params 新闻参数
     * @return int
     */
    public function updateReview($itemid, $params) {
       $itemid = (int) $itemid;
       $formatParams = array();
        if (isset($params['message'])) {
            $formatParams['comment'] = $params['message'];
        }
        if (isset($params['status'])) {
            $status = intval($params['status']);
            $formatParams['status'] = $status;
        }
        if (isset($params['del'])) {
            $formatParams['del'] = intval($params['del']);
        }
        if (empty($formatParams)) {
            return 0;
        }
        $whereStr = '`rwid`='.$itemid;
        return Ebh()->db->update('ebh_news_reviews', $formatParams, $whereStr);
    }

    /**
     * 获取指定用户发表原创文章的数量
     * @param $crid 网校ID
     * @param $uid 原创文章作者ID
     * @return array
     */
    public function myarticleCount($param = array()) {
        $sql = 'SELECT COUNT(*) count FROM `ebh_news` ne';
        $params = array();
        if(!empty($param['crid'])){
            $params[] = 'ne.crid = '.$param['crid'];
        }
        if(!empty($param['uid'])){
            $params[] = 'ne.uid = '.$param['uid'];
        }
        $params[] = 'ne.type = 1 AND ne.status != -1';   //原创文章类型type等于1,status!=1为正常状态
        if (!empty($params)) {
            $sql .= ' WHERE '.implode(' AND ', $params);
        }
        return Ebh()->db->query($sql)->row_array();
    }
    /**
     * 获取指定用户原创文章评论的数量
     * @param $crid 网校ID
     * @param $uid 原创文章作者ID
     * @return array
     */
    public function reviewCount($param = array()) {
        $sql = 'SELECT COUNT(*) count FROM `ebh_news_reviews` nr';
        $params = array();
        if(!empty($param['crid'])){
            $params[] = 'nr.crid = '.$param['crid'];
        }
        if(!empty($param['uid'])){
            $params[] = 'nr.uid = '.$param['uid'];
        }
        $params[] = 'nr.del = 0';   //del等于1表示评论被删除
        if (!empty($params)) {
            $sql .= ' WHERE '.implode(' AND ', $params);
        }
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 新闻分类集
     * @param int $crid 网校ID
     * @return mixed
     */
    public function newsNavCodeList($crid) {
        $sql = 'SELECT `navcode` FROM `ebh_news` WHERE `crid`='.$crid.' AND `status`=1';
        return Ebh()->db->query($sql)->list_field();
    }
    /**
     * 资讯列表(根据首页装扮的配置获取首页资讯)
     * @param $filterParams 过滤条件
     * @return mixed
     */
    public function getNewsLists($filterParams) {
        $params = array();
        $result = array();
        if (isset($filterParams['navcode']) && is_string($filterParams['navcode'])) {
            $navcode = Ebh()->db->escape($filterParams['navcode']);
            $params[] = '`navcode` in ('.implode('\',\'',explode(',',$navcode)).')';
        }
        if (isset($filterParams['begin'])) {
            $offset=intval($filterParams['begin']);
        }
        if (isset($filterParams['last'])) {
            $pagesize=intval($filterParams['last']);
        }
        if (isset($filterParams['crid'])) {
            $params[] = '`crid`='.intval($filterParams['crid']);
        }
        if (isset($filterParams['type'])) {
            $params[] = '`type`='.intval($filterParams['type']);
        } else {
            $params[] = '`type`=0 ';
        }
        $params[] = '`status`=1';
        $sql = 'SELECT `itemid`,`navcode`,`subject`,`note`,`thumb`,`viewnum`,`displayorder`,`dateline` FROM `ebh_news`';
        if (!empty($params)) {
            $sql .= ' WHERE '.implode(' AND ', $params);
        }
        //在主类或子类下,按照资讯分类排序号排序
        if(!empty($filterParams['ranktype'])){
            $ranktype = trim(Ebh()->db->escape($filterParams['ranktype']),'\'');
            $sql .= ' ORDER BY `'.$ranktype.'` DESC, `itemid` DESC ';
        }else{
            $sql .= ' ORDER BY `itemid` DESC ';
        }
        if(isset($offset) && !empty($pagesize)){
            $sql .= ' LIMIT '.$offset.','.$pagesize;
        }
        $res = Ebh()->db->query($sql)->list_array();
        if(!empty($res) && is_array($res)){
            $result = $res;
        }
        return $result;
    }
    /**
     * 资讯列表数量(根据首页装扮的配置获取首页资讯)
     * @param $filterParams 过滤条件
     * @return mixed
     */
    public function getNewsListsCount($filterParams) {
        $params = array();
        $result = 0;
        if (isset($filterParams['navcode']) && is_string($filterParams['navcode'])) {
            $navcode = Ebh()->db->escape($filterParams['navcode']);
            $params[] = '`navcode` in ('.implode('\',\'',explode(',',$navcode)).')';
        }
        if (isset($filterParams['crid'])) {
            $params[] = '`crid`='.intval($filterParams['crid']);
        }
        if (isset($filterParams['type'])) {
            $params[] = '`type`='.intval($filterParams['type']);
        } else {
            $params[] = '`type`=0 ';
        }
        $params[] = '`status`=1';
        $sql = 'SELECT count(1) count FROM `ebh_news`';
        if (!empty($params)) {
            $sql .= ' WHERE '.implode(' AND ', $params);
        }
        $res = Ebh()->db->query($sql)->row_array();
        if(!empty($res['count'])){
            $result = $res['count'];
        }
        return $result;
    }

    /**
     * 移动资讯(交换位置改变排序号)
     * @param $data itemid,crid,step,navcode,ranktype
     * @return bool
     */
    public function rankNews($data) {
        $param = array();
        $itemidarr = array();   //资讯id的集合
        $ranktype = '';         //排序类型,prank主类中资讯的排序,rank子类中资讯的排序(没有子类的资讯分类均为rank)
        $navcode = '';          //资讯分类的集合
        $nowpos = 0;            //当前资讯的排序位置
        $changepos = 0;         //待交换位置资讯的排序
        $changeitemid = 0;      //待交换位置资讯的id
        $itemid = intval($data['itemid']);  //资讯id
        $crid = intval($data['crid']);
        $step = intval($data['step']);      //资讯移动,1下移,-1上移
        $navcode = Ebh()->db->escape($data['navcode']);
        $ranktype = trim(Ebh()->db->escape($data['ranktype']),'\'');
        if (($itemid == 0) || ($crid == 0) || ($step == 0) || empty($navcode) || empty($ranktype) || !in_array($ranktype,array('prank','rank')) || !in_array($step,array(1,-1))) {
            return false;
        }
        //按照排序顺序查询当前分类下的所有资讯列表
        $newsql = 'SELECT `itemid`,`prank`,`rank`,`navcode` FROM ebh_news WHERE `crid`='.$crid.' AND `navcode` in ('.implode('\',\'',explode(',',$navcode)).') AND `status`=1 AND `type`=0';
        $newsql .= ' ORDER BY `'.$ranktype.'` DESC,`itemid` DESC';//先按照主类或子类资讯排序,再id
        $news = Ebh()->db->query($newsql)->list_array();
        if(empty($news)){
            return FALSE;
        }
        //获取当前资讯及要交换位置资讯的id、排序号
        if(is_array($news)){
            foreach ($news as $index=>$new){
                if(!empty($new['itemid'])){
                    if(empty($new[$ranktype])){         //判断当排序字段为空,记录资讯id
                        $itemidarr[] = $new['itemid'];
                    }
                    if($new['itemid'] == $itemid){      //记录当前资讯的排序位置
                        $nowpos = !empty($new[$ranktype]) ? $new[$ranktype] : $itemid;
                        $theindex = $index+$step;       //step为1下移,-1上移
                        if($theindex>=0){
                            $changeitemid = !empty($news[$theindex]['itemid']) ? $news[$theindex]['itemid'] : 0;            //记录下一个(或者上一个)资讯的id
                            $changepos = !empty($news[$theindex][$ranktype]) ? $news[$theindex][$ranktype] : $changeitemid; //下一个(或者上一个)资讯的排序位置
                        }
                    }
                }
            }
            //如果当前分类下的资讯没有prank或rank排序号,更新。(排序号更新为其itemid)
            if(!empty($itemidarr) && is_array($itemidarr)){
                $upranksql = 'UPDATE ebh_news SET `'.$ranktype.'`=`itemid` WHERE `'.$ranktype.'`=0 AND `itemid` IN ('.implode(',',$itemidarr).')';
                Ebh()->db->query($upranksql,false);
            }
        }
        //交换排序位置
        if(!empty($nowpos) && !empty($changepos) && !empty($changeitemid)){
            $changesql = 'UPDATE ebh_news SET `'.$ranktype.'`=(CASE WHEN `itemid`='.$itemid.' THEN '.$changepos.' WHEN `itemid`='.$changeitemid.' THEN '.$nowpos.' END)';
            $changesql .= ' WHERE `crid`='.$crid.' AND `itemid` in ('.$itemid.','.$changeitemid.')';
            return Ebh()->db->query($changesql,false);
        }
    }
}