<?php
/**
 * 客服身份审核
 * Author: ycq
 */
class IdentityController extends Controller{

    public function __construct(){
        parent::init();
    }
    public function parameterRules() {
        return array(
            //申请列表
            'indexAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'default' => 0
                ),
                'status' => array(
                    'name' => 'status',
                    'type' => 'int'
                ),
                'start' => array(
                    'name' => 'start',
                    'type' => 'int'
                ),
                'end' => array(
                    'name' => 'end',
                    'type' => 'int'
                ),
                'aids' => array(
                    'name' => 'aids',
                    'type' => 'array'
                ),
                'page' => array(
                    'name' => 'end',
                    'type' => 'int',
                    'default' => 1
                ),
                'pagesize' => array(
                    'name' => 'end',
                    'type' => 'int',
                    'default' => 20
                )
            ),
            //审核
            'auditAction' => array(
                'aid' => array(
                    'name' => 'aid',
                    'type' => 'int',
                    'require' => true
                ),
                'status' => array(
                    'name' => 'status',
                    'type' => 'int',
                    'require' => true
                ),
                'remark' => array(
                    'name' => 'remark',
                    'type' => 'string'
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'require' => true
                ),
                'ip' => array(
                    'name' => 'ip',
                    'type' => 'string',
                    'default' => ''
                )
            ),
            //申请详情
            'infoAction' => array(
                'aid' => array(
                    'name' => 'aid',
                    'type' => 'int',
                    'require' => true
                )
            )
        );
    }

    /**
     * 申请列表
     */
    public function indexAction(){
        $model = new JsauthModel();
        $params = array();
        if ($this->crid > 0) {
            $params['crid'] = $this->crid;
        }
        if ($this->status !== null) {
            $params['status'] = $this->status;
        }
        if ($this->start !== null) {
            $params['start'] = $this->start;
        }
        if ($this->end !== null) {
            $params['end'] = $this->end;
        }
        if ($this->aids !== null) {
            if (empty($this->aids)) {
                return array(
                    'count' => 0,
                    'list' => array()
                );
            }
            $params['aids'] = $this->aids;
        }
        $count = $model->getCount($params);
        $list = $count > 0 ? $model->getList($params, array('page' => $this->page, 'pagesize' => $this->pagesize)) : array();
        return array(
            'count' => $count,
            'list' => $list
        );
    }

    /**
     * 审核
     * @return mixed 成功ID
     */
    public function auditAction() {
        $model = new JsauthModel();
        return $model->audit($this->aid, $this->status, $this->remark, $this->uid, $this->ip);
    }

    /**
     * 申请详情
     * @return mixed
     */
    public function infoAction() {
        $model = new JsauthModel();
        return $model->getInfo($this->aid);
    }
}