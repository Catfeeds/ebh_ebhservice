<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:50
 */
class FavoriteController extends Controller{

    public function parameterRules(){
        return array(
            'courseAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
            ),
            'addCourseAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int','min'=>1)
            ),
            'deleteCourseAction'   =>  array(
                'fid'  =>  array('name'=>'fid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
            ),
            'courseExistAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int','min'=>1)
            ),
        );
    }

    /**
     * 获取学生课件列表
     * @return mixed
     */
    public function courseAction(){
        $parameters['uid'] = $this->uid;
        $parameters['crid'] = $this->crid;
        $favoriteModel = new FavoriteModel();

        $total = $favoriteModel->getCourseCount($parameters);

        $pageClass  = new Page($total);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;

        $list = $favoriteModel->getCourseList($parameters);
        $result['total'] = $total;
        $result['list'] = $list;

        return $result;
    }

    /**
     * 添加课件收藏
     * @return array
     */
    public function addCourseAction(){
        $courseModel = new CoursewareModel();
        $favoriteModel = new FavoriteModel();
        $course = $courseModel->getSimpleInfoById($this->cwid);
        if(!$course){
            return returnData(0,'课件不存在');
        }
        $isExist = $favoriteModel->courseIsExist($this->crid,$this->uid,$this->cwid);
        if($isExist > 0){
            return returnData(0,'已经收藏过了');
        }

        $parameters['uid'] = $this->uid;
        $parameters['crid'] = $this->crid;
        $parameters['cwid'] = $this->cwid;
        $parameters['type'] = 1;
        $parameters['url'] = '/myroom/mycourse/'.$this->cwid.'.html';
        $parameters['title'] = $course['title'];
        $id = $favoriteModel->insert($parameters);

        if($id > 0){
            return returnData(1,'收藏成功',array(
                'fid'   =>  $id
            ));
        }else{
            return returnData(0,'收藏失败');
        }
    }

    /**
     * 取消收藏
     * @return array
     */
    public function deleteCourseAction(){
        $favoriteModel = new FavoriteModel();
        $res = $favoriteModel->deleteByUid($this->uid,$this->fid);
        if($res === false){
            return returnData(0,'取消失败');
        }else{
            return returnData(1,'取消成功');
        }
    }
    /**
     * 查看课件是否已经收藏
     * @return mixed
     */
    public function courseExistAction(){
        $favoriteModel = new FavoriteModel();
        $isExist = $favoriteModel->courseIsExist($this->crid,$this->uid,$this->cwid);
        return $isExist;
    }
}