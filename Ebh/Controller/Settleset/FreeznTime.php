<?php
/**
 * 网校资金冻结配置
 * Author: ycq
 */
class FreezntimeController extends Controller{

    public function __construct(){
        parent::init();
    }
    public function parameterRules() {
        return array(
            //获取网校资金冻结配置
            'getFreeznTimeAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //设置网校资金冻结
            'setFreeznTimeAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'day' => array(
                    'name' => 'day',
                    'type' => 'int',
                    'require' => true
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //检查资金冻结时间是否可编辑
            'checkEditableAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            )
        );
    }

    /**
     * 获取网校资金冻结配置
     * @return int 天数
     */
    public function getFreeznTimeAction(){
        $model = new FreezntimeModel();
        return $model->getFreeznDay($this->crid, 15);
    }

    /**
     * 设置网校资金冻结
     * @return mixed 成功ID
     */
    public function setFreeznTimeAction() {
        $model = new FreezntimeModel();
        $jsapplyModel = new JsapplyModel();
        $exists = $jsapplyModel->checkExists($this->crid);
        if ($exists) {
            return false;
        }
        return $model->edit($this->day, $this->crid, $this->uid);
    }

    /**
     * 检查资金冻结时间是否可编辑
     * @return bool
     */
    public function checkEditableAction() {
        $jsapplyModel = new JsapplyModel();
        return !$jsapplyModel->checkExists($this->crid);
    }
}