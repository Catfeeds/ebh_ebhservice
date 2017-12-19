<?php
/**
 *个人空间审核model类,针对pan_billchecks表
 *
 */
class PanbillchecksModel {
	var $db = NULL;

	public function __construct(){
		$this->db = new Db(Ebh()->config->get('pandb'));;
	}
	/**
	 * 审核处理
	 *
	 */
	public function check($param){
		$toid = $param['toid'];
		$role = $param['role'];
		$type = $param['type'];
		if(!$toid){return false;}
		//检查是否持存在
		$sql = "select count(*) as count from pan_billchecks where toid = {$toid} and type = {$type}";
		$row = $this->db->query($sql)->row_array();
		//var_dump($row);exit;
		if($row['count']>0){
			//更新
			if($role=='admin'){//管理员审核
				$setArr['admin_uid'] = $param['admin_uid'];
				$setArr['admin_status'] = $param['admin_status'];
				$setArr['admin_remark'] = htmlentities($param['admin_remark'],ENT_NOQUOTES,"utf-8");
				$setArr['admin_ip'] = $param['admin_ip'];
				$setArr['admin_dateline'] = time();
			}elseif($role=='teach'){//教师审核
				$setArr['teach_uid'] = $param['teach_uid'];
				$setArr['teach_status'] = $param['teach_status'];
				$setArr['teach_remark'] = $param['teach_remark'];
				$setArr['teach_ip'] = $param['teach_ip'];
				$setArr['teach_dateline'] = time();
			}
//			p($setArr);die;
			$this->db->update("pan_billchecks",$setArr,array('toid'=>$toid,'type'=>$type));
			
			//网校对应修改课件等状态
			
		}else{
			//添加
			if($role=='admin'){//管理员审核
				$data = array(
					'toid'=>$toid,
					'type'=>$type,
					'admin_uid'=>$param['admin_uid'],
					'admin_status'=>$param['admin_status'],
					'admin_remark'=>htmlentities($param['admin_remark'],ENT_NOQUOTES,"utf-8"),
					'admin_ip'=>$param['admin_ip'],
					'admin_dateline'=>time(),
                    'teach_remark'=>'',
				);
			}elseif($role=='teach'){//教师审核
				$data = array(
					'toid'=>$toid,
					'type'=>$type,
					'teach_uid'=>$param['teach_uid'],
					'teach_status'=>$param['teach_status'],
					'teach_remark'=>$param['teach_remark'],
					'teach_ip'=>$param['teach_ip'],
					'teach_dateline'=>time()			
				);
			}
			$this->db->insert("pan_billchecks",$data);				
		}
		
		//更新feeds/photos/blogs表
		if($param['teach_status']==2){
			$this->updatestatus($toid,  $type);
		}
		
		
		return $this->db->affected_rows();
		
	}

	/**
	 * 批量审核
	 */
	public function multcheck($param){
		$idarr = explode(",", $param['ids']);
		if(!is_array($idarr)){
			return false;
		}
		foreach($idarr as $id){
			$param['toid'] = $id;
			$params = $param;
			$ck = $this->check($params);
			if($ck<=0){
				break;
				return false;
			}
		}
		return true;
	}

    /**
     * 撤销审核操作,并还原相关信息
     */
    public function revoke($param){
        $toid = $param['toid'];
        $type = $param['type'];
        $status = $param['status'];
        if($status == 2 && $type == 11){
            $old_status = $this->db->query('select old_status from pan_billchecks where toid = '.$toid.' and type = '.$type)->row_array();
            $old_status = $old_status['old_status'];
            $this->db->update('pan_files',array('status'=>$old_status),array('fileid'=>$toid));
            $row = $this->db->query('SELECT * FROM pan_files WHERE fileid='.$toid)->row_array();
            if (!empty($row) && $row['status'] == 0)
                $this->db->update('pan_userinfos', array(), array('uid'=>$row['uid'], 'crid'=>$row['crid']),array('filesize'=>'filesize+'.$row['size']));
        }
        $data = array(
            'teach_status' => $param['teach_status'],
            'admin_status' => $param['teach_status'],
            'teach_uid' => $param['teach_uid'],
        );
        $this->db->update("pan_billchecks",$data,array('toid'=>$toid,'type'=>$type));
        return $this->db->affected_rows();
    }

	/**
	 * 更新网校课件,附件等状态
	 * 
	 */
	public function updatestatus($toid,$type){
		if ($type == 11) {
            $status = $this->db->query('select status from pan_files where fileid='.$toid)->row_array();
            $this->db->update('pan_billchecks', array('old_status' => $status['status']), array('toid' => $toid, 'type' => $type));
			$row = $this->db->query('SELECT * FROM pan_files WHERE fileid='.$toid)->row_array();
			$this->db->update('pan_files',array('status'=>2),array('fileid'=>$toid));
			if (!empty($row) && $row['status'] == 0)
				$this->db->update('pan_userinfos', array(), array('uid'=>$row['uid'], 'crid'=>$row['crid']),array('filesize'=>'filesize-'.$row['size']));
		}
	}

}
?>