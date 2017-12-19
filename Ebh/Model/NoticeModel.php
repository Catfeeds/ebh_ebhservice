<?php
/**
* 通知
*/
class NoticeModel {
	private $db;
	public function __construct(){
		$this->db = Ebh()->db;
	}

	/*
	添加通知
	 */

	public function addNotice($param){
		
	}

    /**
     * 更新通知
     * @param $noticeid 通知ID
     * @param $crid 网校ID
     * @param $params 通知参数
     * @return mixed
     */
	public function update($noticeid, $crid, $params) {
	    $update_params = array();
        if (isset($params['title'])) {
            $update_params['title'] = $params['title'];
        }
        if (isset($params['message'])) {
            $update_params['message'] = $params['message'];
        }
        if (isset($params['ntype'])) {
            $update_params['ntype'] = intval($params['ntype']);
        }
        if (isset($params['remind'])) {
            $update_params['remind'] = intval($params['remind']);
        }
        if (isset($params['attid'])) {
            $update_params['attid'] = intval($params['attid']);
        }
        if (isset($params['isreceipt'])) {
            $update_params['isreceipt'] = $params['isreceipt'];
        }
        if (isset($params['receipt'])) {
            $update_params['receipt'] = Ebh()->db->escape_str($params['receipt']);
        }
        $update_params['dateline'] = SYSTIME;
        $whereStr = '`noticeid`='.intval($noticeid).' AND `crid`='.intval($crid);
        return Ebh()->db->update('ebh_notices', $update_params, $whereStr);
    }

    /**
     * 通知列表
     * @param $crid
     * @param $limit
     * @param $orderType 排序：0-默认日期降序，1-日期升序，2-浏览量降序，3-浏览量升序
     * @param bool $setKey
     * @return mixed
     */
	public function getList($crid, $limit, $orderType, $setKey = true) {
	    $offset = 0;
	    $pagesize = 20;
	    if (is_array($limit)) {
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
        $orderStr = '`dateline` DESC';
        switch ($orderType) {
            case 1:
                $orderStr = '`noticeid` ASC';
                break;
            case 2:
                $orderStr = '`viewnum` DESC,`noticeid` DESC';
                break;
            case 3:
                $orderStr = '`viewnum` ASC,`noticeid` DESC';
                break;
        }
        $sql = 'SELECT `noticeid`,`uid`,`title`,`ntype`,`type`,`viewnum`,`dateline`,`districts`,`grades`,`attid`'.
            ' FROM `ebh_notices` WHERE `crid`='.intval($crid).' AND `type`=0'.
            ' ORDER BY '.$orderStr.' LIMIT '.$offset.','.$pagesize;
	    if ($setKey) {
	        return Ebh()->db->query($sql)->list_array('noticeid');
        }
        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 统计通知
     * @param $crid
     * @return int
     */
    public function getCount($crid) {
	    $sql = 'SELECT COUNT(1) AS `c` FROM `ebh_notices` WHERE `crid`='.intval($crid).' AND `type`=0';
	    $ret = Ebh()->db->query($sql)->row_array();
	    if (!empty($ret)) {
	        return $ret['c'];
        }
        return 0;
    }

    /**
     * 删除通知
     * @param $noticeid 通知ID
     * @param $crid 网校ID
     * @return mixed
     */
    public function remove($noticeid, $crid) {
        $whereStr = '`noticeid`='.intval($noticeid).' AND `crid`='.intval($crid);
        return Ebh()->db->delete('ebh_notices', $whereStr);
    }

    /**
     * 通知详情
     * @param $noticeid
     * @param $crid
     */
    public function getModel($noticeid, $crid) {
        $fileds = array('`noticeid`','`isreceipt`','`receipt`', '`uid`', '`title`', '`message`',
            '`ntype`', '`type`', '`cids`', '`viewnum`', '`dateline`',
            '`status`', '`districts`', '`grades`', '`attid`','`remind`');
        $sql = 'SELECT '.implode(',', $fileds).' FROM `ebh_notices` WHERE '.
            '`noticeid`='.intval($noticeid).' AND `crid`='.intval($crid);
        return Ebh()->db->query($sql)->row_array();
    }

    public function add($params) {
        if (empty($params['crid']) || empty($params['uid']) || empty($params['title']) || empty($params['message'])) {
            return false;
        }
        $format_params = array(
            'crid' => intval($params['crid']),
            'uid' => intval($params['uid']),
            'title' => $params['title'],
            'message' => $params['message'],
            'type' => !isset($params['type']) ? 0 : intval($params['type']),
            'ntype' => !isset($params['ntype']) ? 0 : intval($params['ntype']),
            'remind' => !isset($params['remind']) ? 0 : intval($params['remind']),
            'isreceipt' => !isset($params['isreceipt']) ? 0 : intval($params['isreceipt']),
            'receipt' => !isset($params['receipt']) ? '' : Ebh()->db->escape_str($params['receipt']),
            'dateline' => SYSTIME
        );
        if (!empty($params['attid'])) {
            $format_params['attid'] = intval($params['attid']);
        }
        return Ebh()->db->insert('ebh_notices', $format_params);
    }




    public function getNoticeCount($crid,$params){
        //$sql = 'select noticeid,uid,crid,title,message,viewnum,dateline from ebh_notices';
        $sql = 'select count(noticeid) as count from ebh_notices';
        $wherearr = array();

        $wherearr[] = ' crid='.$crid;
        if(!empty($params['groupid']) && $params['groupid'] == 5){
            $wherearr[] = ' (ntype=1 or ntype=2)';
        }else{
            $wherearr[] = ' (ntype=1 or ntype=3)';
        }
        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        $count = Ebh()->db->query($sql)->row_array();
        return $count['count'];
    }

    public function getNoticeList($crid,$params){
        $sql = 'select n.noticeid,n.uid,n.crid,n.title,n.message,n.viewnum,n.dateline,n.attid,u.realname,att.title as attname from ebh_notices n left join ebh_users u on u.uid=n.uid left join ebh_attachments att on att.attid=n.attid';

        $wherearr = array();

        $wherearr[] = ' n.crid='.$crid;
        if(!empty($params['groupid']) && $params['groupid'] == 5){
            $wherearr[] = ' (n.ntype=1 or n.ntype=2)';
        }else{
            $wherearr[] = ' (n.ntype=1 or n.ntype=3)';
        }
        if(!empty($wherearr)){
            $sql.= ' where '.implode(' AND ',$wherearr);
        }
        if(!empty($params['order'])){
            $sql.= ' order by '.$params['order'];
        }else{
            $sql.= ' order by n.dateline desc';
        }
        if(isset($params['limit'])){
            $sql .= ' limit '.$params['limit'];
        }

        return Ebh()->db->query($sql)->list_array();
    }


    /**
     *添加通知的浏览数
     */
    public function addviewnum($noticeid) {
        $wherearr = array('noticeid'=>$noticeid);
        $setarr = array('viewnum'=>'viewnum+1');
        return Ebh()->db->update('ebh_notices',array(),$wherearr,$setarr);
    }


    /**
     *添加通知的回执
     */
    public function addReceipt($params) {
        if (empty($params['crid']) || empty($params['uid']) || empty($params['noticeid'])) {
            return false;
        }
        if (!isset($params['explains'])) {
            $params['explains'] = '';
        }
        $sql = 'select uid from ebh_notice_receipts where uid='.$params['uid'].' and noticeid='.$params['noticeid'].' limit 1';
        //已添加则返回
        $res = Ebh()->db->query($sql)->row_array();
        if (!empty($res)) {
            return false;
        }
        $format_params = array(
            'crid' => intval($params['crid']),
            'uid' => intval($params['uid']),
            'choice' => !isset($params['choice']) ? 0 : intval($params['choice']),
            'explains' => Ebh()->db->escape_str($params['explains']),
            'noticeid' => intval($params['noticeid']),
            'dateline' => SYSTIME
        );
        return Ebh()->db->insert('ebh_notice_receipts', $format_params);
    }

    /**
     * 回执列表
     * @param $filterParams 过滤条件
     * @param int $limit 限制条件
     * @param bool $setKey 是否以receiptid作为键值
     * @return mixed
     */
    public function getListAndTotalPage($filterParams, $limit = 20, $setKey = true) {
        $params = array();
        if (isset($filterParams['noticeid'])) {
            $params[] = '`n`.`noticeid`='.intval($filterParams['noticeid']);
        }
        if (isset($filterParams['uid'])) {
            $params[] = '`n`.`uid`='.intval($filterParams['uid']);
        }
        if (isset($filterParams['groupid'])) {
            $params[] = '`n`.`groupid`='.intval($filterParams['groupid']);
        }
        if (isset($filterParams['q'])) {
            $params[] = '`r`.`explains` LIKE '.Ebh()->db->escape('%'.$filterParams['q'].'%');
        }
        $sql = 'SELECT `n`.`noticeid`,`n`.`uid`,`r`.`crid`,`r`.`dateline`,`r`.`choice`,`r`.`explains` FROM `ebh_usernotices` `n` LEFT JOIN `ebh_notice_receipts` `r` ON `r`.`noticeid`= `n`.`noticeid` AND `r`.`uid`= `n`.`uid`';
        $countsql = 'SELECT count(1) as c FROM `ebh_usernotices` `n` LEFT JOIN `ebh_notice_receipts` `r` ON `r`.`noticeid`= `n`.`noticeid` AND `r`.`uid`= `n`.`uid`';
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
        $sql .= ' ORDER BY `receiptid` DESC LIMIT '.$offset.','.$pagesize;
        if ($setKey) {
            return Ebh()->db->query($sql)->list_array('receiptid');
        }
        $res['list'] = Ebh()->db->query($sql)->list_array();
        $res['totalpage'] = $count['c'];
        return $res;
    }

    /**
     *获取通知回执详情
     */
    public function getReceiptDetail($filterParams=array()) {

        if (isset($filterParams['noticeid'])) {
            $params[] = '`n`.`noticeid`='.intval($filterParams['noticeid']);
        }
        if (isset($filterParams['uid'])) {
            $params[] = '`n`.`uid`='.intval($filterParams['uid']);
        }
        if (isset($filterParams['groupid'])) {
            $params[] = '`n`.`groupid`='.intval($filterParams['groupid']);
        }

        $sql = 'SELECT `n`.`noticeid`,`n`.`status`,`n`.`uid`,`r`.`crid`,`r`.`dateline`,`r`.`choice`,`r`.`explains` FROM `ebh_usernotices` `n` LEFT JOIN `ebh_notice_receipts` `r` ON `r`.`noticeid`= `n`.`noticeid` AND `r`.`uid`= `n`.`uid`';
        if (!empty($params)) {
            $sql .= ' WHERE '.implode(' AND ', $params);
        }
        //所有已回执的人
        if (!isset($filterParams['uid'])) {
            return Ebh()->db->query($sql)->list_array();
        } else {//回执固定那个人
            $sql .= ' LIMIT 1';
            //算出总的数量
            return Ebh()->db->query($sql)->row_array();
        }
        
    }
}