<?php

/**
 * 网校装扮
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/12/4
 * Time: 14:28
 */
class DesignController extends Controller
{
    public function __construct()
    {
        parent::init();
    }
    public function parameterRules()
    {
        return array(
            //读取网校装扮列表
            'getDesignListAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'roomtype' => array(
                    'name' => 'roomtype',
                    'type' => 'string',
                    'require' => true
                )
            ),
            //删除装扮
            'deleteDesignAction' => array(
                'did' => array(
                    'name' => 'did',
                    'type' => 'int',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'roomtype' => array(
                    'name' => 'roomtype',
                    'type' => 'string',
                    'require' => true
                )
            ),
            //编辑装扮
            'editDesignAction' => array(
                'did' => array(
                    'name' => 'did',
                    'type' => 'int',
                    'default' => 0
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'roomtype' => array(
                    'name' => 'roomtype',
                    'type' => 'string',
                    'require' => true
                ),
                'clientType' => array(
                    'name' => 'clientType',
                    'type' => 'int',
                    'require' => true
                ),
                'name' => array(
                    'name' => 'name',
                    'type' => 'string'
                ),
                'remark' => array(
                    'name' => 'remark',
                    'type' => 'string'
                )
            ),
            //选择装扮
            'chooseDesignAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'roomtype' => array(
                    'name' => 'roomtype',
                    'type' => 'string',
                    'require' => true
                ),
                'did' => array(
                    'name' => 'did',
                    'type' => 'int',
                    'require' => true
                ),
                'clientType' => array(
                    'name' => 'clientType',
                    'type' => 'int',
                    'require' => true,
                    'min' => 0,
                    'max' => 1
                ),
                'checked' => array(
                    'name' => 'checked',
                    'type' => 'boolean',
                    'default' => true
                )
            ),
            //根据did获取网校装扮列表
            'getDesignByDidAction' => array(
                'did' => array(
                    'name' => 'did',
                    'type' => 'int',
                    'require' => true
                )
            )
        );
    }

    /**
     * 读取网校装扮列表
     * @return array
     */
    public function getDesignListAction() {
        $model = new DesignModel();
        $list = $model->getDesignList($this->crid, $this->roomtype);
        array_unshift($list, array(
            'did' => 0,
            'name' => '老版首页装扮',
            'remark' => '老版Plate首页装扮',
            'client_type' => 0
        ));

        array_walk($list, function(&$design) {
            if (trim($design['name']) == '') {
                $design['name'] = '未命名';
            }
        });
        $ret['pc'] = array_filter($list, function($design) {
           return empty($design['client_type']);
        });
        $ret['mobile'] = array_values(array_diff_key($list, $ret['pc']));
        $ret['pc'] = array_values($ret['pc']);
        $checkeds = array_filter($ret['pc'], function($design) {
            return !empty($design['checked']);
        });
        $key = 0;
        if (!empty($checkeds)) {
            $key = key($checkeds);
        }
        $ret['pc'][$key]['checked'] = 1;

        return $ret;
    }

    /**
     * 删除装扮
     * @return mixed
     */
    public function deleteDesignAction() {
        $model = new DesignModel();
        return $model->deleteDesign($this->did, $this->crid, $this->roomtype);
    }

    /**
     * 编辑装扮
     * @return bool|unknown
     */
    public function editDesignAction() {
        $model = new DesignModel();
        $params = array(
            'name' => trim($this->name),
            'remark' => trim($this->remark)
        );
        if ($this->did > 0) {
            $params['did'] = $this->did;
            return $model->editDesign($params, $this->crid, $this->clientType);
        }
        $params['crid'] = $this->crid;
        $params['roomtype'] = $this->roomtype;
        $params['client_type'] = $this->clientType;

        return $model->addDesign($params);
    }

    /**
     * 选择装扮
     * @return bool
     */
    public function chooseDesignAction() {
        $model = new DesignModel();
        return $model->chooseDesign($this->did, $this->crid, $this->roomtype, $this->clientType, $this->checked);
    }

    /**
     * 根据did获取网校装扮列表
     * @return mixed
     */
    public function getDesignByDidAction() {
        $model = new DesignModel();
        return $model->getDesignByDid(array('did'=>$this->did));
    }
}