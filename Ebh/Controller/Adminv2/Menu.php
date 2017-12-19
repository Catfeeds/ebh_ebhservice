<?php
/**
 * 菜单
 */

class MenuController extends Controller{
	protected $menumodel;
	public function init(){
		$this->menumodel = new MenuModel();
		parent::init();
	}

	public function parameterRules(){
		return array(
			'listAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
				'page' =>	array('name'=>'page','default'=>1,'type'=>'int'),
				'nohide' =>	array('name'=>'nohide','default'=>0,'type'=>'int'),
				'withmodule' =>	array('name'=>'withmodule','default'=>0,'type'=>'int'),
				'onlylevel1' =>  array('name'=>'onlylevel1','default'=>0,'type'=>'int'),
				'nomalteacher'  =>  array('name'=>'nomalteacher','default'=>0,'type'=>'int'),
				'uid'  =>  array('name'=>'uid','default'=>0,'type'=>'int'),
			),
			'addAction'   =>  array(
				'crid'  =>  array('name'=>'crid','default'=>0,'type'=>'int'),
				'mtitle' =>	array('name'=>'mtitle','require'=>TRUE,'type'=>'string'),
				'mdisplayorder' =>	array('name'=>'mdisplayorder','default'=>0,'type'=>'int'),
				'url' =>	array('name'=>'url','default'=>'','type'=>'string'),
				'status' =>	array('name'=>'status','default'=>0,'type'=>'int'),
				'icon' =>	array('name'=>'icon','default'=>'','type'=>'string'),
				'tmid' =>	array('name'=>'tmid','default'=>0,'type'=>'int'),
				'roomtype' =>	array('name'=>'roomtype','default'=>0,'type'=>'int'),
			),
			'editAction'   =>  array(
				'crid'  =>  array('name'=>'crid','default'=>0,'type'=>'int'),
				'mid' =>	array('name'=>'mid','require'=>TRUE,'type'=>'int'),
				'mtitle' =>	array('name'=>'mtitle','default'=>'','type'=>'string'),
				'oldmtitle' =>	array('name'=>'oldmtitle','default'=>'','type'=>'string'),
				'mdisplayorder' =>	array('name'=>'mdisplayorder','default'=>0,'type'=>'int'),
				'url' =>	array('name'=>'url','default'=>'','type'=>'string'),
				'status' =>	array('name'=>'status','default'=>0,'type'=>'int'),
				'icon' =>	array('name'=>'icon','default'=>'','type'=>'string'),
				'tmid' =>	array('name'=>'tmid','default'=>0,'type'=>'int'),
				'roomtype' =>	array('name'=>'roomtype','default'=>0,'type'=>'int'),
				'statusonly' => array('name'=>'statusonly','default'=>0,'type'=>'int'),
			),
			'detailAction'   =>  array(
				'crid'  =>  array('name'=>'crid','default'=>0,'type'=>'int'),
				'mid' =>	array('name'=>'mid','require'=>TRUE,'type'=>'int'),
				'roomtype'  =>  array('name'=>'roomtype','default'=>0,'type'=>'int'),
			),
			'delAction'   =>  array(
				'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
				'mid' =>	array('name'=>'mid','require'=>TRUE,'type'=>'int'),
			),
			'baseListAction'   =>  array(
				'roomtype'  =>  array('name'=>'roomtype','default'=>0,'type'=>'int'),
				'onlylevel1' =>  array('name'=>'onlylevel1','default'=>0,'type'=>'int'),
			),
            'getListByQueryAction'   =>  array(
				'roomtype'  =>  array('name'=>'roomtype','default'=>0,'type'=>'int'),
                'crid'  =>  array('name'=>'crid','type'=>'int'),
                'tmid' =>	array('name'=>'tmid','type'=>'int'),
                'q' =>	array('name'=>'q','type'=>'string'),
                'order' =>	array('name'=>'order','type'=>'string'),
                'pagesize' =>	array('name'=>'pagesize','default'=>10,'type'=>'int'),
                'page' =>	array('name'=>'page','default'=>1,'type'=>'int'),
			),
		);
	}

	/**
	 * 获取菜单列表
	 */
	public function listAction(){
		$param['crid'] = $this->crid;
		$crmodel = new ClassRoomModel();
		$crinfo = $crmodel->getRoomByCrid($this->crid);
		$param['roomtype'] = $crinfo['property'] == 3 && $crinfo['isschool'] ==7?1:0;
		$param['pagesize'] = $this->pagesize;
		$param['page'] = $this->page;
		$param['singlearray'] = 1;
		$param['nohide'] = $this->nohide;
		$param['withmodule'] = $this->withmodule;
		$param['onlylevel1'] = $this->onlylevel1;
		$param['nomalteacher'] = $this->nomalteacher;
		$param['uid'] = $this->uid;
		$menulist = $this->menumodel->getMenuList($param);
		return array('menulist'=>$menulist/*,'menucount'=>$menucount*/);
	}
	/**
	 * 获取菜单列表
	 */
	public function baseListAction(){
		$param['roomtype'] = $this->roomtype;
		$param['onlylevel1'] = $this->onlylevel1;
		$menulist = $this->menumodel->getBaseMenuList($param);
		return array('menulist'=>$menulist);
	}
	
	/*
	添加菜单
	*/
	public function addAction(){
		$param['crid'] = $this->crid;
		$param['mtitle'] = $this->mtitle;
		$param['mdisplayorder'] = $this->mdisplayorder;
		$param['url'] = $this->url;
		$param['status'] = $this->status;
		$param['icon'] = $this->icon;
		$param['tmid'] = $this->tmid;
		if(empty($this->crid)){//系统菜单,roomtype指定
			$param['roomtype'] = $this->roomtype;
			$param['issystem'] = 1;
		} else {//roomtype根据网校获取
			$crmodel = new ClassRoomModel();
			$crinfo = $crmodel->getRoomByCrid($this->crid);
			$param['roomtype'] = $crinfo['property'] == 3 && $crinfo['isschool'] ==7?1:0;
		}
		//设置从主数据库读取,防止主从服务器来不及同步的问题
		Ebh()->db->set_con(0);
		return $this->menumodel->addMenu($param);
	}
	/*
	编辑菜单
	*/
	public function editAction(){
		$param['crid'] = $this->crid;
		$param['mid'] = $this->mid;
		$param['mtitle'] = $this->mtitle;
		$param['oldmtitle'] = $this->oldmtitle;
		$param['mdisplayorder'] = $this->mdisplayorder;
		$param['url'] = $this->url;
		$param['status'] = $this->status;
		$param['icon'] = $this->icon;
		$param['tmid'] = $this->tmid;
		$param['statusonly'] = $this->statusonly;
		if(empty($this->crid)){//系统菜单,roomtype指定
			$param['roomtype'] = $this->roomtype;
			$param['issystem'] = 1;
		} else {//roomtype根据网校获取
			$crmodel = new ClassRoomModel();
			$crinfo = $crmodel->getRoomByCrid($this->crid);
			$param['roomtype'] = $crinfo['property'] == 3 && $crinfo['isschool'] ==7?1:0;
		}
		//设置从主数据库读取,防止主从服务器来不及同步的问题
		Ebh()->db->set_con(0);
		return $this->menumodel->editMenu($param);
	}
	
	/*
	菜单详情
	*/
	public function detailAction(){
		$param['crid'] = $this->crid;
		$param['mid'] = $this->mid;
		if(empty($this->crid)){//系统菜单,roomtype指定
			$param['roomtype'] = $this->roomtype;
		} else {//roomtype根据网校获取
			$crmodel = new ClassRoomModel();
			$crinfo = $crmodel->getRoomByCrid($this->crid);
			$param['roomtype'] = $crinfo['property'] == 3 && $crinfo['isschool'] ==7?1:0;
		}
		return $this->menumodel->menuDetail($param);
	}
	/*
	删除菜单
	*/
	public function delAction(){
		$param['crid'] = $this->crid;
		$param['mid'] = $this->mid;
		$crmodel = new ClassRoomModel();
		$crinfo = $crmodel->getRoomByCrid($this->crid);
		$param['roomtype'] = $crinfo['property'] == 3 && $crinfo['isschool'] ==7?1:0;
		return $this->menumodel->delMenu($param);
	}

    /**
     * 根据搜索条件获取一二级菜单列表
     */
    public function getListByQueryAction(){
        $param = array();
        $param['roomtype'] = $this->roomtype;   //网校类型,0教育版,1企业版
        $param['pagesize'] = $this->pagesize;
        $param['page'] = $this->page;
        if(isset($this->crid)){
            $param['crid'] = $this->crid;
        }
        if(isset($this->q)){
            $param['q'] = $this->q;
        }
        if(isset($this->tmid)){//父级菜单id
            $param['tmid'] = $this->tmid;
        }
        if(!empty($this->order)){
            $param['order'] = $this->order;
        }
        $menulist = $this->menumodel->getListByQuery($param);
        $count = $this->menumodel->getCountByQuery($param);
        return array('menulist'=>$menulist,'count'=>$count);
    }
}