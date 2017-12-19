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

}