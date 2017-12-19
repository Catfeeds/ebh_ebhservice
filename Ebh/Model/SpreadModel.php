<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:33
 */
class SpreadModel{

    /**
     * 获取指定课程的推广详情
     * @param $folderid
     */
    public function detail($itemid,$uid){
        $sql = 'select p.itemid,p.pid,p.iname,p.isummary,p.crid,p.folderid,p.iprice,p.imonth,p.iday,p.cannotpay from ebh_pay_items p where status=0 and itemid='.$itemid;
        $payItem = Ebh()->db->query($sql)->row_array();
        if(!$payItem){
            return false;
        }
        /**
         * 获取课程信息
         */
        $sql = 'select folderid,foldername,img,uid,crid,coursewarenum,summary,fprice,viewnum,detail from ebh_folders where folderid='.$payItem['folderid'];
        $folder = Ebh()->db->query($sql)->row_array();

        if(!$folder){
            return  false;
        }
        /**
         * 获取用户是否拥有权限
         */
        $userpermisionsModel = new UserpermisionsModel();
        $permission = $userpermisionsModel->check($payItem['crid'],$uid,$folder['folderid']);


        /**
         * 获取课程第一个免费视频课
         */
        $sql = 'select c.cwid,c.logo,c.thumb from ebh_roomcourses rc join ebh_coursewares c on c.cwid=rc.cwid where rc.folderid='.$folder['folderid'].' and c.ism3u8 = 1 and rc.isfree=1  order by c.displayorder asc,c.cwid desc limit 1';
        $course = Ebh()->db->query($sql)->row_array();
        $folder['lastCourse'] = $course ? $course : array();

        $folder['buyCount'] = $userpermisionsModel->getCountByFolderId($payItem['crid'],$folder['folderid']);
        return  array(
            'item'  =>  $payItem,
            'folder'    =>  $folder,
            'permission'   =>  $permission
        );

    }
}