<?php
/**
 * 云盘文件model类
 */
class FileModel {
	var $db = NULL;
	var $ebhdb = NULL;

	public function __construct(){
		$this->db = new Db(Ebh()->config->get('pandb'));;
		$this->ebhdb = Ebh()->db;
	}

	/**
	 * 获取文件列表
	 */
	public function getFileList($param){
		$sql = 'SELECT f.fileid,f.isdir,f.title,f.path,f.dateline,f.size,f.suffix,f.uid,f.crid,f.isshare,ck.admin_status,s.ispreview,ck.teach_status,ck.del,ck.admin_dateline,ck.teach_dateline,ck.teach_uid,ck.admin_uid FROM pan_files f LEFT JOIN pan_sources s ON f.sid=s.sid LEFT JOIN pan_billchecks ck ON ck.toid = f.fileid AND ck.type=11';
		$wherearr[] = 'f.isdir=0';
		if (isset($param['q'])&&$param['q']!=''){
            $qstr = $this->db->escape_str($param['q']);
            $wherearr[] = ' (f.title like \'%' . $qstr. '%\' )';
        }
        if(!empty($param['access'])){
            $wherearr[]='f.crid in ('.$this->db->escape_str($param['access']).')';
        }
        if(!empty($param['crid'])){
        	$wherearr[]='f.crid='.intval($param['crid']);
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
                $wherearr[] = 'ck.admin_status in(1,2) and ck.del=0';
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
		if(!empty($wherearr))
			$sql.= ' where '.implode(' AND ',$wherearr);

		$sql.= ' order by fileid desc';
		if(!empty($param['limit'])) {
			$sql .= ' limit '.$param['limit'];
		} else {
			if (empty($param['page']) || $param['page'] < 1)
				$page = 1;
			else
				$page = $param['page'];
			$pagesize = empty($param['pagesize']) ? 300 : $param['pagesize'];
			$start = ($page - 1) * $pagesize;
			$sql .= ' limit ' . $start . ',' . $pagesize;
        }
		return $this->db->query($sql)->list_array();
	}

	/**
	 * 获取文件总数
	 */
	public function getFileCount($param){
		$count = 0;
		$sql = 'SELECT count(*) count FROM pan_files f LEFT JOIN pan_billchecks ck ON ck.toid = f.fileid AND ck.type=11';
		$wherearr[] = 'f.isdir=0';
		if (isset($param['q'])&&$param['q']!=''){
            $qstr = $this->db->escape_str($param['q']);
            $wherearr[] = ' (f.title like \'%' . $qstr. '%\' )';
        }
        if(!empty($param['access'])){
            $wherearr[]='f.crid in ('.$this->db->escape_str($param['access']).')';
        }
        if(!empty($param['crid'])){
        	$wherearr[]='f.crid='.intval($param['crid']);
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
                $wherearr[] = 'ck.admin_status in(1,2) and ck.del=0';
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
		if(!empty($wherearr))
			$sql.= ' where '.implode(' AND ',$wherearr);

		$row = $this->db->query($sql)->row_array();
		if(!empty($row))
			$count = $row['count'];
        return $count;
	}


	public function getFileById($fileid){
		$wherearr = array();
		$sql = 'SELECT f.fileid,f.title,f.dateline,f.size,f.suffix,f.uid,f.crid,ck.admin_status,ck.admin_remark,ck.teach_status,ck.teach_remark,ck.del,ck.admin_dateline,ck.teach_uid,ck.teach_dateline,ck.delline,ck.admin_ip,ck.teach_ip, ck.admin_uid FROM pan_files f LEFT JOIN pan_billchecks ck ON ck.toid=f.fileid AND ck.type=11 WHERE fileid='.intval($fileid);
		$row = $this->db->query($sql)->row_array();
		return $row;
	}
}