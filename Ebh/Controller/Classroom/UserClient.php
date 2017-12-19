<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 11:46
 */
class UserClientController extends Controller{
    public function parameterRules(){
        return array(
            'addAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'ismobile'  =>  array('name'=>'ismobile','type'=>'int','default'=>0),
                'system'  =>  array('name'=>'system','require'=>true),
                'browser'  =>  array('name'=>'browser','require'=>true),
                'broversion'  =>  array('name'=>'broversion','require'=>true),
                'screen'  =>  array('name'=>'screen','require'=>true),
                'ip'  =>  array('name'=>'ip','require'=>true),
                'vendor'  =>  array('name'=>'vendor','require'=>true),
                'isext'  =>  array('name'=>'isext','type'=>'int','default'=>0),

            ),
            'getClientsByUidAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
            ),
        );
    }

    /**
     * 添加设备信息
     * @return bool
     */
    public function addAction(){
        $param['uid'] = $this->uid;
        $param['crid'] = $this->crid;
        $param['ismobile'] = $this->ismobile;
        $param['system'] = $this->system;
        $param['browser'] = $this->browser;
        $param['broversion'] = $this->broversion;
        $param['screen'] = $this->screen;
        $param['ip'] = $this->ip;
        $param['isext'] = $this->isext;
        $param['vendor'] = $this->vendor;
        $curtime = microtime(TRUE);
        $param['dateline'] = intval($curtime);
        $param['lasttime'] = $curtime;
        $userClientModel = new UserClientModel();
        $id = $userClientModel->add($param);
        if($id === false){
            return $id;
        }
        return false;
    }
    /**
     * 获取客户端列表
     * @return mixedton
     */
    public function getClientsByUidAction(){
        $userClientModel = new UserClientModel();
        $client = $userClientModel->getClientsByUid($this->uid,$this->crid);
        return $client;
    }
}