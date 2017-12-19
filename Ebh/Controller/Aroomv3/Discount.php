<?php

/**
 * 折扣
 * Auther: songpeng
 * Date: 2017/5/13
 * Time: 09:16
 */
class DiscountController extends Controller {
    private $model = null;
    public function __construct(){
        parent::init();
        $this->model = new SystemSettingModel();
    }
    
    public function parameterRules() { 
        return array(  
            //网校开关是否开启
            'checkSwitchAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'    
                ),                  
            ),
            //设置折扣开、关
            'changeSwitchAction'=> array(
                'flag' => array(
                    'name' => 'flag',
                    'require' => true,
                    'type' => 'int'
                ),
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
            ),
            
            //添加一条折扣记录
            'addAction' => array(
                'num' => array(
                    'name' => 'num',
                    'require' => true,
                    'type' => 'int'
                ),
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'discount' => array(
                    'name' => 'discount',
                    'require' => true
                ),
                'sub' => array(
                    'name' => 'sub',
                    'require' => true,
                    'type' => 'int'
                ),
            ),
            'delAction' => array(
                'num' => array(
                    'name' => 'num',
                    'require' => true,
                    'type' => 'int'
                ),
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
            ),
            'updateAction' => array(
                'num' => array(
                    'name' => 'num',
                    'require' => true,
                    'type' => 'int'
                ),
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'discount' => array(
                    'name' => 'discount',
                    'require' => true
                ),
            ),
            'getAction' => array(
                'num' => array(
                    'name' => 'num',
                    'require' => true,
                    'type' => 'int'
                ),
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
            ),          
            'getlistAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
            ),
        );
    }
    /**
     * 检查网校开关是否开启 
     */
    public function checkSwitchAction(){
        $setting = $this->model->getModel($this->crid);
        if(!empty($setting) && ($setting['iscollect']==1)){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * 设置折扣开、关
     */
    public function changeSwitchAction(){
        $setting = $this->model->getModel($this->crid);
        if(!empty($setting)){
            $ck = $this->model->update($this->crid, array('iscollect'=> intval($this->flag)));
        }else{
            $ck = $this->model->add($this->crid, array('iscollect'=> intval($this->flag)));
        }
        return $ck;
    }

    /**
     * 折扣添加
     */
    public function addAction() {
        $check = $this->model->getModel($this->crid);//检查是否已存在记录(json)         
        if(!empty($check)){
            $checkarr = json_decode($check['discounts']); //将数据返回成数组
            $count = count($checkarr);    
            array_multisort($checkarr,SORT_ASC);  //排序（展示页已排序，这里也要排序以对应正确的下标）
            $flag = 1;
            $i = 0;
            foreach($checkarr as $arr){                  
                if($arr[0] == $this->num && $arr[1] == $this->discount){//数量、折扣相等，返回false
                    return false;
                }
                if($arr[0] == $this->num && $arr[1] !== $this->discount && $i == $this->sub){//数量相等，折扣不等，下标相等，更新数组
                    $checkarr[$i][1] = $this->discount;
                    $flag = 0;
                    break;
                }
                if($arr[0] !== $this->num  && $i == $this->sub){//数量不等，下标相等，更新数组
                    $checkarr[$i][0] = $this->num;
                    $checkarr[$i][1] = $this->discount;
                    $flag = 0;
                    break;
                }
                $i++;
            }
            if($flag){
                $checkarr[$count] = array($this->num,$this->discount);//数量不等，下标不等，追加数组
            }       
            $param = json_encode($checkarr);
            return $this->model->update($this->crid,array('discounts'=>$param));//更新
        }else{
            $arr[] = array($this->num,$this->discount);
            $param = json_encode($arr);
            return $this->model->add($this->crid,array('discounts'=>$param));//添加
        }
    }
    /**
     * 折扣删除
     *   
     */
    public function delAction() {
        $check = $this->model->getModel($this->crid);//检查是否已存在记录(json)
        if(!empty($check['discounts'])){ 
            $checkarr = json_decode($check['discounts']);
            $i = 0;
            foreach($checkarr as $arr){
                if($arr[0] == $this->num){//数量相等，删除数组元素
                    unset($checkarr[$i]);
                    $checkarr = array_values($checkarr);
                }
                $i++;
            }
            $param = json_encode($checkarr);
            return $this->model->update($this->crid,array('discounts'=>$param));//更新
        }else{
            return false;
        }
    }
    /**
     * 获取折扣列表
     *   
     */
    public function getlistAction() { 
        $retarr = array();
        $ret = $this->model->getModel($this->crid);//检查是否已存在记录(json)
         if(!empty($ret['discounts'])){
             $retarr = json_decode($ret['discounts']);
             array_multisort($retarr,SORT_ASC);
         }
         return $retarr;
    }

    

}