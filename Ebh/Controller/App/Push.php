<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:11
 * 推送管理接口
 */
class PushController extends Controller{

    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
                'status'    =>  array('name'=>'status','type'=>'int')
            ),
            'addAction'    =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'message'  =>  array('name'=>'message','require'=>true),
                'type'  =>  array('name'=>'type','require'=>true),
                'link_value'  =>  array('name'=>'link_value','require'=>false,'type'=>'array','default'=>array()),
                'pushtime'  =>  array('name'=>'pushtime','require'=>true,'type'=>'int'),
            ),
            'pushAction'    =>  array(
                'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int')
            ),
            'delAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int')
            ),
        );
    }

    /**
     * 读取幻灯片列表
     * @return array
     */
    public function listAction(){
        $appPushModel = new AppPushModel();
        $parameters = array();
        if($this->status !== null){
            $parameters['status'] = $this->status;
        }
        $total = $appPushModel->getCount($this->crid,$parameters);
        $pageClass  = new Page($total,$this->pagesize);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;

        $list = $appPushModel->getList($this->crid,$parameters);

        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
    }
    /**
     * 删除推送
     * @return array
     */
    public function delAction(){
        $appPushModel = new AppPushModel();
        $result = $appPushModel->del($this->pid,$this->crid);

        if($result > 0){
            //删除成功后删除定时任务
            CronUtil::deleteCron('appPush_'.$this->pid);
            return returnData(1,'删除成功');
        }else{
            return returnData(0,'删除失败');
        }
    }

    /**
     * 添加推送消息
     * @return array
     */
    public function addAction(){
        //$time = strtotime('2017-09-13 17:25');
        //CronUtil::addUrlCronWithoutLoop('test1',$time,'http://192.168.0.90/push.php');
        $parameters['crid'] = $this->crid;
        $parameters['message'] = $this->message;
        $parameters['type'] = $this->type;
        $parameters['link_value'] = $this->link_value;
        $parameters['pushtime'] = $this->pushtime;

        $classRoomModel = new ClassRoomModel();

        $classroomInfo = $classRoomModel->getModel($this->crid);
        if(!$classroomInfo){
            return returnData(0,'网校信息不存在');
        }

        $domain = $classroomInfo['domain'];
        $link_value = $this->link_value;
        switch ($this->type){
            case 'none':
                $link_value = (object)array();
                $url = '';
                break;
            case 'folder':
                $folderId = explode('_',$link_value['folderid']);
                $folderId = $folderId[0];
                $url  = $domain.'://folder/'.$folderId;
                break;
            default:
                return returnData(0,'请选择正确的链接类型');
                break;
        }

        $parameters['url'] = $url;
        $parameters['link_value'] = json_encode($link_value);

        $appPushModel = new AppPushModel();

        $result = $appPushModel->add($parameters);

        if($result > 0){
            //创建定时任务

            $pushUrl = Ebh()->config->get('apiserver.pushnotify.host').'/App/Push/push?pid='.$result;
            CronUtil::addUrlCronWithoutLoop('appPush_'.$result,$this->pushtime,$pushUrl);

            return returnData(1,'添加成功',array('pid'=>$result));
        }else{
            return returnData(0,'添加失败');
        }
    }


    public function testAction(){
        $res = CronUtil::addUrlCronWithoutLoop('test111223',time()+60,'http://192.168.0.90/push.php');
        var_export($res);exit;
    }
    /**
     * 推送指定ID
     * @return array
     */
    public function pushAction(){
        $pid = $this->pid;

        $appPushModel = new AppPushModel();

        $detail = $appPushModel->getDetail(array('pid'=>$pid));
        if(!$detail){

            return returnData(0,'推送信息不存在');
        }
        if($detail['status'] == 1){
            return returnData(0,'该信息已推送过');
        }
        $jPushtUtil  = new JPushUtil($detail['crid']);
        $rs = $jPushtUtil->push($detail['message'],array('url'=>$detail['url'],'type'=>$detail['type'],'link_value'=>json_decode($detail['link_value'],true)));
        if($rs){
            $appPushModel->edit(array('pid'=>$detail['pid'],'status'=>1));
            return returnData(1,'推送成功');
        }else{
            return returnData(0,'推送失败');
        }
    }
}