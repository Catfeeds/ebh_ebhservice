<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:21
 * 课程权限注入类
 *
 * useage:$folderUtil->init($list,$userInfo['uid'],$this->crid)->injectPermission()->getResult();
 */
class FolderUtil{

    public function __construct(){
        $this->uid = 0;
        $this->crid = 0;
        $this->isschool = 0;
        $this->groupid = 0;
        $this->dataList = array();
        $this->resultData = array();
    }


    /**
     * 初始化
     * @param array $dataList
     * @param int $uid 用户ID
     * @param int $crid 网校ID
     * @return $this
     */
    public function init($dataList = array(),$uid = 0,$crid = 0){
        $this->dataList = $dataList;
        if(is_numeric($crid) && $crid >0){
            $this->crid = $crid;
            $sql = 'select isschool from ebh_classrooms where crid = '.$crid.' limit 1';
            $roominfo = Ebh()->db->query($sql)->row_array();
            if(!empty($roominfo)){
                $this->isschool = $roominfo['isschool'];
            }else{
                $this->isschool = 0;
            }
        }else{
            $this->isschool = 0;
        }

        if (  is_numeric($uid) && $uid > 0 ){
            $sql = 'select groupid from ebh_users where uid = '.$uid.' limit 1';
            $userinfo = Ebh()->db->query($sql)->row_array();
            if(!empty($userinfo)){
                $this->uid = $uid;
                $this->groupid = $userinfo['groupid'];
            }else{
                $this->groupid = 0;
            }
        }else{
            $this->groupid = 0;
        }

        return $this;
    }

    /**
     * 注入权限 返回新课程列表
     * @return array
     */
    public function injectPermission($format = true){
        //返回数据显示格式 0=按服务包分类显示 1直接显示课程
        $this->resultData['show'] = 1;
        if(empty($this->dataList)){
            $this->resultData['datalist'] = $this->dataList;
            return $this;
        }

        //注入收费课程权限
        $this->_injectPermissionInFolder();


        //需要格式化为true 则格式化为按服务包分类
        if($format && $this->isschool == 7 ){
            $this->resultData['show'] = 0;
            $resultList = array();
            foreach ($this->dataList as $list){
                $resultList[$list['pid']]['package'] = array(
                    'pid'   =>  $list['pid'],
                    'pname' =>  $list['pname'],
                    'pdisplayorder' =>  $list['pdisplayorder'],
                    'itype' =>  $list['itype']
                );
                $resultList[$list['pid']]['list'][] = $list;
            }

            $this->dataList = array_values($resultList);


            //处理服务包的排序
            foreach ($this->dataList as $list){
                $sort['pdisplayorder'][] = $list['package']['pdisplayorder'];
                $sort['itype'][] = $list['package']['itype'];
                $sort['pid'][] = $list['package']['pid'];
            }

            array_multisort($sort['itype'] ,SORT_ASC,$sort['pdisplayorder'] , SORT_ASC ,$sort['pid'] , SORT_DESC , $this->dataList);
        }
        $this->resultData['datalist'] = $this->dataList;

        return $this;

    }

    /**
     * 获取结果
     * @return array
     */
    public function getResult(){
        return $this->resultData;
    }


    /**
     * 对课程列表注入权限
     * @param array $list
     */
    private function _injectPermissionInFolder($list = array()){
        if(empty($list)){
            $list = $this->dataList;
        }

        //读取用户已购买的的课程
        $folderModel = new FolderModel();
        $parameters = array(
            'uid'   =>  $this->uid,
            'crid'  =>    $this->crid,
            'filterdate'    =>  1
        );
        $userFolders = $folderModel->getUserPayFolderList($parameters);
        $userfolderids = array();
        //读取到用户已经购买的课程ID
        foreach ($userFolders as $ufolder){
            if(!in_array($ufolder['folderid'],$userfolderids)){
                $userfolderids[] = $ufolder['folderid'];
            }
        }

        //获取课程购买信息
        $folderIds = array();
        foreach ($list as $folder){
            $folderIds[] = $folder['folderid'];
        }
        $payitemModel = new PayitemModel();
        $payitems = $payitemModel->getItemsByFolderIds($folderIds,$this->crid);
        $items = array();
        foreach ($payitems as $payitem){
            $items[$payitem['folderid']] = $payitem;
        }
        foreach ($list as &$folder){
            if(isset($items[$folder['folderid']])){
                $folder['itemid'] = $items[$folder['folderid']]['itemid'];
                $folder['iprice'] = $items[$folder['folderid']]['iprice'];
                $folder['pid'] = $items[$folder['folderid']]['pid'];
                $folder['sid'] = $items[$folder['folderid']]['sid'] ? $items[$folder['folderid']]['sid'] :0;
                $folder['pname'] = $items[$folder['folderid']]['pname'];
                $folder['cannotpay'] = $items[$folder['folderid']]['cannotpay'];
                $folder['pdisplayorder'] = $items[$folder['folderid']]['pdisplayorder'];
                $folder['itype'] = $items[$folder['folderid']]['itype'];
            }else{
                $folder['itemid'] = 0;
                $folder['iprice'] = 0;
                $folder['pid'] = 0;
                $folder['sid'] = 0;
                $folder['pname'] = '';
                $folder['cannotpay'] = 0;
            }
            //用户如果已经拥有课程 标记权限
            if(in_array($folder['folderid'],$userfolderids) || $this->isschool != 7 || $this->groupid == 5){
                $folder['permission'] = 1;
            }else{
                $folder['permission'] = 0;
            }

            //如果开启了本校课程免费 //则价格显示免费
            if($folder['isschoolfree'] == 1){
                $roomuserModel = new RoomUserModel();
                $is_alumni = $roomuserModel->isAlumni($this->crid, $this->uid);
                if($is_alumni){
                    $folder['iprice'] = 0;
                }
            }
        }




        $this->dataList = $list;



    }

}