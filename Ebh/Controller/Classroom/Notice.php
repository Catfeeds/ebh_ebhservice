<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 13:54
 */
class NoticeController extends Controller{

    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
            ),
            'detailAction'   =>  array(
                'noticeid'  =>  array('name'=>'noticeid','require'=>true,'type'=>'int'),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
            ),
            'addViewNumAction'   =>  array(
                'noticeid'  =>  array('name'=>'noticeid','require'=>true,'type'=>'int'),
            ),
        );
    }

    /**
     * 获取通知详情
     * @return array
     */
    public function detailAction(){
        $noticeModel = new NoticeModel();
        $detail = $noticeModel->getModel($this->noticeid,$this->crid);
        if(!$detail){
            return returnData(0,'通知不存在');
        }
        return returnData(1,'',$detail);
    }
    /**
     * 获取网校通知
     * @return array
     */
    public function listAction(){
        $userModel = new UserModel();

        $user = $userModel->getUserById($this->uid);

        if($user['groupid'] == 5){
            $parameters['groupid'] = 5;
        }else{
            $parameters['groupid'] = 6;
        }

        $noticeModel = new NoticeModel();
        $total = $noticeModel->getNoticeCount($this->crid,$parameters);
        $pageClass  = new Page($total,$this->pagesize);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $list = $noticeModel->getNoticeList($this->crid,$parameters);
        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
    }

    /**
     * 增加浏览量
     * @return bool
     */
    public function addViewNumAction(){
        $noticeModel = new NoticeModel();
        $noticeModel->addviewnum($this->noticeid);
        return true;
    }
}