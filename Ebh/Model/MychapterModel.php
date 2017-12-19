<?php
/**
 *我的知识点模型
 */
class MychapterModel{
	public function getList($param= array()){
		$sql = 'SELECT c.*,f.foldername FROM  ebh_schchapters c  JOIN ebh_folders f ON c.folderid = f.folderid';
		$wherearr = array ();
		if (!empty($param['chapterid'] )) {
			$wherearr [] = 'c.chapterid = ' . $param['chapterid'];
		}
		if(!empty($param['crid'])){
			$wherearr[] = 'c.crid = '.$param['crid'];
		}
		if (isset($param['pid'] )) {
			$wherearr [] = 'c.pid = ' . $param['pid'];
		}
		if(! empty ( $param['chapterpath'] )){
			$wherearr [] = ' c.chapterpath like \''.$param['chapterpath'].'%\' ';
		}
		if(! empty ( $param['level'] )){
			$wherearr [] = ' c.level = ' . $param['level'];
		}
		if(! empty ( $param['folderid'] )){
			$wherearr [] = ' c.folderid = ' . $param['folderid'];
		}
		if (!empty( $wherearr )) {
			$sql.= ' WHERE ' . implode ( ' AND ', $wherearr );
		}
		if(!empty($param['order'])){
			$sql.=' order by '.$param['order'];
		}else{
			$sql.=' order by c.pid,c.displayorder,c.chapterid ';
		}
		if (!empty($param['limit'])) {
			$sql .= ' limit ' . $param['limit'];
		} else {
			$sql .= ' limit 0,10';
		}
		$chapterList = Ebh()->db->query($sql)->list_array();
		$newChapterList = array();
		foreach ($chapterList as $chapter) {
			$newChapterList[$chapter['chapterid']] = $chapter;
		}
		$result = array();
		$chapterList = $this->getchaptertrees($newChapterList);
		if(!empty($chapterList)) {
			foreach($chapterList as $chapter) {
				$result[] = $chapter;
			}
		}
		return $result;
	}
	//获取知识点列表，与getList的区别是知识点不再左连接后再查询提高效率
	public function getChapterList($param= array()){
		$sql = 'SELECT c.chapterid, c.pid, c.chaptername FROM  ebh_schchapters c ';
		$wherearr = array ();
		if (!empty($param['chapterid'] )) {
			$wherearr [] = 'c.chapterid = ' . $param['chapterid'];
		}
		if(!empty($param['crid'])){
			$wherearr[] = 'c.crid = '.$param['crid'];
		}
		if (isset($param['pid'] )) {
			$wherearr [] = 'c.pid = ' . $param['pid'];
		}
		if(! empty ( $param['chapterpath'] )){
			$wherearr [] = ' c.chapterpath like \''.$param['chapterpath'].'%\' ';
		}
		if(! empty ( $param['level'] )){
			$wherearr [] = ' c.level = ' . $param['level'];
		}
		if (!empty( $wherearr )) {
			$sql.= ' WHERE ' . implode ( ' AND ', $wherearr );
		}
		if(!empty($param['order'])){
			$sql.=' order by '.$param['order'];
		}else{
			$sql.=' order by c.pid,c.displayorder,c.chapterid ';
		}
		if (!empty($param['limit'])) {
			$sql .= ' limit ' . $param['limit'];
		}
		$chapterList = Ebh()->db->query($sql)->list_array();
		return $chapterList;
	}

	/**
	*以树结构方式获取章节列表
	*/
	function getchaptertrees($chapterlist) {
		$chapterarr = array();
		$chaptertree = array();
		foreach($chapterlist as $chapter) {
			if (empty($chapter['pid']))
				$chaptertree[0][] = $chapter['chapterid'];
			else {
				$chaptertree[$chapter['pid']][] = $chapter['chapterid'];
			}
		}
		$pid = 0;
		$this->getchapterarray($chapterlist,$chaptertree,$chapterarr,$pid);
		return $chapterarr;
	}
	function getchapterarray($chapterlist,$chaptertree,&$chapterarr,$pid) {
		if(isset($chaptertree[$pid])) {
			foreach($chaptertree[$pid] as $childchapter) {
				$chapterarr[$childchapter] = $chapterlist[$childchapter];
				$this->getchapterarray($chapterlist,$chaptertree,$chapterarr,$childchapter);
			}
		}
	}

	/**
	*判断给定章节名称是否存在
	*/
	public function chapterExists($param) {
		$sql = 'select * from ebh_schchapters c where c.crid='.intval($param['crid']).' and c.pid='.intval($param['pid'])." and c.chaptername='".Ebh()->db->escape_str($param['chaptername'])."'";
		
		if(!empty($param['chapterid'])) {
			$sql .=' and chapterid != '.$param['chapterid'];
		}
		
		$item = Ebh()->db->query($sql)->list_array();
		return empty($item)?false:true;
	}

	/**
	*添加知识点信息
	*/
	public function insert($param = array()) {
		$setarr = array ();
		if(!isset($param['chaptername']))
			return false;
		if (!empty($param['pid'])) {
			$setarr['pid'] = intval($param['pid']);
			$upitem = $this->getChapterById($param['pid']);
			if(!empty($upitem)) {
				$setarr['level'] = $upitem['level'] + 1;
				$setarr['chapterpath'] = $upitem['chapterpath'];
			}
		}
		if (isset( $param['chaptername'] )) {
			$setarr['chaptername'] =  $param['chaptername'] ;
		}
		if (!empty($param['crid'])){
			$setarr['crid'] =  intval($param['crid']) ;
		}
		if (! empty( $param['folderid'] )) {
			$setarr['folderid'] =  intval($param['folderid']) ;
		}
		if (! empty( $param['uid'] )) {
			$setarr['uid'] =  intval($param['uid']) ;
		}
		if (!empty($param['displayorder'])){
			$setarr['displayorder'] =  intval($param['displayorder']) ;
		}
		$chapterid = Ebh()->db->insert('ebh_schchapters', $setarr);
		
		if($chapterid) {
			$this->fixChapterPath($chapterid);
		}
		return $chapterid;
	}
	/**
	*更新知识点信息
	*/
	function update($param,$chapterid) {
		$setarr = array ();
		if (isset( $param['chaptername'] )) {
			$setarr['chaptername'] =  $param['chaptername'] ;
		}
		if (!empty($param['folderid'])){
			$setarr['folderid'] =  intval($param['folderid']) ;
		}
		if (isset($param['pid'])){
			$setarr['pid'] =  intval($param['pid']) ;
		}
		if (! empty ( $param['level'] )) {
			$setarr['level'] =  intval($param['level']) ;
		}
		if (!empty ($param['chapterpath'])){
			$setarr['chapterpath'] =  $param['chapterpath'] ;
		}
		if (!empty ($param['displayorder'])){
			$setarr['displayorder'] =  intval($param['displayorder']) ;
		}
		if (!empty ($param['uid'])){
			$setarr['uid'] =  intval($param['uid']) ;
		}
		if(empty($setarr) || empty($chapterid))
			return false;
		$wherearr = array();
		$wherearr['chapterid'] = $chapterid;
		$wherearr['crid'] = $param['crid'];
		$result = Ebh()->db->update('ebh_schchapters',$setarr,$wherearr);
		if(empty($param['chapterpath'])){
			$chapterpath = $this->fixChapterPath($chapterid);
		}
		$curchapter = $this->getChapterById($chapterid);
		if(!empty($curchapter)){
			$this->updateChildren($curchapter['chapterid'],$curchapter['level'],$curchapter['chapterpath']);
		}
		return $result;
	}

	/**
	*根据章节id获取章节信息
	*/
	public function getChapterById($chapterid) {
		if(empty($chapterid))
			return false;
		$sql = 'select c.*,f.foldername from ebh_schchapters c left join ebh_folders f on (c.folderid=f.folderid) where c.chapterid='.$chapterid;
		return Ebh()->db->query($sql)->row_array();
	}
	/**
	*根据chapterid删除章节
	*/
	public function deleteById($chapterid,$crid = 0) {
		$where = array('chapterid'=>$chapterid,'crid'=>$crid);
		return Ebh()->db->delete('ebh_schchapters',$where);
	}
	/*
	查询子知识点
	*/
	public function getChildren($chapterid){
		$sql = 'select * from ebh_schchapters where pid='.$chapterid;
		return Ebh()->db->query($sql)->list_array();
	}
	
	/**
	*获取老师所教课程,用于知识点编辑
	*/
	function getfolder($crid,$tid = 0){
		$result = array();
		if(!empty($tid)){
			$sql = 'SELECT f.folderid,f.foldername,tf.tid FROM ebh_folders f left join  ebh_teacherfolders tf on f.folderid = tf.folderid  where tf.crid = '.$crid.' and f.folderlevel = 2 and f.power != 2 and tf.tid = '.$tid;
			$result = array();
			$result = Ebh()->db->query($sql)->list_array();
		}else{
			$sql = 'SELECT f.folderid,f.foldername FROM ebh_folders f where f.crid = '.$crid.' and f.folderlevel = 2 and f.power != 2';
			$result = array();
			$result = Ebh()->db->query($sql)->list_array();
		}
		return $result;
	}

	function multimove($param){
		$setarr = array();
		if (!empty($param['folderid'])){
			$setarr['folderid'] =  intval($param['folderid']) ;
		}
		if (isset($param['pid'])){
			$setarr['pid'] =  intval($param['pid']) ;
		}
	}
	//根据章节id批量将其排序加1
	function increaseOrder($chapteridArr){
		$wherearr = ' chapterid in ('.implode(',', $chapteridArr).')';
		$setarr = array('displayorder' => 'displayorder+1');
		$afrows = Ebh()->db->update('ebh_schchapters', array(), $wherearr, $setarr);
		return $afrows;
	}

	/**
	 *递归升级子知识点的level和chapterpath
	 *$pid 父级id
	 *$level 父级level
	 *$chapterpath 父级chapterpath
	 */
	private function updateChildren($pid = 0,$level = 0,$chapterpath = ''){
		$sql = 'select chapterid from ebh_schchapters where pid = '.$pid;
		$children = Ebh()->db->query($sql)->list_array();
		if(empty($children)){
			return;
		}
		foreach ($children as $child) {
			Ebh()->db->update('ebh_schchapters', array('level'=>$level+1,'chapterpath'=>$chapterpath.'/'.$child['chapterid']), array('chapterid'=>$child['chapterid']));
			$this->updateChildren($child['chapterid'],$level+1,$chapterpath.'/'.$child['chapterid']);
		}
	}

	//根据chapterid递归获取正确的chapterpath
	private function _fixChapterPath($chapterid = 0){
		$path = '';
		$curchapter = $this->getChapterById($chapterid);
		//获取父节点
		$pchapter = $this->getChapterById($curchapter['pid']);
		if(!empty($pchapter)){
			$path = $this->_fixChapterPath($pchapter['chapterid']).$path;
		}
		if(empty($chapterid)){
			$chapterid = '';
		}
		return $path.'/'.$chapterid;
	}
	//根据chapterip修正chapterpath
	public function fixChapterPath($chapterid = 0){
		if(empty($chapterid)){
			return;
		}
		$chapterpath = $this->_fixChapterPath($chapterid);
		$level = substr_count($chapterpath,'/');
		$setarr = array(
			'chapterpath'=>$chapterpath,
			'level'=>$level
		);
		$wherearr = array(
			'chapterid'=>$chapterid
		);
		return Ebh()->db->update('ebh_schchapters',$setarr,$wherearr);
	}

	/**
	 * 获取知识点列表
	 * @param  intval $crid 学校编号
	 * @param  intval $versionid 版本编号(获取指定版本下的所有节点)
	 * @return mix	   知识点列表
	 */
	public function getNodeList($crid, $versionid = 0) {
		$sql = 'SELECT chapterid,chaptername,pid,displayorder FROM ebh_schchapters WHERE crid=' . intval($crid);
		if(!empty($versionid))
		{
			$version_sql = 'SELECT chapterid,chapterpath FROM ebh_schchapters WHERE chapterid=' . intval($versionid);
			$version = Ebh()->db->query($version_sql)->row_array();
			$version_path = $version['chapterpath'];
			$sql.=" AND chapterpath like '". $version_path ."/%'";
		} else {
			$sql.= ' AND level = 1';
		}
		$sql.=' order by pid,displayorder,chapterid';
		$list = Ebh()->db->query($sql)->list_array();
		
		return $list;
	}
	/**
	 * 获取版本列表
	 * @param  intval $crid 学校编号
	 * @return mix	   版本列表
	 */
	public function getversionlist($crid) {
		$sql = 'SELECT chapterid,chaptername,displayorder FROM ebh_schchapters WHERE level=1 AND crid=' . intval($crid);
		$sql.=' order by pid,displayorder,chapterid ';

		$list = Ebh()->db->query($sql)->list_array();

		return $list;
	}

	/**
	 * 获取同节点最大排序数字
	 * @param  [type] $param [description]
	 * @return [type]		[description]
	 */
	public function getMaxDisplayOrder($param) {
		$wherearr = array();
		$sql = 'SELECT max(displayorder) maxorder FROM ebh_schchapters';
		if (!empty($param['crid'] )) {
			$wherearr [] = 'crid = ' . $param['crid'];
		}
		if (isset($param['level'] )) {
			$wherearr [] = 'level = ' . $param['level'];
		}
		if (!empty( $wherearr )) {
			$sql.= ' WHERE ' . implode ( ' AND ', $wherearr );
		}

		$row = Ebh()->db->query($sql)->row_array();
		$maxorder = 0;
		if(!empty($row))
			$maxorder = $row['maxorder'];
		return $maxorder;
	}

	/*
	 * 版本新后台移动
	*/
	public function moveit($param){
		if(empty($param['chapterid']) || empty($param['crid']))
			return FALSE;
		if(!empty($param['isup'])){
			$compare = '<';
			$op = '-';
			$order = 'displayorder desc,chapterid asc';
		} else {
			$compare = '>';
			$op = '+';
			$order = 'displayorder asc,chapterid asc';
		}
		$sql = 'SELECT chapterid,displayorder FROM ebh_schchapters WHERE chapterid='. intval($param['chapterid']) .
		' AND crid=' . $param['crid'] . ' AND level=1';
		$thischapter = Ebh()->db->query($sql)->row_array();
		$sqlsameorder = 'SELECT chapterid,displayorder FROM ebh_schchapters WHERE displayorder='. intval($thischapter['displayorder']) .
		' AND crid=' . $param['crid'] . ' AND level=1 AND chapterid<>'.$thischapter['chapterid'];
		$sameorder = Ebh()->db->query($sqlsameorder)->row_array();
		if (!empty($sameorder))
		{
			$sqlAllforone = 'update ebh_schchapters set displayorder=displayorder'.$op.'1 where crid='.$param['crid'].' and displayorder'.$compare.'='.$thischapter['displayorder'].' and level=1 and chapterid<>'.$thischapter['chapterid'];
			Ebh()->db->query($sqlAllforone);
		}

		$sql2 = 'select chapterid,displayorder from ebh_schchapters';
		$wherearr[] = 'crid='.$param['crid'];
		$wherearr[] = 'displayorder'.$compare.$thischapter['displayorder'];
		$wherearr[] = 'level=1';
		$sql2 .= ' where '.implode(' AND ',$wherearr);
		$sql2 .= ' order by '.$order;
		$sql2 .= ' limit 1';
		$deschapter = Ebh()->db->query($sql2)->row_array();
		if(empty($deschapter))
			return TRUE;
		Ebh()->db->update('ebh_schchapters',array('displayorder'=>$deschapter['displayorder']),array('chapterid'=>$thischapter['chapterid']));
		Ebh()->db->update('ebh_schchapters',array('displayorder'=>$thischapter['displayorder']),array('chapterid'=>$deschapter['chapterid']));
		return TRUE;
	}

	/**
	*更新知识点信息
	*/
	function editname($param,$chapterid) {
		$setarr = array ();
		if (isset( $param['chaptername'] )) {
			$setarr['chaptername'] =  $param['chaptername'] ;
		}
		if (!empty ($param['uid'])){
			$setarr['uid'] =  intval($param['uid']) ;
		}
		if(empty($setarr) || empty($chapterid))
			return FALSE;
		$wherearr = array();
		$wherearr['chapterid'] = $chapterid;
		$wherearr['crid'] = $param['crid'];
		$result = Ebh()->db->update('ebh_schchapters',$setarr,$wherearr);
		return $result;
	}

	/**
	 * 根据知识点路径获取完整的知识点名称
	 * @param  string $chapterpath 知识点路径
	 * @return string			  完整的知识点名称
	 */
	function getFullName($chapterpath) {
		$fullname = '';
		$chapterarray = array();
		$fullnameArr = array();
		if (empty($chapterpath))
			return $fullname;
		$chapteridArr = explode('/', $chapterpath);
		if (!empty($chapteridArr)){
			unset($chapteridArr[0]);
			$sql = 'SELECT chapterid,chaptername FROM ebh_schchapters WHERE chapterid in ('.implode(',', $chapteridArr).')';
			$chapterlist = Ebh()->db->query($sql)->list_array();
			if (!empty($chapterlist))
			{
				foreach($chapterlist as $chapter)
				{
					$chapterarray[$chapter['chapterid']] = $chapter['chaptername'];
				}

				foreach($chapteridArr as $chapterid)
				{
					$fullnameArr[] = empty($chapterarray[$chapterid]) ? '' : $chapterarray[$chapterid];
				}

				$fullname = implode(' > ', $fullnameArr);
			}
		}

		return $fullname;
	}




	/**
	 * @获取知识点列表树结构
	 */
	function getmychapterlist($param = array()) {
		$sql = 'select c.chapterid,c.chaptername,c.pid,chapterpath from ebh_schchapters c';
		$wherearr = array();
		if(!empty($param['folderid'])) {
			$wherearr[] = 'c.folderid='.$param['folderid'];
		}
		if(!empty($param['level'])) {
			$wherearr[] = 'c.level='.$param['level'];
		}
		if(isset($param['pid'])) {
			$wherearr[] = 'c.pid='.$param['pid'];
		}
		if(!empty($param['crid'])){
			$wherearr[] = 'c.crid='.$param['crid'];
		}
		if(!empty($param['chapterids'])){
			$wherearr[] = 'c.chapterid in ('.implode(',', $param['chapterids']).')';
		}
		if(!empty($wherearr))
			$sql .= ' where '.implode(' and ',$wherearr);
		$sql .= ' order by c.pid,c.displayorder,c.chapterid';
		return Ebh()->db->query($sql)->list_array();
	}

	/**
	 * 获取网校知识点第二级节点
	 */
	function getchaptersbytoppid($topid,$crid){
		if(empty($topid) && empty($crid)){
			return false;
		}
		$sql = 'select c.chapterid,c.chaptername from ebh_schchapters c where c.crid = '.$crid.' and pid = '.$topid;
		$sql .= ' order by c.pid,c.displayorder,c.chapterid';
		return Ebh()->db->query($sql)->list_array();
	}

	/**
	 * 根据网校获取知识点顶级节点
	 */
	function getchaptersbycrid($crid){
		if(empty($crid)){
			return false;
		}
		$sql = 'select c.chapterid,c.chaptername from ebh_schchapters c where c.crid = '.$crid.' and level = 1';
		$sql .= ' order by c.pid,c.displayorder,c.chapterid';
		return Ebh()->db->query($sql)->list_array();
	}

	/**
	 * 新版获取顶级节点下的所有子节点
	 */
	public function getnodelistv2($crid, $versionid = 0) {
		$sql = 'SELECT chapterid as id,chaptername as name,pid as pId,displayorder FROM ebh_schchapters WHERE crid=' . intval($crid);
		if(!empty($versionid))
			$sql.=" AND chapterpath like '/". $versionid ."/%'";
		$sql.=' order by pid,displayorder,chapterid';
		$chapterlist = Ebh()->db->query($sql)->list_array();
		foreach ($chapterlist as &$chapter) {
			$chapter['open'] = true;
		}
		return $chapterlist;
	}

	/**
	 * 关联知识点
	 */
	public function selectchapter($param){
		if (empty($param['folderid']) || empty($param['crid']) || empty($param['chapterid'])){
			return FALSE;
		}
		$wherearr['folderid'] = $param['folderid'];
		$wherearr['crid'] = $param['crid'];
		$setarr['chapterid'] = $param['chapterid'];
		return Ebh()->db->update('ebh_folders',$setarr,$wherearr);
	}

 }