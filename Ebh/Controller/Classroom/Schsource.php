<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:51
 */
class SchsourceController extends Controller{


    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','default'=>0),
                'uid'  =>  array('name'=>'uid','default'=>0),
            ),
        );
    }

    /**
     * 获取企业选课信息
     * @return array
     */
    public function listAction(){
        $openarr = array();
        $unopenarr = array();
        $schscourceModel = new SchsourceModel();
        $selecteditems = $schscourceModel->getselecteditems(array('crid'=>$this->crid));

        if(empty($selecteditems)){
            return array(
                'open'  =>  $openarr,
                'unopen'    =>  $unopenarr
            );
        }

        $userpermissionModel = new UserpermisionsModel();
        $itemids = array_keys($selecteditems);
        $itemids = implode(',',$itemids);
        $itemparam = array('uid'=>$this->uid,'crid'=>$this->crid,'itemids'=>$itemids);
        $openids = $userpermissionModel->getPermissionByItemids($itemparam);//已开通的
        $openids = array_keys($openids);
        foreach($selecteditems as $k=>$sitem){
            if(in_array($sitem['itemid'],$openids)){//已开通
                $selecteditems[$k]['paid'] = true;
            } elseif($sitem['del'] == 1){//没开通，又被删除的
                unset($selecteditems[$k]);
            }else{
                $selecteditems[$k]['paid'] = false;
            }
        }

        if(empty($selecteditems)){
            return array(
                'open'  =>  $openarr,
                'unopen'    =>  $unopenarr
            );
        }

        $showitemids = array_column($selecteditems,'itemid');
        $showitemids = implode(',',$showitemids);
        $showitemlist = $schscourceModel->getItemList(array('itemids'=>$showitemids));//需要显示的课程（开通，未开通）




        $payitemModel = new PayitemModel();

        $folderlist = $payitemModel->getFolderListByItemids($showitemids);

        foreach($showitemlist as $k=>$showitem){
            $itemid = $showitem['itemid'];
            $showitem['img'] = $folderlist[$itemid]['img'];
            $showitem['price'] = $selecteditems[$itemid]['price'];
            $showitem['sourceid'] = $selecteditems[$itemid]['sourceid'];
            $showitem['name'] = $selecteditems[$itemid]['name'];
            $showitem['speaker'] = $folderlist[$itemid]['speaker'];
            $showitem['summary'] = $folderlist[$itemid]['summary'];
            $showitem['coursewarenum'] = $folderlist[$itemid]['coursewarenum'];
            if(!empty($selecteditems[$itemid]['paid'])){//已开通课程
                $openarr[$showitem['sourceid']][] = $showitem;
            } else {//未开通课程
                if(!empty($folderlist[$itemid]) && $folderlist[$itemid]['del'] ==0){
                    $unopenarr[$showitem['sourceid']][] = $showitem;
                }
            }
        }
        return array(
            'open'  =>  $openarr,
            'unopen'    =>  $unopenarr
        );



    }
}