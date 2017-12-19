<?php
/**
 * 学生端头部链接.
 * Author: ycq
 */
class ClientLinkController extends Controller{
    public function parameterRules(){
        return array(
            'indexAction'   =>  array(
                'crid'  =>  array('name' => 'crid', 'require' => true, 'type' => 'int', 'min' => 1),
                'foradmin' => array('name' => 'foradmin', 'default' => 0, 'type' => 'int', 'min' => 0, 'max' => 1)
            ),
            'editAction' => array(
                'lid'  =>  array('name' => 'lid', 'type' => 'int', 'default' => 0),
                'crid'  =>  array('name' => 'crid', 'require' => true, 'type' => 'int', 'min' => 1),
                'name' => array('name' => 'name', 'type' => 'string'),
                'href' => array('name' => 'href', 'type' => 'string'),
                'label' => array('name' => 'label', 'type' => 'string'),
                'target' => array('name' => 'target', 'type' => 'int', 'max' => 2, 'min' => 0),
                'enabled' => array('name' => 'enabled', 'type' => 'int'),
                'zindex' => array('name' => 'zindex', 'type' => 'int', 'min' => 0),
                'category' => array('name' => 'category','type' => 'int')
            ),
            'deleteAction' => array(
                'lid'  =>  array('name' => 'lid', 'require' => true, 'type' => 'int', 'min' => 1),
                'crid'  =>  array('name' => 'crid', 'require' => true, 'type' => 'int', 'min' => 1)
            ),
            'sortAction' => array(
                'lid'  =>  array('name' => 'lid', 'require' => true, 'type' => 'int', 'min' => 1),
                'crid'  =>  array('name' => 'crid', 'require' => true, 'type' => 'int', 'min' => 1),
                'forword' => array('name' => 'forword', 'require' => true, 'type' => 'int', 'max' => 1, 'min' => 0)
            ),
            'roomSettingAction' => array(
                'crid'  =>  array('name' => 'crid', 'require' => true, 'type' => 'int', 'min' => 1)
            )
        );
    }

    /**
     * 链接列表
     * @return array
     */
    public function indexAction() {
        $model = new StudentLinkModel();
        return $model->getLinks($this->crid, $this->foradmin);
    }

    /**
     * 编辑链接
     * @return mixed
     */
    public function editAction() {
        $model = new StudentLinkModel();
        $params = array();
        if ($this->name !== null) {
            $params['name'] = trim($this->name);
        }
        if ($this->href !== null) {
            $params['href'] = trim($this->href);
        }
        if ($this->target !== null) {
            $params['target'] = $this->target;
        }
        if ($this->label !== null) {
            $params['label'] = $this->label;
        }
        if ($this->zindex !== null) {
            $params['zindex'] = $this->zindex;
        }
        if ($this->enabled !== null) {
            $params['enabled'] = $this->enabled;
        }
        if ($this->category !== null) {
            $params['category'] = $this->category;
        }
        if ($this->lid > 0) {
            return $model->update($this->lid, $params, $this->crid);
        }
        return $model->add($params, $this->crid);
    }

    /**
     * 移动调整链接的优先级
     * @return bool
     */
    public function sortAction() {
        $model = new StudentLinkModel();
        return $model->sort($this->lid, $this->forword, $this->crid);
    }

    /**
     * 删除链接
     * @return mixed
     */
    public function deleteAction() {
        $model = new StudentLinkModel();
        return $model->delete($this->lid, $this->crid);
    }

    /**
     * 获取站点配置
     * @return array
     */
    public function roomSettingAction() {
        $systemSettingModel = new SystemSettingModel();
        $setting = $systemSettingModel->getSetting($this->crid);
        return $setting;
    }
}