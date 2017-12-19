<?php
/**
 *兑换码控制器
 * ebhservice.
 * Author: tyt
 * Email: 345468755@qq.com
 */
class RedeemController extends Controller{
    public $redeem;

    public function init(){
        parent::init();
        $this->redeem = new RedeemModel();
    }
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','default' => 0,'type'=>'int'),
                'cardid'  =>  array('name'=>'cardid','default' => 0,'type'=>'int'),
                'status'  =>  array('name'=>'status','type'=>'int'),
                'type'  =>  array('name'=>'type','default' => 1,'type'=>'int'),
                'redeemnumber'  =>  array('name'=>'redeemnumber','default'=>''),
                'name'  =>  array('name'=>'name','default'=>''),
                'foldername'  =>  array('name'=>'foldername','default'=>''),
                'q'  =>  array('name'=>'q','default'=>''),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
                'lot'  =>  array('name'=>'lot','type'=>'int','default'=>0),
                'page'  =>  array('name'=>'page','type'=>'int','default'=>0)
            ),
            'updateAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'lotid'  =>  array('name'=>'lotid','require'=>true,'type'=>'int'),
                'name'  =>  array('name'=>'name','default'=>''),
                'effecttime'  =>  array('name'=>'effecttime','type'=>'int','default'=>0)
            ),
            'refundAction'   =>  array(
                'lotid'  =>  array('name'=>'lotid','type'=>'int','default'=>0),//批次id
                'cardid'  =>  array('name'=>'cardid','type'=>'int','default'=>0),//单个卡片退款
                'cardidstr'  =>  array('name'=>'cardidstr','default'=>'')//单个卡片退款
            ),
            'addAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'name'  =>  array('name'=>'name','require'=>true),
                'effecttime' => array('name' => 'effecttime','type' => 'int','default'=>0),
                'docards'  =>  array('name'=>'docards','type'=>'int','default'=>0),
                'price'  =>  array('name'=>'price','require'=>true),
                'folderid'  =>  array('name'=>'folderid','require'=>true,'type' => 'int'),
                'itemid'  =>  array('name'=>'itemid','default' => 0,'type'=>'int'),
                'number'  =>  array('name'=>'number','require'=>true,'type' => 'int')
            )
        );
    }

    /**
     * 添加
     * @return mixed
     */
    public function addAction(){
        $parameters = array();
        $parameters['effecttime'] = $this->effecttime;
        $parameters['crid'] = $this->crid;
        $parameters['folderid'] = $this->folderid;
        $parameters['itemid'] = $this->itemid;
        $parameters['price'] = $this->price;
        $parameters['docards'] = $this->docards;//是否直接生成兑换码
        $parameters['name'] = $this->name;
        $parameters['number'] = $this->number;
        return $this->redeem->add($parameters);
    }

    /**
     * 退换兑换接口
     * @return mixed
     */
    public function refundAction(){
        $parameters = array();
        $parameters['cardidstr'] = $this->cardidstr;
        $parameters['cardid'] = $this->cardid;
        $parameters['lotid'] = $this->lotid;
        log_message(1111);
        return $this->redeem->refund($parameters);
    }

    /**
     * 获取总的原创文章列表
     * @return array
     */
    public function listAction(){
        $filterParams = array();
        $filterParams['crid'] = $this->crid;
        if ($this->folderid) {
            $filterParams['folderid'] = $this->folderid;
        }
         if ($this->cardid) {
            $filterParams['cardid'] = $this->cardid;
        }
        if ($this->lot) {
            $filterParams['lot'] = $this->lot;
        }
        if (isset($this->status)) {
            $filterParams['status'] = $this->status;
        }
        if (!empty($this->q)) {
            $filterParams['q'] = $this->q;
        }
        if (!empty($this->foldername)) {
            $filterParams['foldername'] = $this->foldername;
        }
        if (!empty($this->name)) {
            $filterParams['name'] = $this->name;
        }
        if (!empty($this->redeemnumber)) {
            $filterParams['redeemnumber'] = $this->redeemnumber;
        }
        $res = $this->redeem->getListAndTotalPage($filterParams, array(
            'pagesize' => $this->pagesize,
            'page' => $this->page
        ), false);
        if (!$this->lot) {
            $this->bulidUserInfo($res);//添加用户信息
        }
        return $res;
    }

    /**
     * 更新资讯
     */
    public function updateAction() {
        $params = array();
        if ($this->effecttime) {
            $params['effecttime'] = $this->effecttime;
        }
        if ($this->name) {
            $params['name'] = $this->name;
        }
        if (empty($params)) {
            return 0;
        }
        return $this->redeem->update($this->lotid, $this->crid, $params);
    }

    /**
     *构建用户信息的函数，包括班级
     *@param $result,返回list包括uid
     */
    public function bulidUserInfo(&$result,$defaultKey='uid') {
        if (!empty($result['list'])) {
            $uidsArr = array();
            foreach ($result['list'] as $value) {
                $uidsArr[] = $value[$defaultKey];
            }
            //获取用户的信息追加
            $uids = implode(',', array_unique($uidsArr));
            $userModel = new UserModel();
            $classesModel = new ClassesModel();
            $getCity = 1;
            $userinfos = $userModel->getUserInfoByUid($uids,$getCity);
            $classinfos = $classesModel->getClassInfoByCrid($this->crid,array_unique($uidsArr));
            if (!empty($classinfos)) {
                foreach ($classinfos as $cvalue) {
                    $user_classes[$cvalue['uid']] = $cvalue;
                }
            }
            if (!empty($userinfos)) {
                foreach ($userinfos as $uvalue) {
                    $user_infos[$uvalue['uid']] = $uvalue;
                }
                foreach ($result['list'] as &$rvalue) {
                    if (isset($user_infos[$rvalue[$defaultKey]])) {//把用户信息追加
                        $rvalue['cityname'] = $user_infos[$rvalue[$defaultKey]]['cityname'];
                        $rvalue['username'] = $user_infos[$rvalue[$defaultKey]]['username'];
                        $rvalue['realname'] = $user_infos[$rvalue[$defaultKey]]['realname'];
                        $rvalue['sex'] = $user_infos[$rvalue[$defaultKey]]['sex'];
                        $rvalue['face'] = $user_infos[$rvalue[$defaultKey]]['face'];
                        $rvalue['class'] = empty($user_classes[$rvalue[$defaultKey]]['classname'])?'暂无班级':$user_classes[$rvalue[$defaultKey]]['classname'];
                    }
                }
            }
        }
        return $result;
    }
}