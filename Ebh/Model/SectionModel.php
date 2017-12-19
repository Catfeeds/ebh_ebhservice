<?php
/**
 * 课程章节信息model类
 */
class SectionModel{
	public function insert($param = array()) {
		$sid = Ebh()->db->insert('ebh_sections',$param);
		return $sid;
	}
	public function getSections($param = array()) {
		$sql = 'select s.sid,s.sname from ebh_sections s ';
		$wherearr = array();
		if(!empty($param['folderid'])) 
			$wherearr[] = 's.folderid='.$param['folderid'];
		if(!empty($param['crid']))
			$wherearr[] = 's.crid='.$param['crid'];
		if(!empty($wherearr))
			$sql .= ' WHERE '.implode (' AND ', $wherearr);
		else
			return FALSE;
		$sql .=' order by s.displayorder asc';
		return Ebh()->db->query($sql)->list_array();
	}
	/**
	 * 获取课程下章节的最大排序号
	 * @param type $folderid课程编号
	 * @return int 最大排序号
	 */
	public function getMaxOrder($folderid) {
		$sql = 'select max(s.displayorder) maxorder from ebh_sections s where s.folderid='.$folderid;
		$item = Ebh()->db->query($sql)->row_array();
		$maxorder = 1;
		if(!empty($item))
			$maxorder = $item['maxorder'];
		return $maxorder;
	}
	public function update($param = array(),$wherearr = array()) {
		if(empty($param) || empty($wherearr) || empty($wherearr['sid']))
			return FALSE;
		return Ebh()->db->update('ebh_sections',$param,$wherearr);
	}
	public function del($wherearr = array()) {
		if(empty($wherearr) || empty($wherearr['sid']))
			return FALSE;
		return Ebh()->db->delete('ebh_sections',$wherearr);
	}
	public function changeOrder($param) {
		$sid = $param['sid'];
		$crid = $param['crid'];
		$isup = $param['isup'];
		$sql = 'select s.folderid,s.displayorder from ebh_sections s where s.sid='.$sid.' AND crid='.$crid;
		$section = Ebh()->db->query($sql)->row_array();
		if(empty($section)){
			return FALSE;
		}
		$folderid = $section['folderid'];
		$optarr = array('>','<');
		$orderarr = array('asc','desc');
		$qsql = 'select s.sid,s.displayorder from ebh_sections s where s.folderid='.$folderid.' and s.displayorder '.$optarr[$isup].$section['displayorder'].' order by displayorder '.$orderarr[$isup].' limit 1';
		$upsection = Ebh()->db->query($qsql)->row_array();
		
		if(empty($upsection))
			return FALSE;
		
		Ebh()->db->begin_trans();
		Ebh()->db->update('ebh_sections',array('displayorder'=>$upsection['displayorder']),array('sid'=>$sid));
		Ebh()->db->update('ebh_sections',array('displayorder'=>$section['displayorder']),array('sid'=>$upsection['sid']));
		if (Ebh()->db->trans_status() === false) {
			Ebh()->db->rollback_trans();
			return false;
		}
		Ebh()->db->commit_trans();
		return TRUE;
	}
	

}
