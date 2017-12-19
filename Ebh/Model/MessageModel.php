<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:49
 */
class MessageModel{

    public $db = null;
    public function __construct(){
        $this->db = Ebh()->db;
    }

    /**
     *添加私信记录
     */
    public function insert($param) {
        if(!isset($param['fromid']) || empty($param['toid']) || empty($param['type']) || !isset($param['message']) || empty($param['crid']))
            return FALSE;
        $setarr = array();
        if(!empty($param['fromid']))
            $setarr['fromid'] = $param['fromid'];
        if(!empty($param['toid']))
            $setarr['toid'] = $param['toid'];
        if(!empty($param['sourceid']))
            $setarr['sourceid'] = $param['sourceid'];
        if(!empty($param['crid']))
            $setarr['crid'] = $param['crid'];
        if(!empty($param['type']))
            $setarr['type'] = $param['type'];
        if(!empty($param['ulist']))
            $setarr['ulist'] = $param['ulist'];
        if(isset($param['message']))
            $setarr['message'] = $param['message'];
        if(!empty($param['isread']))
            $setarr['isread'] = $param['isread'];
        $setarr['dateline'] = SYSTIME;
        $mid = $this->db->insert('ebh_messages',$setarr);
        return $mid;
    }
    /**
     *更新私信状态为已读
     *@param int $mid 私信记录ID
     */
    public function updateIsRead($mid) {
        if(empty($mid))
            return FALSE;
        $wherearr = array('mid'=>$mid);
        $setarr = array('isread'=>1);
        $afrows = $this->db->update('ebh_messages',$setarr,$wherearr);
        return $afrows;
    }
    /**
     *获取收件人的未读数据
     *@param int $touid 收件人编号
     */
    public function getUnReadCount($toid,$crid=0) {
        if(empty($toid))
            return FALSE;
        $unreadlist = array();
        $sql = "select type,count(*) as count from ebh_messages where toid=$toid and crid=$crid and isread=0 group by type";
        $rows = $this->db->query($sql)->list_array();
        foreach($rows as $row) {
            $unreadlist[$row['type']] = $row['count'];
        }
        return $unreadlist;
    }
    /**
     *获取私信收件箱列表
     */
    public function getMsgList($param) {
        $wherearr = array();
        $sql = 'select m.mid,m.fromid,m.toid,m.sourceid,m.type,m.ulist,m.message,m.isread,m.dateline,u.sex,u.face,u.username,u.realname,u.groupid from ebh_messages m left join ebh_users u';
        if(empty($param['toid']) && empty($param['fromid']))
            return FALSE;
        if(!empty($param['toid']))
        {
            $wherearr[] = 'toid=' . intval($param['toid']);
            $sql = $sql .= ' on m.fromid=u.uid';
        }
        elseif(!empty($param['fromid']))
        {
            $wherearr[] = 'fromid=' . intval($param['fromid']);
            $sql = $sql .= ' on m.toid=u.uid';
        }
        if(!empty($param['crid']))
            $wherearr[] = 'crid=' . intval($param['crid']);
        if(!empty($param['type']))
            $wherearr[] = 'type=' . intval($param['type']);
        if(!empty($param['typelist']))
            $wherearr[] = 'type in (' . $param['typelist'] . ')';
        if(isset($param['isread']))
            $wherearr[] = 'isread=' . intval($param['isread']);
        if(!empty($wherearr))
            $sql .= ' WHERE ' . implode(' AND ', $wherearr);
        $sql .= ' ORDER BY m.mid DESC';
        if(!empty($param['limit'])) {
            $sql .= ' limit '.$param['limit'];
        } else {
            if (empty($param['page']) || $param['page'] < 1)
                $page = 1;
            else
                $page = $param['page'];
            $pagesize = empty($param['pagesize']) ? 20 : $param['pagesize'];
            $start = ($page - 1) * $pagesize;
            $sql .= ' limit ' . $start . ',' . $pagesize;
        }
        return $this->db->query($sql)->list_array();
    }

    /**
     * 获取私信收件箱总数
     * @param  array $param 查询参数
     * @return int        总数
     */
    public function getMsgCount($param) {
        $wherearr = array();
        $sql = 'select count(*) as count from ebh_messages';
        if(empty($param['toid']) || empty($param['fromid']))
            return FALSE;
        if(!empty($param['toid']))
            $wherearr[] = 'toid=' . intval($param['toid']);
        if(!empty($param['crid']))
            $wherearr[] = 'crid=' . intval($param['crid']);
        if(!empty($param['fromid'])&&$param['fromid']>0)
            $wherearr[] = 'fromid=' . intval($param['fromid']);
        if(!empty($param['type']))
            $wherearr[] = 'type=' . intval($param['type']);
        if(!empty($param['typelist']))
            $wherearr[] = 'type in (' . $param['typelist'] . ')';
        if(isset($param['isread']))
            $wherearr[] = 'isread=' . intval($param['isread']);
        if(!empty($wherearr))
            $sql .= ' WHERE ' . implode(' AND ', $wherearr);
        $row = $this->db->query($sql)->row_array();
        return $row['count'];
    }
    /**
     *获取私信 发件箱列表
     */
    public function getSendMsgList($param) {
        if(empty($param['fromid']))
            return FALSE;
        $sql = 'select mid,toid,type,message,isread,dateline from ebh_messages where toid='.$param['fromid'];
        if(!empty($param['limit'])) {
            $sql .= ' limit '.$param['limit'];
        } else {
            if (empty($param['page']) || $param['page'] < 1)
                $page = 1;
            else
                $page = $param['page'];
            $pagesize = empty($param['pagesize']) ? 10 : $param['pagesize'];
            $start = ($page - 1) * $pagesize;
            $sql .= ' limit ' . $start . ',' . $pagesize;
        }
        return $this->db->query($sql)->list_array();
    }
    /**
     *获取同种消息未读记录
     *@param int $toid
     *@param int $sourceid
     *@param int $type
     */
    public function getLastUnReadMessage($toid,$sourceid,$type) {
        $sql = "select mid,fromid,ulist,dateline from ebh_messages where toid=$toid and sourceid=$sourceid and type=$type and isread=0";
        return $this->db->query($sql)->row_array();
    }
    /**
     *更新私信消息
     */
    public function updateMessage($param) {
        if(empty($param['mid']))
            return FALSE;
        $setarr = array();
        if(!empty($param['ulist']))
            $setarr['ulist'] = $param['ulist'];
        if(!empty($param['message']))
            $setarr['message'] = $param['message'];
        if(!empty($param['dateline']))
            $setarr['dateline'] = $param['dateline'];
        $wherearr = array('mid'=>$param['mid']);
        return $this->db->update('ebh_messages',$setarr,$wherearr);
    }
    /**
     *根据toid和type类型批量更新已读状态
     */
    public function resetMessageCount($toid,$crid,$type) {
        $setarr = array('isread'=>1);
        $wherearr = array('toid'=>$toid,'crid'=>$crid,'type'=>$type);
        return $this->db->update('ebh_messages',$setarr,$wherearr);
    }
}