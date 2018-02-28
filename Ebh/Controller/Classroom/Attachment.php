<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:31
 */
class AttachmentController extends Controller{
    public function parameterRules(){
        return array(
            'listByIdsAction' =>  array(
                'attids'  =>  array('name'=>'attids','require'=>true,'type'=>'array'),
            ),

        );
    }

    /**
     * 根据附件ID数组获取附件列表
     * @return mixed
     */
    public function listByIdsAction(){
        $attachmodel = new AttachmentModel();
        return $attachmodel->getAttachByAttids($this->attids);
    }
}