<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:04
 */
class NavigationController extends Controller{
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'pagesize'  =>  array('name'=>'pagesize','type'=>'int','default'=>getConfig('system.page.listRows')),
            ),
            'delAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int'),
                'id'  =>  array('name'=>'id','require'=>true,'type'=>'int')
            ),
            'addAction'    =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'name'  =>  array('name'=>'name','require'=>true),
                'image_url'  =>  array('name'=>'image_url','require'=>true),
                'type'  =>  array('name'=>'type','require'=>true),
                'link_value'  =>  array('name'=>'link_value','require'=>false,'type'=>'array','default'=>array()),
                'displayorder'  =>  array('name'=>'displayorder','require'=>true,'type'=>'int'),
            ),
            'editAction'    =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'id'  =>  array('name'=>'id','require'=>true,'type'=>'int','min'=>1),
                'name'  =>  array('name'=>'name','require'=>false),
                'image_url'  =>  array('name'=>'image_url','require'=>false),
                'type'  =>  array('name'=>'type','require'=>false),
                'link_value'  =>  array('name'=>'link_value','require'=>false,'type'=>'array'),
                'displayorder'  =>  array('name'=>'displayorder','require'=>false,'type'=>'int'),
            )
        );
    }


    /**
     * 添加数据
     */
    public function addAction(){
        $parameters['crid'] = $this->crid;
        $parameters['name'] = $this->name;
        $parameters['image_url'] = $this->image_url;
        $parameters['type'] = $this->type;
        $parameters['displayorder'] = $this->displayorder;

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
            case 'type':
                $pid =  explode('_',$link_value['pid']);
                $pid = $pid[0];
                $sid = explode('_',$link_value['sid']);
                $sid = $sid[0];
                $url = $domain.'://type/?pid='.$pid.'&sid='.$sid;
                break;
            default:
                return returnData(0,'请选择正确的链接类型');
                break;
        }

        $parameters['url'] = $url;
        $parameters['link_value'] = json_encode($link_value);
        $appNavigationModel = new AppNavigationsModel();
        $result = $appNavigationModel->add($parameters);
        if($result > 0){
            return returnData(1,'添加成功',array('id'=>$result));
        }else{
            return returnData(0,'添加失败');
        }

    }

    /**
     * 编辑链接
     * @return array
     */
    public function editAction(){
        $parameters['id'] = $this->id;
        if($this->name !== null){
            $parameters['name'] = $this->name;
        }
        if($this->image_url !== null){
            $parameters['image_url'] = $this->image_url;
        }
        if($this->displayorder !== null){
            $parameters['displayorder'] = $this->displayorder;
        }
        $classRoomModel = new ClassRoomModel();
        $classroomInfo = $classRoomModel->getModel($this->crid);
        if(!$classroomInfo){
            return returnData(0,'网校信息不存在');
        }
        $domain = $classroomInfo['domain'];
        if($this->type !== null){
            $parameters['type'] = $this->type;

            if($this->type != 'none' && $this->link_value === null){
                return returnData(0,'请选择链接的值');
            }
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
                case 'type':
                    $pid =  explode('_',$link_value['pid']);
                    $pid = $pid[0];
                    $sid = explode('_',$link_value['sid']);
                    $sid = $sid[0];
                    $url = $domain.'://type/?pid='.$pid.'&sid='.$sid;
                    break;
                default:
                    return returnData(0,'请选择正确的链接类型');
                    break;
            }

            $parameters['url'] = $url;
            $parameters['link_value'] = json_encode($link_value);
        }

        $appNavigationModel = new AppNavigationsModel();
        $result = $appNavigationModel->edit($parameters);

        if($result === false){
            return returnData(0,'修改失败');
        }else{
            return returnData(1,'修改成功');
        }

    }

    /**
     * 读取幻灯片列表
     * @return array
     */
    public function listAction(){
        $appNavigationModel = new AppNavigationsModel();
        $total = $appNavigationModel->getCount($this->crid);
        $pageClass  = new Page($total,$this->pagesize);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;

        $list = $appNavigationModel->getList($this->crid,$parameters);

        return array(
            'total' =>  $total,
            'list'  =>  $list,
            'nowPage'   =>  $pageClass->nowPage,
            'totalPage' =>  $pageClass->totalPages
        );
    }

    /**
     * 删除
     * @return array
     */
    public function delAction(){
        $appNavigationModel = new AppNavigationsModel();
        $result = $appNavigationModel->del($this->id,$this->crid);

        if($result > 0){
            return returnData(1,'删除成功');
        }else{
            return returnData(0,'删除失败');
        }
    }
}