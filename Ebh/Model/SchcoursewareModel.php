<?php
/**
 * 作业系统主观题Model类 SchcoursewareModel
 */
class SchcoursewareModel {
	var $db = NULL;
	public function __construct(){
		$this->db = Ebh()->db;
	}
	/**
	*获取主观题列表
	*/
	public function getCourseList($param) {
		$sql = 'select c.cwid,c.eid,c.uid,c.type,c.cwname,c.cwsource,c.cwurl,c.cwlength,c.cwsize,c.dateline from ebh_schcoursewares c';
		$wherearr = array();
		if(!empty($param['cwid'])) {
			$wherearr[] = 'c.cwid ='.$param['cwid'];
		}
		if(isset($param ['eid'])){
			$wherearr[] = 'c.eid = '.$param['eid'];
		}
		if(isset($param ['uid'])){
			$wherearr[] = 'c.uid = '.$param['uid'];
		}
		if(!empty($wherearr))
			$sql .= ' where '.implode(' and ',$wherearr);
		if(!empty($param['order']))
			$sql .= ' order by '.$param['order'];
		else
			$sql .= ' order by c.cwid desc';
		if(!empty($param['limit']))
			$sql .= ' limit '.$param['limit'];
		else
			$sql .= ' limit 0,10';
		return $this->db->query($sql)->list_array();
	}

	/**
	*根据cwid获取课件详情
	*/
	function getcoursebyid($cwid) {
		$sql = "select c.cwid,c.eid,c.uid,c.cwname,c.cwsource,c.cwurl,c.cwlength,c.cwsize,c.dateline,c.type,c.html from ebh_schcoursewares c where c.cwid=$cwid";
		return $this->db->query($sql)->row_array();
	}

	/**
	*获取作业课件编号，用户编号等信息获取答题笔记笔记
	*/
	function getCourseNote($param = array()) {
		$sql = 'select n.noteid,n.cwid,n.aid,n.qid,n.source,n.url,n.score,n.remark,n.dateline,n.images,n.voices,n.upanswer,n.size from ebh_schnotes n';
		$wherearr = array();
		if(isset( $param['noteid'])){
			$wherearr[] = 'n.noteid = '.$param['noteid'];
		}
		if(isset($param ['uid'])){
			$wherearr[] = 'n.uid = '.$param['uid'];
		}
		if(isset($param ['aid'])){
			$wherearr[] = 'n.aid = '.$param['aid'];
		}
		if(isset($param ['cwid'])){
			$wherearr[] = 'n.cwid = '.$param['cwid'];
		}
		if(isset($param ['qid'])){
			$wherearr[] = 'n.qid = '.$param['qid'];
		}
		if(empty($wherearr))
			return FALSE;
		$sql .= ' where '.implode(' and ',$wherearr);
		$sql .= ' limit 0,1';
		return $this->db->query($sql)->row_array();
	}
	/**
	*根据aid，和cwid列表获取作业课件编号，用户编号等信息获取答题笔记笔记
	*/
	function getCoursenoteBycwids($param = array()) {
		$sql = 'select n.noteid,n.cwid,n.qid,n.aid,n.source,n.url,n.score,n.remark,n.dateline,n.images,n.voices,n.upanswer from ebh_schnotes n';
		$wherearr = array();
		if(isset($param ['aid'])){
			$wherearr[] = 'n.aid = '.$param['aid'];
		}
		if(isset($param ['uid'])){
			$wherearr[] = 'n.uid = '.$param['uid'];
		}
		if(isset($param ['cwids'])){
			$wherearr[] = 'n.cwid in('.$param['cwids'].')';
		}
		if(empty($wherearr))
			return FALSE;
		$sql .= ' where '.implode(' and ',$wherearr);
		return $this->db->query($sql)->list_array();
	}
	/**
	*更新课件笔记
	*/
	function updatenote($param = array(),$wherearr = array()){
		if(empty($wherearr))
			return FALSE;
		$setarr = array();
		if (isset($param['uid'])) {
            $setarr['uid'] = intval($param['uid']);
        }
		if (isset($param['aid'])) {
            $setarr['aid'] = intval($param['aid']);
        }
        if (isset($param['cwid'])){
        	$setarr['cwid'] = intval($param['cwid']);
        }
        if (isset($param['source'])) {
            $setarr['source'] = $param['source'];
        }
		if (isset($param['score'])){
			$setarr['score'] = $param['score'];
		}
		if (isset($param['remark'])){
			$setarr['remark'] = $param['remark'];
		}
		if (!empty($param['upanswer'])){
			$setarr['upanswer'] = $param['upanswer'];
		}
        if (!empty($param['url'])) {
            $setarr['url'] = $param['url'];
        }
		if(empty($setarr))
			return FALSE;
		return $this->db->update('ebh_schnotes',$setarr,$wherearr);
	}
	/**
	*添加作业笔记
	*/
	function insertnote($param){
		if(empty($param['uid']) || empty($param['cwid']))
			return FALSE;
		$setarr=array();
		$setarr['uid'] = $param['uid'];
		$setarr['cwid'] = $param['cwid'];
		if (isset($param['aid'])){
			$setarr['aid'] = $param['aid'];
		}
		if (!empty($param['source'])){
			$setarr['source'] = $param['source'];
		}
		if (isset($param['score'])){
			$setarr['score'] = $param['score'];
		}
		if (isset($param['remark'])){
			$setarr['remark'] = $param['remark'];
		}
		if (!empty($param['url'])){
			$setarr['url'] = $param['url'];
		}
		if (!empty($param['upanswer'])){
			$setarr['upanswer'] = $param['upanswer'];
		}
		if (!empty($param['size'])){
			$setarr['size'] = $param['size'];
		}
		$setarr ['dateline'] = SYSTIME;
		return $this->db->insert('ebh_schnotes',$setarr);
	}
	/**
	*删除作业笔记
	*/
	function deletenote($wherearr = array()){
		if(!empty($wherearr)){
			return $this->db->delete('ebh_schnotes',$wherearr);
		}else {
			return  false;
		}
	}

	/*
	主观题上传的图片删除
	*/
	function delUpanswer($param){
		$sql = 'select upanswer from ebh_schnotes where cwid='.$param['cwid'].' and uid='.$param['uid'].' and qid='.$param['qid'];
		$result = $this->db->query($sql)->row_array();
		$upanswer = explode(",", $result['upanswer']);
		$key=array_search($param['upanswer'],$upanswer);
		if($key!==false){
			unset($upanswer[$key]);
		}
		$upanswer = implode(",", $upanswer);
		if($this->db->update('ebh_schnotes',array('upanswer'=>$upanswer),array('cwid'=>$param['cwid'],'uid'=>$param['uid'],'qid'=>$param['qid']))){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	
	/**
	*添加作业课件图片
	*/
	function insertschcwimages($param){
		if(empty($param['aid']) || empty($param['cwid']) || empty($param['url']))
			return false;
		$aid = $param['aid'];
		$url = $param['url'];
		$cwid = $param['cwid'];
		$index = empty($param['index'])?0:$param['index'];
		$urlarr = array();
		if($index == 1) {	//重新开始
			$urlarr[] = $url;
		} else {
			$imagesql = 'select images from ebh_schnotes where aid='.$aid.' and cwid='.$cwid;
			$imageitem = $this->db->query($imagesql)->row_array();
			if(!empty($imageitem['images'])) {
				$urlarr = explode(',',$imageitem['images']);
			}
			$urlarr[] = $url;
		}
		$images = implode(',',$urlarr);
		return $this->db->update('ebh_schnotes',array('images'=>$images),array('aid'=>$aid,'cwid'=>$cwid));
	}
	/**
	*添加作业课件语音
	*/
	function insertschcwvoices($param){
		if(empty($param['aid']) || empty($param['cwid']) || empty($param['url']))
			return false;
		$aid = $param['aid'];
		$url = $param['url'];
		$cwid = $param['cwid'];
		$index = empty($param['index'])?0:$param['index'];
		$urlarr = array();
		if($index == 1) {	//重新开始
			$urlarr[] = $url;
		} else {
			$voicesql = 'select voices from ebh_schnotes where aid='.$aid.' and cwid='.$cwid;
			$voiceitem = $this->db->query($voicesql)->row_array();
			//
			if(!empty($voiceitem['voices'])) {
				$urlarr = explode(',',$voiceitem['voices']);
			}
			$urlarr[] = $url;
		}
		$voices = implode(',',$urlarr);
		return $this->db->update('ebh_schnotes',array('voices'=>$voices),array('aid'=>$aid,'cwid'=>$cwid));
	}
	
	/**
	*提交主观题,更新aid
	@param  uid,aid,cwidlist
	*/
	public function updateaid($param) {
		if(empty($param['aid']) || empty($param['cwidlist']) || empty($param['uid']))
			return false;
		$aid = $param['aid'];
		$uid = $param['uid'];
		$cwidlist = implode(',',$param['cwidlist']);
		$flag = true;
		foreach ($param['cwidlist'] as $key => $value) {
			$qid = $param['qidlist'][$key];
			$sql = 'select noteid from ebh_schnotes where cwid='.$value.' and uid='.$uid.' and qid='.$qid;
			$result = $this->db->query($sql)->row_array();
			if ($result['noteid']) {
				$upsql = 'update ebh_schnotes set aid='.intval($aid).' where aid is not null and cwid in ('.$cwidlist.') and uid='.$uid.' and qid='.$qid;
				$this->db->query($upsql);
				// if(!$_SGLOBAL ['db']->query($upsql)){
					// $flag = false;
				// }
			}else{
				$upsql = 'insert into ebh_schnotes (cwid,aid,uid,dateline,qid) value('.$value.','.$aid.','.$uid.','.SYSTIME.','.$qid.')';
				$this->db->query($upsql);
				// if(!$_SGLOBAL ['db']->query($upsql)){
					// $flag = false;
				// }
			}

		}
	}
}
?>