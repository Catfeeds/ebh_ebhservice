<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:17
 */
class SpreadController extends Controller{
    public function parameterRules(){
        return array(
            'detailAction'   =>  array(
                'itemid'  =>  array('name'=>'itemid','require'=>true,'type'=>'int','min'=>1),
                'uid'   =>   array('name'=>'uid','require'=>true,'type'=>'int','min'=>0),
            )
        );
    }
    public function detailAction(){
        $spreadModel = new SpreadModel();
        $result = $spreadModel->detail($this->itemid,$this->uid);

        if(!$result){
            return returnData(0,'信息不存在');
        }
        return returnData(1,'',$result);
    }
}