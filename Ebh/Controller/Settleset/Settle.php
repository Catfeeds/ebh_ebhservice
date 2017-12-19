<?php
/**
 * 网校资金冻结配置
 * Author: ycq
 */
class SettleController extends Controller{

    public function __construct(){
        parent::init();
    }
    public function parameterRules() {
        return array(
            //数据列表
            'indexAction' => array(
                'step' => array(
                    'name' => 'step',
                    'type' => 'int',
                    'require' => true
                ),
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
                'paystatus' => array(
                    'name' => 'paystatus',
                    'type' => 'int'
                ),
                'type' => array(
                    'name' => 'type',
                    'type' => 'int'
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
                'jid' => array(
                    'name' => 'jid',
                    'type' => 'int',
                    'require' => true
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'require' => true
                ),
                'step' => array(
                    'name' => 'step',
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
                    'type' => 'string',
                    'default' => ''
                ),
                'ip' => array(
                    'name' => 'ip',
                    'type' => 'string',
                    'require' => true
                )
            ),
            //短信参数
            'auditMsgForSmsAction' => array(
                'ids' => array(
                    'name' => 'ids',
                    'type' => 'array',
                    'require' => true
                )
            ),
            //支付详情
            'infoAction' => array(
                'jid' => array(
                    'name' => 'jid',
                    'type' => 'int',
                    'require' => true
                ),
                'step' => array(
                    'name' => 'step',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //支付
            'payAction' => array(
                'jid' => array(
                    'name' => 'jid',
                    'type' => 'int',
                    'require' => true
                ),
                'paystatus' => array(
                    'name' => 'paystatus',
                    'type' => 'int',
                    'require' => true
                ),
                'uid' => array(
                    'name' => 'uid',
                    'type' => 'int',
                    'require' => true
                ),
                'ip' => array(
                    'name' => 'ip',
                    'type' => 'string',
                    'require' => true
                )
            ),
			'updatePayStatusAction' => array(
				'jids' => array('name'=>'jids','default'=>'','type'=>'string'),
				'paystatus' => array('name'=>'paystatus','default'=>0,'type'=>'int'),
				'jsuid' => array('name'=>'jsuid','default'=>0,'type'=>'int'),
			),
            //付款备注
            'payRemarkAction' => array(
                'jid' => array(
                    'name' => 'jid',
                    'type' => 'int',
                    'require' => true
                ),
                'jsnotes' => array(
                    'name' => 'jsnotes',
                    'type' => 'string'
                )
            ),
			
			'processAction' => array(
				'jids' => array('name'=>'jids','require'=>true,'type'=>'string'),
			)
        );
    }

    /**
     * 数据列表
     */
    public function indexAction(){
        $model = new JsapplyModel();
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
        if ($this->step == 1) {
            //公司审核
            $count = $model->getFirstCount($params);
            $list = $model->getFirstList($params, array('page' => $this->page, 'pagesize' => $this->pagesize));
            return array(
                'count' => $count,
                'list' => $list
            );
        }
        if ($this->step == 3 && $this->paystatus !== null) {
            $params['paystatus'] =  $this->paystatus;
        }
        if ($this->step == 3 && $this->type !== null) {
            $params['type'] =  $this->type;
        }
        if ($this->step == 2 || $this->step == 3) {
            //账务审核,批量付款
            $count = $model->getSecondCount($params);
            $list = $model->getSecondList($params, array('page' => $this->page, 'pagesize' => $this->pagesize));
            return array(
                'count' => $count,
                'list' => $list
            );
        }
        return array();
    }

    /**
     * 审核
     */
    public function auditAction() {
        $model = new JsapplyModel();
        return $model->audit($this->jid, $this->uid, $this->step, $this->status, $this->remark, $this->ip);
    }

    /**
     * 短信参数
     * @return array
     */
    public function auditMsgForSmsAction() {
        $model = new JsapplyModel();
        $ids = array_map('intval', $this->ids);
        return $model->auditMsgForSms($ids);
    }

    /**
     * 详情
     */
    public function infoAction() {
        $model = new JsapplyModel();
        $step = $this->step == 2 ? 2 : 1;
        return $model->getInfo($this->jid, $step);
    }

    /**
     * 支付
     */
    public function payAction() {
        $model = new JsapplyModel();
        return $model->pay($this->jid, $this->paystatus, $this->uid, $this->ip);
    }
	
	/*
	结算批量付款支付状态更新
	*/
	public function updatePayStatusAction(){
		Ebh()->db->set_con(0);
		$pomodel = new JsapplyModel();
		return $pomodel->updatePayStatus(array('jids'=>$this->jids,'paystatus'=>$this->paystatus,'jsuid'=>$this->jsuid));
	}
    /**
     * 编辑支付备注
     * @return mixed
     */
    public function payRemarkAction() {
        $model = new JsapplyModel();
        return $model->editPayRemark($this->jid, $this->jsnotes);
    }
	
	/*
	已处理状态
	*/
	public function processAction(){
		$pomodel = new JsapplyModel();
		return $pomodel->process(array('jids'=>$this->jids));
	}
}