<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:24
 */
class ItemController extends Controller{
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'itemid'  =>  array('name'=>'itemid','require'=>true,'type'=>'int','min'=>1),
                'sid'  =>  array('name'=>'sid','require'=>false,'type'=>'int','default'=>array()),
            ),
			'openCountAction' => array(
				'itemid' =>array('name'=>'itemid','type'=>'string','default'=>''),
				'bid' =>array('name'=>'bid','type'=>'string','default'=>''),
				'crid' =>array('name'=>'crid','type'=>'int','require'=>true),
				'uid' =>array('name'=>'uid','type'=>'int','default'=>0),
				'islist' =>array('name'=>'islist','type'=>'boolean','default'=>false),
			),
			
			'openListAction'=>array(
				'itemid' =>array('name'=>'itemid','type'=>'int','default'=>0),
				'bid' =>array('name'=>'bid','type'=>'int','default'=>0),
				'crid' =>array('name'=>'crid','type'=>'int','require'=>true),
			),
        );
    }

    /**
     * 获取产品列表
     * @return array
     */
    public function listAction(){
        $payItemModel = new PayitemModel();
        if($this->sid > 0){
            $parameters['sid'] = $this->sid;
        }else{
            $parameters['itemid'] = $this->itemid;
        }
        $parameters['limit'] = '0,100';
        $list = $payItemModel->getItemList($parameters);


        return returnData(1,'',$list);

    }
	
	/*
	开通人数
	*/
	public function openCountAction(){
		if(empty($this->itemid) && empty($this->bid)){
			return array('opencount'=>0,'selfcount'=>0);
		}
		$idtype = empty($this->itemid)?'bid':'itemid';
		$id = $this->$idtype;
		$ordermodel = new PayorderModel();
		return $ordermodel->getOpenCount(array('crid'=>$this->crid,$idtype=>$id,'uid'=>$this->uid,'islist'=>$this->islist));
	}
	
	/*
	课程(包)开通人员列表
	*/
	public function openListAction(){
		if(empty($this->itemid) && empty($this->bid)){
			return array('list'=>array(),'name'=>'');
		}
		$idtype = empty($this->itemid)?'bid':'itemid';
		$id = $this->$idtype;
		if($idtype == 'itemid'){//课程
			$pimodel = new PayitemModel();
			$iteminfo = $pimodel->geSimpletItemByItemid($id);
		} else {//课程包
			$bundlemodel = new BundleModel();
			$iteminfo = $bundlemodel->getSimpleByBid($id);
		}
		if(empty($iteminfo)){
			return array('list'=>array(),'name'=>'');
		} elseif($idtype == 'itemid'){
			$iteminfo['name'] = $iteminfo['iname'];
		}
		$limit = 0;
		if(!empty($iteminfo['islimit']) && $iteminfo['limitnum']>0){
			$limit = $iteminfo['limitnum'];
		}
		$ordermodel = new PayorderModel();
		$openlist = $ordermodel->getOpenList(array('crid'=>$this->crid,$idtype=>$id,'limit'=>$limit));
		if(!empty($openlist)){
			$uids = array_column($openlist,'uid');
			$uids = implode(',',$uids);
			$usermodel = new UserModel();
			$userlist = $usermodel->getUserByUids($uids);
			//插入用户信息
			foreach($openlist as &$user){
				$uid = $user['uid'];
				$user['username'] = $userlist[$uid]['username'];
				$user['realname'] = $userlist[$uid]['realname'];
				$user['sex'] = $userlist[$uid]['sex'];
				$user['face'] = $userlist[$uid]['face'];
			}
		}
		return array('list'=>$openlist,'name'=>$iteminfo['name']);
	}
}