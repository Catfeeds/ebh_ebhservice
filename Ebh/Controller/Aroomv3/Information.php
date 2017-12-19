<?php

/**
 * 信息管理
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/3/21
 * Time: 16:45
 */
class InformationController extends Controller
{
    public function __construct()
    {
        parent::init();
    }

    public function parameterRules() {
        return array(
            //发布公告
            'addAction' => array(
                'toid' => array(
                    'name' => 'toid',
                    'require' => true,
                    'type' => 'int'
                ),
                'message' => array(
                    'name' => 'message',
                    'require' => true,
                    'type' => 'string'
                ),
                'ip' => array(
                    'name' => 'ip',
                    'require' => true,
                    'type' => 'string'
                )
            ),
            //通知列表
            'noticesAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int'
                ),
                'pagenum' => array(
                    'name' => 'pagenum',
                    'type' => 'int'
                ),
                'sortmode' => array(
                    'name' => 'sortmode',
                    'type' => 'int'
                )
            ),
            //通知数
            'getNoticeCountAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                )
            ),
            //删除通知
            'removeNoticeAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'noticeid' => array(
                    'name' => 'noticeid',
                    'require' => true,
                    'type' => 'int'
                )
            ),
            //更新通知
            'updateNoticeAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'uid' => array(
                    'name' => 'uid',
                    'require' => true,
                    'type' => 'int'
                ),
                'noticeid' => array(
                    'name' => 'noticeid',
                    'require' => true,
                    'type' => 'int'
                ),
                'title' => array(
                    'name' => 'title',
                    'type' => 'string'
                ),
                'message' => array(
                    'name' => 'message',
                    'type' => 'string'
                ),
                'ntype' => array(
                    'name' => 'ntype',
                    'type' => 'int'
                ),
                'attid' => array(
                    'name' => 'attid',
                    'type' => 'int'
                ),
                'remind' => array(
                    'name' => 'remind',
                    'type' => 'int'
                ),
                'file_server' => array(
                    'name' => 'file_server',
                    'type' => 'string'
                ),
                'file_name' => array(
                    'name' => 'file_name',
                    'type' => 'string'
                ),
                'file_url' => array(
                    'name' => 'file_url',
                    'type' => 'string'
                ),
                'file_md5' => array(
                    'name' => 'file_md5',
                    'type' => 'string'
                ),
                'file_size' => array(
                    'name' => 'file_size',
                    'type' => 'int'
                ),
                'ip'=>array(
                    'name' => 'ip',
                    'require' => true,
                    'type' => 'string'
                ),
                'isreceipt' => array(
                    'name' => 'isreceipt',
                    'default' => 0,
                    'type' => 'int'
                ),
                'receipt' => array(
                    'name' => 'receipt',
                    'default' => '',
                    'type' => 'string'
                )
            ),
            //通知详情
            'getNoticeAction' => array(
                'noticeid' => array(
                    'name' => 'noticeid',
                    'type' => 'int',
                    'require' => true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            //发布通知
            'addNoticeAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'require' => true,
                    'type' => 'int'
                ),
                'uid' => array(
                    'name' => 'uid',
                    'require' => true,
                    'type' => 'int'
                ),
                'ip' => array(
                    'name' => 'ip',
                    'type' => 'string',
                    'require' => true
                ),
                'title' => array(
                    'name' => 'title',
                    'type' => 'string',
                    'require' => true
                ),
                'message' => array(
                    'name' => 'message',
                    'type' => 'string',
                    'require' => true
                ),
                'ntype' => array(
                    'name' => 'ntype',
                    'type' => 'int',
                    'require' => true
                ),
                'type' => array(
                    'name' => 'type',
                    'type' => 'int',
                    'require' => true
                ),
                'attid' => array(
                    'name' => 'attid',
                    'type' => 'int'
                ),
                'remind' => array(
                    'name' => 'remind',
                    'type' => 'int'
                ),
                'file_server' => array(
                    'name' => 'file_server',
                    'type' => 'string'
                ),
                'file_name' => array(
                    'name' => 'file_name',
                    'type' => 'string'
                ),
                'file_md5' => array(
                    'name' => 'file_md5',
                    'type' => 'string'
                ),
                'file_url' => array(
                    'name' => 'file_url',
                    'type' => 'string'
                ),
                'file_size' => array(
                    'name' => 'file_size',
                    'type' => 'int'
                ),
                'isreceipt' => array(
                    'name' => 'isreceipt',
                    'default' => 0,
                    'type' => 'int'
                ),
                'receipt' => array(
                    'name' => 'receipt',
                    'default' => '',
                    'type' => 'string'
                ),
            ),
            //当前公告
            'getSingleMessageAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            )
        );
    }

    /**
     * 发布公告
     */
    public function addAction() {
        $model = new SendinfoModel();
        return $model->addNotice($this->toid, $this->message,$this->ip);
    }

    /**
     * 通知列表
     * @return mixed
     */
    public function noticesAction() {
        $model = new NoticeModel();
        $limit = array();
        if ($this->pagesize !== NULL) {
            $limit['pagesize'] = $this->pagesize;
        }
        if ($this->pagenum !== NULL) {
            $limit['page'] = $this->pagenum;
        }
        if (empty($limit)) {
            $limit = 20;
        }
        return $model->getList($this->crid, $limit, intval($this->sortmode), false);
    }

    /**
     * 通知数
     * @return int
     */
    public function getNoticeCountAction() {
        $model = new NoticeModel();
        return $model->getCount($this->crid);
    }

    /**
     * 删除通知
     */
    public function removeNoticeAction() {
        $model = new NoticeModel();
        return $model->remove($this->noticeid, $this->crid);
    }

    /**
     * 通知详情
     * @return mixed
     */
    public function getNoticeAction() {
        $model = new NoticeModel();
        $notice = $model->getModel($this->noticeid, $this->crid);
        if ($notice['attid'] > 0) {
            $attrModel = new AttachmentModel();
            $attr = $attrModel->getModel($notice['attid']);
            if (!empty($attr)) {
                $notice['attr'] = $attr;
            }
        }
        return $notice;
    }

    /**
     * 更新通知
     * @return mixed
     */
    public function updateNoticeAction() {
        $model = new NoticeModel();
        $params = array();
        if ($this->title !== NULL) {
            $params['title'] = $this->title;
        }
        if ($this->message !== NULL) {
            $params['message'] = $this->message;
        }
        if ($this->ntype !== NULL) {
            $params['ntype'] = $this->ntype;
        }
        if ($this->uid !== NULL) {
            $params['uid'] = $this->uid;
        }  
        if ($this->ip !== NULL) {
            $params['ip'] = $this->ip;
        }
        if ($this->remind !== NULL) {
            $params['remind'] = $this->remind;
        }
        if (isset($this->isreceipt)) {
            $params['isreceipt'] = $this->isreceipt;
        }
        if (isset($this->receipt)) {
            $params['receipt'] = $this->receipt;
        }
        
        if ($this->attid !== NULL) {
            $params['attid'] = $this->attid;
            return $model->update($this->noticeid, $this->crid, $params);
        }
        $nameArr = explode('.', $this->file_name);
        if (count($nameArr) > 0) {
            $suffix = end($nameArr);
        } else {
            $suffix = '';
        }
        Ebh()->db->begin_trans();
        if (!empty($this->file_name) && !empty($this->file_size)){
            $attachmentModel = new AttachmentModel();
            $attid = $attachmentModel->add($this->uid, $this->crid, array(
                'checksum' => $this->file_md5,
                'title' => $this->file_name,
                'message' => $this->message,
                'source' => $this->file_server,
                'url' => $this->file_url,
                'filename' => $this->file_name,
                'size' => $this->file_size,
                'suffix' => $suffix
            ));
            $params['attid'] = $attid;
        }
        $ret = $model->update($this->noticeid, $this->crid, $params);
        if (Ebh()->db->trans_status() === FALSE) {
            Ebh()->db->rollback_trans();
        }
        Ebh()->db->commit_trans();
        return $ret;
    }

    /**
     * 发布通知
     * @return mixed
     */
    public function addNoticeAction() {
        $model = new NoticeModel();
        $params = array();
        $params['crid'] = $this->crid;
        $params['type'] = $this->type;
        $params['uid'] = $this->uid;
        $params['title'] = $this->title;
        $params['message'] = $this->message;
        $params['ntype'] = $this->ntype;
        $params['ip'] = $this->ip;
        $params['remind'] = $this->remind;
        $params['isreceipt'] = $this->isreceipt;
        if (!empty($this->receipt)) {
            $params['receipt'] = $this->receipt;
        }
        if ($this->attid !== NULL) {
            $params['attid'] = $this->attid;
            return $model->add($params);
        }
        $nameArr = explode('.', $this->file_name);
        if (count($nameArr) > 0) {
            $suffix = end($nameArr);
        } else {
            $suffix = '';
        }
        Ebh()->db->begin_trans();
        if (!empty($this->file_name) && !empty($this->file_size)){
            $attachmentModel = new AttachmentModel();
            $attid = $attachmentModel->add($this->uid, $this->crid, array(
                'checksum' => $this->file_md5,
                'title' => $this->file_name,
                'message' => $this->message,
                'source' => $this->file_server,
                'url' => $this->file_url,
                'filename' => $this->file_name,
                'size' => $this->file_size,
                'suffix' => $suffix
            ));
            $params['attid'] = $attid;
        }
       
        $ret = $model->add($params);
        if (Ebh()->db->trans_status() === FALSE) {
            Ebh()->db->rollback_trans();
        }
        Ebh()->db->commit_trans();
        return $ret;
    }

    /**
     * 获取当前公告
     * @return string
     */
    public function getSingleMessageAction() {
        $model = new SendinfoModel();
        return $model->getSingleModel($this->crid);
    }
}