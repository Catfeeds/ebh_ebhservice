<?php

/**
 * 附件
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/23
 * Time: 15:48
 */
class AttachmentModel {
    /**
     * 获取附件
     * @param $attid
     * @return mixed
     */
    public function getModel($attid) {
        $fields = array('`attid`', '`cwid`', '`sourceid`', '`source`', '`url`', '`filename`', '`suffix`', '`size`');
        $sql = 'SELECT '.implode(',', $fields).' FROM `ebh_attachments` WHERE `attid`='.intval($attid);
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 添加附件
     * @param $uid
     * @param $crid
     * @param $params
     * @return mixed
     */
    public function add($uid, $crid, $params) {
        $format_params = array(
            'uid' => intval($uid),
            'crid' => intval($crid)
        );
        if (isset($params['checksum'])) {
            $format_params['checksum'] = $params['checksum'];
        }
        if (isset($params['title'])) {
            $format_params['title'] = $params['title'];
        }
        if (isset($params['message'])) {
            $format_params['message'] = $params['message'];
        }
        if (isset($params['source'])) {
            $format_params['source'] = $params['source'];
        }
        if (isset($params['url'])) {
            $format_params['url'] = $params['url'];
        }
        if (isset($params['filename'])) {
            $format_params['filename'] = $params['filename'];
        }
        if (isset($params['suffix'])) {
            $format_params['suffix'] = $params['suffix'];
        }
        if (isset($params['size'])) {
            $format_params['size'] = intval($params['size']);
        }
        if (isset($params['status'])) {
            $format_params['status'] = intval($params['status']);
        }
        $format_params['dateline'] = SYSTIME;
        return Ebh()->db->insert('ebh_attachments', $format_params);
    }


    /**
    * 根据课件编号等信息获取附件列表
    * @param array $queryarr 
    * @return array 附件列表数组
    */
    public function getAttachmentListByCwid($queryarr = array()) {
        $sql = 'SELECT a.attid,a.title,a.filename,a.source,a.url,a.suffix,a.size,a.`status`,a.dateline,a.ispreview from ebh_attachments a';
        $wherearr = array();
        $wherearr[] ='a.cwid=' . $queryarr['cwid'];
        if(isset($queryarr['status']))
            $wherearr[] = 'a.status='.$queryarr['status'];
        $sql.= ' where '.implode(' AND ',$wherearr);
        $sql .= ' ORDER BY  a.attid desc ';
        return Ebh()->db->query($sql)->list_array();
    }
    /**
    *根据cwid删除附件
    */
    public function deletebycwid($cwid){
        $where = array('cwid'=>intval($cwid));
        return Ebh()->db->delete('ebh_attachments',$where);
    }
    /**
    * 根据课件编号等信息获取附件总数
    * @param array $queryarr 
    * @return int
    */
    public function getAttachmentCountByCwid($queryarr = array()) {
        $count = 0;
        $sql = 'SELECT count(*) count from ebh_attachments a ';
        $wherearr = array();
        $wherearr[] ='a.cwid=' . $queryarr['cwid'];
        if(isset($queryarr['status']))
            $wherearr[] = 'a.status='.$queryarr['status'];
        $sql.= ' where '.implode(' AND ',$wherearr);
        $countrow = Ebh()->db->query($sql)->row_array();
        if (!empty($countrow))
            $count = $countrow['count'];
        return $count;
        }
    /*
    附件总数量
    @param array $param
    @return int
    */
    public function getattachmentcount($param) {
        $sql = 'select count(*) count from ebh_attachments a '
        .' left join ebh_coursewares cw on cw.cwid = a.cwid  '
        .' left join ebh_billchecks ck on ck.toid = a.attid and ck.type=2 ';
        if (isset($param['q'])){
        $qstr = Ebh()->db->escape_str($param['q']);
        $wherearr[] = ' ( a.title like \'%' . $qstr . '%\' or a.suffix like \'%' . $qstr . '%\')';
        }
        if(!empty($param['access'])){
            $wherearr[]='a.crid in ('.Ebh()->db->escape_str($param['access']).')';
        }
        //管理员
        if($param['role']=='admin'){
            if($param['admin_status']>0){
                $wherearr[] = 'ck.admin_status ='.$param['admin_status'];
            }
            if($param['cat']==0){
                $wherearr[] = '(ck.admin_status is null or ck.admin_status=0 or ck.admin_status = 3)';
            }
            if($param['cat']==1){
                $wherearr[] = '(ck.admin_status in(1,2) and ck.del=0)';
            }
            if($param['cat']==2){
                $wherearr[] = 'ck.del=1';
            }
        //教师
        }elseif($param['role']=='teach'){
            if($param['teach_status']>0){
                $wherearr[] = '(ck.teach_status ='.$param['teach_status']. ') or (ck.admin_status='.$param['teach_status']. ')';
            }
            if($param['cat']==0){
               $wherearr[] = '( ck.teach_status is null or ck.teach_status = 0 ) and (ck.admin_status is null or ck.admin_status = 3 or ck.admin_status = 0)';
            }
            if($param['cat']==1){
                $wherearr[] = 'ck.teach_status>0 and ck.del=0';
            }
            if($param['cat']==2){
                $wherearr[] = 'ck.del=1';
            }
        }
        if(!empty($param['crid'])){
            if(is_array($param['crid'])){
                $wherearr[] = 'a.crid in( '.implode(',', $param['crid']).')';
                }else{
                    $wherearr[] = 'a.crid ='.$param['crid'];
                }
            }
            if (!empty($wherearr))
                $sql.=' where ' . implode(' AND ', $wherearr);
            //var_dump($sql);
            $count = Ebh()->db->query($sql)->row_array();
            return $count['count'];
            }
    /*
    所有附件列表
    @param array $param
    @return array 列表数组
    */
    public function getattachmentlist($param) {
        $sql = 'select rc.folderid,a.uid,a.title,a.suffix,a.source,a.size,a.status,a.dateline,a.attid,a.crid,a.message,a.url,cw.title as cwtitle,cw.islive,cw.catid,ck.admin_dateline,ck.teach_dateline,ck.admin_status,ck.teach_uid,ck.teach_status,ck.del, ck.admin_uid from ebh_attachments a '
        .' left join ebh_coursewares cw on cw.cwid = a.cwid  '
        .' left join ebh_roomcourses rc on rc.cwid = cw.cwid  '
        .' left join ebh_billchecks ck on ck.toid = a.attid and ck.type=2 ';        
        if (!empty($param['q'])){
            $qstr = Ebh()->db->escape_str($param['q']);
            $wherearr[] = ' ( a.title like \'%' . $qstr . '%\' or a.suffix like \'%' . $qstr . '%\')';
        }
        if(!empty($param['access'])){
            $wherearr[]='a.crid in ('.Ebh()->db->escape_str($param['access']).')';
        }
            //管理员
            if($param['role']=='admin'){
                if($param['admin_status']>0){
                    $wherearr[] = 'ck.admin_status ='.$param['admin_status'];
                }
                if($param['cat']==0){
                    $wherearr[] = '(ck.admin_status is null or ck.admin_status = 0 or ck.admin_status = 3)';
                }
                if($param['cat']==1){
                    $wherearr[] = '(ck.admin_status in(1,2) and ck.del=0)';
                }
                if($param['cat']==2){
                    $wherearr[] = 'ck.del=1';
                }
                //教师
            }elseif($param['role']=='teach'){
                if($param['teach_status']>0){
                    $wherearr[] = '(ck.teach_status ='.$param['teach_status']. ' or ck.admin_status='.$param['teach_status']. ')';
                }
                if($param['cat']==0){
                    $wherearr[] = '( ck.teach_status is null or ck.teach_status = 0 ) and (ck.admin_status is null or ck.admin_status = 3 or ck.admin_status = 0)';
                }
                if($param['cat']==1){
                    $wherearr[] = 'ck.teach_status>0 and ck.del=0';
                }
                if($param['cat']==2){
                    $wherearr[] = 'ck.del=1';
                }
            }
            if(!empty($param['crid'])){
                if(is_array($param['crid'])){
                    $wherearr[] = 'a.crid in( '.implode(',', $param['crid']).')';
                }else{
                        $wherearr[] = 'a.crid ='.$param['crid'];
                }
            }
        if (!empty($wherearr))
            $sql.=' where ' . implode(' AND ', $wherearr);
        $sql.=' order by a.dateline DESC';
        if (!empty($param['limit']))
            $sql.= ' limit ' . $param['limit'];
        $rows =  Ebh()->db->query($sql)->list_array();
        //下面是对应优化代码
        $cridstr = '';
        $folderidstr = '';
        $cridrows=array();
        $folderidrows=array();
        foreach($rows as $key=>$row){
            if(!empty($row['crid'])){
                $cridstr.= $row['crid'].',';
            }
            if(!empty($row['folderid'])){
                $folderidstr .= $row['folderid'].',';
            }
        }
        $cridstr = implode(',',array_unique(explode(',',rtrim($cridstr, ','))));
        $folderidstr = implode(',',array_unique(explode(',',rtrim($folderidstr, ','))));
            //学校名称
            if($cridstr!=''){
                $ssql = 'select crid,crname from ebh_classrooms where crid in('.$cridstr.')';
                $cridrows =  Ebh()->db->query($ssql)->list_array();
                $cridrows = $this->_arraycoltokey($cridrows,'crid');
            }
            //分类名称
            if($folderidstr!=''){
                $fsql =  'select folderid,foldername from ebh_folders where folderid in('.$folderidstr.')';
                $folderidrows =  Ebh()->db->query($fsql)->list_array();
                $folderidrows = $this->_arraycoltokey($folderidrows,'folderid');
            }
            //附件审核人名称
            foreach($rows as &$row){
                $row['crname'] = $cridrows[$row['crid']]['crname'];
                $row['foldername'] = empty($folderidrows[$row['folderid']]['foldername'])?'':$folderidrows[$row['folderid']]['foldername'];
            }
            return $rows;
        }
    /*
    编辑
    @param array $param
    @return int 影响行数
    */
    public function editattachment($param) {
        if (isset($param['status']))
            $setarr['status'] = $param['status'];
        if (!empty($param['title']))
            $setarr['title'] = $param['title'];
        if (!empty($param['message']))
            $setarr['message'] = $param['message'];
        $wherearr = array('attid' => $param['attid']);
        $row = Ebh()->db->update('ebh_attachments', $setarr, $wherearr);
        return $row;
        }
    /*
    删除附件
    @param int $attid
    @return int
    */
    public function deleteattachment($attid) {
        return Ebh()->db->delete('ebh_attachments', 'attid=' . $attid);
    }
    
    public function getCridById($id){
        $sql="select crid from ebh_attachments where attid=".Ebh()->db->escape($id);
        $row=Ebh()->db->query($sql)->row_array();
        return $row['crid'];
    }
    /**
    * 二维数组某个列的值作为索引键
    * @param unknown $data
    * @param string $key
    *
    */
    protected  function _arraycoltokey($array, $key = '') {
        if(empty($key)) return ;
        $newarray = array();
        foreach ($array as $row){
            $newarray[$row[$key]] = $row;
            }
            return $newarray;
        }
    /*
    根据id获取附件
    */
    public function getAttachById($attid){
        $sql = "SELECT a.attid,a.uid,a.crid,a.cwid,a.title,a.message,a.source,a.url,a.filename,a.suffix,a.size,a.ispreview,a.`status`,a.dateline,u.realname,cw.title as ctitle,cr.crname,
        ck.admin_status,ck.admin_remark,ck.teach_status,ck.teach_remark,ck.del,ck.teach_uid,ck.admin_dateline,ck.teach_dateline,ck.delline,ck.admin_ip,ck.teach_ip, ck.admin_uid "
        ." FROM  ebh_attachments a "
        ." left join ebh_users u on u.uid =  a.uid"
        ." left join ebh_coursewares cw on cw.cwid = a.cwid "
        ." left join ebh_billchecks ck on ck.toid = a.attid"
        ."  left join ebh_classrooms cr on cr.crid = a.crid"            
        ." WHERE a.attid = $attid";
        return Ebh()->db->query($sql)->row_array();
    }
	/**
     * 获取开场视频
     * @param $attid 附件ID
     * @return mixed
     */
    public function getIntro($attid) {
        $sql = 'SELECT `b`.`url`,`b`.`source`,`b`.`suffix`,`b`.`filename`,`b`.`attid` FROM `ebh_folder_intros` `a` JOIN `ebh_attachments` `b` ON `b`.`attid`=`a`.`attid` WHERE `a`.`attid`='.intval($attid).' AND `b`.`status`=1';
        return Ebh()->db->query($sql)->row_array();
    }
	
	/*
	 *获取多个附件，资讯和网校详情用
	*/
	public function getMultiAttachByAttid($attid,$crid){
		$fields = array('`attid`', '`cwid`', '`sourceid`', '`source`', '`url`', '`filename`', '`suffix`', '`size`');
        $sql = 'SELECT '.implode(',', $fields).' FROM `ebh_attachments` WHERE `attid` in ('.$attid.') and crid='.$crid;
        return Ebh()->db->query($sql)->list_array();
	}

}