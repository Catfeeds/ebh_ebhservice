<?php
/**
 * 知识点
 */
class ChapterController extends Controller{
	public $chaptermodel;
	
	public function init(){
		$this->chaptermodel = new MychapterModel();
		parent::init();
		
	}
	public function parameterRules(){
		return array(
			'chapterListAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'vid'  =>  array('name'=>'vid','type'=>'int'),
			),
			'selectChapterAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'folderid'  =>  array('name'=>'folderid','require'=>true,'type'=>'int'),
				'chapterid'  =>  array('name'=>'chapterid','require'=>true,'type'=>'int'),
			),
			'isExistsAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int'),
				'chaptername'  =>  array('name'=>'chaptername','require'=>true,'type'=>'string'),
				'chapterid'  =>  array('name'=>'chapterid','type'=>'int'),
			),
			'addChapterAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
				'pid'  =>  array('name'=>'pid','default'=>0,'type'=>'int'),
				'chaptername'  =>  array('name'=>'chaptername','require'=>true,'type'=>'string'),
				'displayorder'  =>  array('name'=>'displayorder','default'=>0,'type'=>'int'),
			),
			'editChapterAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
				'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int'),
				'chaptername'  =>  array('name'=>'chaptername','require'=>true,'type'=>'string'),
				'displayorder'  =>  array('name'=>'displayorder','default'=>0,'type'=>'int'),
				'chapterid'  =>  array('name'=>'chapterid','require'=>true,'type'=>'int'),
			),
			'increaseOrderAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'nextids'  =>  array('name'=>'nextids','require'=>true,'type'=>'int'),
			),
			'delAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'chapterid'  =>  array('name'=>'chapterid','require'=>true,'type'=>'int'),
			),
			'getChildrenAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'chapterid'  =>  array('name'=>'chapterid','require'=>true,'type'=>'int'),
			),
			'changeChapterOrderAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
				'chapterid'  =>  array('name'=>'chapterid','require'=>true,'type'=>'int'),
				'isup'  =>  array('name'=>'isup','type'=>'int'),
			),
			'maxOrderAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
			),
			
		);
	}
	
	/*
	知识点列表
	*/
	public function chapterListAction(){
		$chapterlist = $this->chaptermodel->getNodeList($this->crid,$this->vid);
		return $chapterlist;
	}
	/*
	课程选择知识点
	*/
	public function selectChapterAction(){
		$param['crid'] = $this->crid;
		$param['folderid'] = $this->folderid;
		$param['chapterid'] = $this->chapterid;
		return $this->chaptermodel->selectChapter($param);
		
	}
	/*
	知识点是否重名
	*/
	public function isExistsAction(){
		$param['crid'] = $this->crid;
		$param['pid'] = $this->pid;
		$param['chaptername'] = $this->chaptername;
		$param['chapterid'] = $this->chapterid;
		return $this->chaptermodel->chapterExists($param);
		
	}
	/*
	添加知识点
	*/
	public function addChapterAction(){
		$param['crid'] = $this->crid;
		$param['uid'] = $this->uid;
		$param['displayorder'] = $this->displayorder;
		$param['pid'] = $this->pid;
		$param['chaptername'] = $this->chaptername;
		//设置从主数据库读取,防止主从服务器来不及同步的问题
		Ebh()->db->set_con(0);
		$chapterid = $this->chaptermodel->insert($param);
		return $chapterid;
		
	}
	/*
	编辑知识点
	*/
	public function editChapterAction(){
		$param['crid'] = $this->crid;
		$param['displayorder'] = $this->displayorder;
		$param['pid'] = $this->pid;
		$param['chaptername'] = $this->chaptername;
		$result = $this->chaptermodel->update($param,$this->chapterid);
		return $result;
		
	}
	/*
	多个知识点排序号+1
	*/
	public function increaseOrderAction(){
		$result = $this->chaptermodel->increaseOrder($this->nextids);
		return $result;
		
	}
	
	/*
	删除知识点
	*/
	public function delAction(){
		return $this->chaptermodel->deleteById($this->chapterid, $this->crid);
	}
	
	/*
	查看子知识点
	*/
	public function getChildrenAction(){
		return $this->chaptermodel->getChildren($this->chapterid);
	}
	
	
	/*
	版本上下移动
	*/
	public function changeChapterOrderAction(){
		$param['crid'] = $this->crid;
		$param['chapterid'] = $this->chapterid;
		$param['isup'] = $this->isup;
		
		return $this->chaptermodel->moveit($param);
	}
	
	/*
	版本最大排序号
	*/
	public function maxOrderAction(){
		return $this->chaptermodel->getMaxDisplayOrder(array('crid' => $this->crid, 'level' => 1));
	}
	
}