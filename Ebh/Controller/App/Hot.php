<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:32
 */
class HotController extends Controller{
    public function parameterRules(){
        return array(
            'setFolderViewNumAction'    =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'folderid'  =>  array('name'=>'folderid','require'=>true,'type'=>'int','min'=>1),
                'viewnum'  =>  array('name'=>'viewnum','require'=>true,'type'=>'int','min'=>0),
            )
        );
    }

    /**
     * 更新网校人气值
     * @return array
     */
    public function setFolderViewNumAction(){

        $folderModel = new FolderModel();

        $folder = $folderModel->getFolderById($this->folderid,$this->crid);

        if(!$folder){
            return returnData(0,'课程不存在');
        }

        //更新人气
        $folderModel->setviewnum($this->folderid,$this->viewnum);
        //更新缓存中的人气值
        Ebh()->cache->getRedis()->hset('folder'.'viewnum', $this->folderid,$this->viewnum);

        return returnData(1,'设置成功');
    }
}