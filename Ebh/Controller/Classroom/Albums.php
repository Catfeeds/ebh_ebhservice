<?php
/**
 * 问卷调查
 * Created by PhpStorm.
 * User: zyp
 * Date: 2017/07/03
 * Time: 19:12
 */
class AlbumsController extends Controller{
    public $albumsModel;
    public function init(){
        parent::init();
        $this->albumsModel = new AlbumsModel();
    }
    public function parameterRules(){
        return array(
            'getAlbumsAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'aid'  =>  array('name'=>'aid','default'=>0,'type'=>'int'),
                'q' =>	array('name'=>'q','type'=>'string'),
                'issystem'  =>  array('name'=>'issystem','default'=>0,'type'=>'int'),
                'systype'  =>  array('name'=>'systype','default'=>0,'type'=>'int'),
            ),
            'addAlbumsAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'crid'  =>  array('name'=>'crid','type'=>'int','min'=>1),
                'alname'  =>  array('name'=>'alname','type'=>'string'),
                'aid'  =>  array('name'=>'aid','default'=>0,'type'=>'int'),
                'issystem'  =>  array('name'=>'issystem','type'=>'int'),
                'ishide'  =>  array('name'=>'ishide','type'=>'int'),
                'displayorder'  =>  array('name'=>'displayorder','type'=>'int'),
                'systype'  =>  array('name'=>'systype','type'=>'int'),
                'clienttype'  =>  array('name'=>'clienttype','type'=>'int'),
            ),
            'editAlbumsAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'crid'  =>  array('name'=>'crid','type'=>'int','min'=>1),
                'aid'  =>  array('name'=>'aid','require'=>true,'type'=>'int','min'=>1),
                'alname'  =>  array('name'=>'alname','require'=>true,'type'=>'string'),
                'paid'  =>  array('name'=>'paid','type'=>'int','min'=>1),
                'ishide'  =>  array('name'=>'ishide','type'=>'int'),
                'displayorder'  =>  array('name'=>'displayorder','type'=>'int'),
                'systype'  =>  array('name'=>'systype','type'=>'int'),
            ),
            'delAlbumsAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'crid'  =>  array('name'=>'crid','type'=>'int','min'=>1),
                'aid'  =>  array('name'=>'aid','require'=>true,'type'=>'int'),
            ),
            'getAlbumsPhotosAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'aid'  =>  array('name'=>'aid','default'=>0,'type'=>'int'),
                'q' =>	array('name'=>'q','type'=>'string'),
            ),
            'getUserPhotosAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'q' =>	array('name'=>'q','type'=>'string'),
            ),
            'addAlbumsPhotosAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'crid'  =>  array('name'=>'crid','type'=>'int','min'=>1),
                'aid'  =>  array('name'=>'aid','default'=>0,'type'=>'int'),
                'photoname'  =>  array('name'=>'photoname','type'=>'string'),
                'size'  =>  array('name'=>'size','type'=>'int'),
                'ext'  =>  array('name'=>'ext','type'=>'string'),
                'server'  =>  array('name'=>'server','type'=>'string'),
                'path'  =>  array('name'=>'path','type'=>'string'),
                'issystem'  =>  array('name'=>'issystem','type'=>'int'),
                'width'  =>  array('name'=>'width','default'=>0,'type'=>'int'),
                'height'  =>  array('name'=>'height','default'=>0,'type'=>'int'),
                'ishide'  =>  array('name'=>'ishide','type'=>'int'),
                'roomtype'  =>  array('name'=>'roomtype','type'=>'string'),
                'clienttype'  =>  array('name'=>'clienttype','type'=>'int'),
            ),
            'editPhotosAction'   =>  array(
                'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int','min'=>1),
                'photoname'  =>  array('name'=>'photoname','require'=>true,'type'=>'string'),
            ),
            'delAlbumsPhotosAction'   =>  array(
                'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int','min'=>1),
                'aid'  =>  array('name'=>'aid','default'=>0,'type'=>'int'),
            ),
            'editGalleryPhotosAction'   =>  array(
                'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int','min'=>1),
                'photoname'  =>  array('name'=>'photoname','type'=>'string'),
                'oldaid'  =>  array('name'=>'oldaid','type'=>'int','min'=>1),
                'newaid'  =>  array('name'=>'newaid','type'=>'int','min'=>1),
                'istop'  =>  array('name'=>'istop','type'=>'int'),
                'size'  =>  array('name'=>'size','type'=>'int'),
                'ext'  =>  array('name'=>'ext','type'=>'string'),
                'server'  =>  array('name'=>'server','type'=>'string'),
                'path'  =>  array('name'=>'path','type'=>'string'),
                'width'  =>  array('name'=>'width','default'=>0,'type'=>'int'),
                'height'  =>  array('name'=>'height','default'=>0,'type'=>'int'),
                'ishide'  =>  array('name'=>'ishide','type'=>'int'),
                'issystem'  =>  array('name'=>'issystem','type'=>'int'),
            ),
            'delGalleryPhotosAction'   =>  array(
                'pids'  =>  array('name'=>'pids','require'=>true,'type'=>'array'),
                'issystem'  =>  array('name'=>'issystem','type'=>'int'),
            ),
            'getGalleryPhotosAction'   =>  array(
                'aid'  =>  array('name'=>'aid','type'=>'int'),
                'systype'  =>  array('name'=>'systype','type'=>'int'),
                'issystem'  =>  array('name'=>'issystem','default'=>1,'type'=>'int'),
                'notpage'  =>  array('name'=>'notpage','type'=>'int'),
                'toplevel'  =>  array('name'=>'toplevel','type'=>'int'),
                'q' =>	array('name'=>'q','type'=>'string'),
                'clienttype'  =>  array('name'=>'clienttype','type'=>'int'),
                'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
                'page' =>	array('name'=>'page','default'=>0,'type'=>'int'),
            ),
            'getGallerysAction'   =>  array(
                'paid'  =>  array('name'=>'paid','type'=>'int'),
                'q' =>	array('name'=>'q','type'=>'string'),
                'issystem'  =>  array('name'=>'issystem','default'=>1,'type'=>'int'),
                'systype'  =>  array('name'=>'systype','default'=>0,'type'=>'int'),
                'ishide'  =>  array('name'=>'ishide','type'=>'int'),
                'clienttype'  =>  array('name'=>'clienttype','type'=>'int'),
            ),
            'getPhotosByPidAction'   =>  array(
                'pid'  =>  array('name'=>'pid','require'=>true,'type'=>'int','min'=>1),
                'issystem'  =>  array('name'=>'issystem','default'=>1,'type'=>'int'),
            ),
            'getGalleryByAidAction'   =>  array(
                'aid'  =>  array('name'=>'aid','require'=>true,'type'=>'int','min'=>1),
                'issystem'  =>  array('name'=>'issystem','default'=>1,'type'=>'int'),
            ),
        );
    }
    /**
    *根据aid获取指定相册信息
    */
    public function getAlbumsAction(){
        $param = array();
        $param['crid'] = $this->crid;
        $param['uid'] = $this->uid;
        $param['aid'] = $this->aid;
        if(isset($this->q)){
            $param['q'] = $this->q;
        }
        if(!empty($this->issystem)){
            $param['issystem'] = $this->issystem;
        }
        if(!empty($this->systype)){
            $param['systype'] = $this->systype;
        }
        return $this->albumsModel->getAlbums($param);
    }
    /**
     *创建新相册
     */
    public function addAlbumsAction(){
        $param = array();
        $param['uid'] = $this->uid;
        $param['alname'] = !empty($this->alname) ? $this->alname : '新建文件夹';
        $param['aid'] = $this->aid;
        if(!empty($this->issystem)){
            $param['issystem'] = $this->issystem;
        }
        if(!empty($this->crid)){
            $param['crid'] = $this->crid;
        }
        if(!empty($this->ishide)){
            $param['ishide'] = $this->ishide;
        }
        if(!empty($this->displayorder)){
            $param['displayorder'] = $this->displayorder;
        }
        if(!empty($this->systype)){
            $param['systype'] = $this->systype;
        }
        if(isset($this->clienttype)){
            $param['clienttype'] = $this->clienttype;   //模版终端类型：0-电脑端,1-手机版
        }
        return $this->albumsModel->addAlbums($param);
    }
    /**
     *修改相册名称或图片库分类
     */
    public function editAlbumsAction(){
        $param = array();
        $param['uid'] = $this->uid;
        $param['aid'] = $this->aid;
        $param['alname'] = $this->alname;
        if(!empty($this->crid)){
            $param['crid'] = $this->crid;
        }
        if(isset($this->paid)){
            $param['paid'] = $this->paid;
        }
        if(isset($this->ishide)){
            $param['ishide'] = $this->ishide;
        }
        if(isset($this->displayorder)){
            $param['displayorder'] = $this->displayorder;
        }
        if(isset($this->systype)){
            $param['systype'] = $this->systype;
        }
        return $this->albumsModel->editAlbums($param);
    }
    /**
     *删除相册
     */
    public function delAlbumsAction(){
        $param = array();
        if(!empty($this->crid)){
            $param['crid'] = $this->crid;
        }
        $param['uid'] = $this->uid;
        $param['aid'] = $this->aid;
        return $this->albumsModel->delAlbums($param);
    }
    /**
     *根据aid获取指定相册图片信息
     */
    public function getAlbumsPhotosAction(){
        $param = array();
        $param['crid'] = $this->crid;
        $param['uid'] = $this->uid;
        $param['aid'] = $this->aid;
        if(isset($this->q)){
            $param['q'] = $this->q;
        }
        return $this->albumsModel->getAlbumsPhotos($param);
    }
    /**
     *获取指定用户图片信息
     */
    public function getUserPhotosAction(){
        $param = array();
        $param['crid'] = $this->crid;
        $param['uid'] = $this->uid;
        if(isset($this->q)){
            $param['q'] = $this->q;
        }
        return $this->albumsModel->getUserPhotos($param);
    }
    /**
     *保存相册中图片信息
     */
    public function addAlbumsPhotosAction(){
        $param = array();
        if(!empty($this->crid)){
            $param['crid'] = $this->crid;
        }
        $param['uid'] = $this->uid;
        $param['aid'] = $this->aid;//相册id
        $param['photoname'] = $this->photoname;
        $param['ext'] = $this->ext;
        $param['size'] = $this->size;
        $param['server'] = $this->server;
        $param['path'] = $this->path;
        $param['width'] = $this->width;
        $param['height'] = $this->height;
        if(!empty($this->issystem)){
            $param['issystem'] = $this->issystem;   //相册类型,0普通相册,1系统相册,2首页装扮模板相册
        }
        if(!empty($this->ishide)){
            $param['ishide'] = $this->ishide;       //是否隐藏,1表示隐藏
        }
        if(!empty($this->roomtype)){
            $param['roomtype'] = $this->roomtype;   //edu教育版,com企业版
        }
        if(isset($this->clienttype)){
            $param['clienttype'] = $this->clienttype;   //模版终端类型：0-电脑端,1-手机版
        }
        return $this->albumsModel->addOnePhotos($param);
    }
    /**
     *修改相册中图片名称
     */
    public function editPhotosAction(){
        $param = array();
        $param['pid'] = $this->pid;
        $param['photoname'] = $this->photoname;
        return $this->albumsModel->editPhotos($param);
    }

    /**
     *删除相册中图片
     */
    public function delAlbumsPhotosAction(){
        $param = array();
        $param['aid'] = $this->aid;
        $param['pid'] = $this->pid;
        return $this->albumsModel->delAlbumsPhotos($param);
    }
    /**
     *修改图片库图片
     */
    public function editGalleryPhotosAction(){
        $param = array();
        $param['pid'] = $this->pid;
        if(!empty($this->photoname)){
            $param['photoname'] = $this->photoname;
        }
        if(!empty($this->oldaid)){
            $param['oldaid'] = $this->oldaid;
        }
        if(!empty($this->newaid)){
            $param['newaid'] = $this->newaid;
        }
        if(isset($this->istop)){
            $param['istop'] = $this->istop;
        }
        if(!empty($this->ext)) {
            $param['ext'] = $this->ext;
        }
        if(!empty($this->size)) {
            $param['size'] = $this->size;
        }
        if(!empty($this->server)) {
            $param['server'] = $this->server;
        }
        if(!empty($this->path)) {
            $param['path'] = $this->path;
        }
        if(!empty($this->width)) {
            $param['width'] = $this->width;
        }
        if(!empty($this->height)) {
            $param['height'] = $this->height;
        }
        if(isset($this->ishide)){
            $param['ishide'] = $this->ishide;       //是否隐藏,1表示隐藏
        }
        if(isset($this->issystem)){
            $param['issystem'] = $this->issystem;   //相册类型,0普通相册,1系统相册,2首页装扮模板相册
        }
        return $this->albumsModel->editGalleryPhotos($param);
    }
    /**
     *删除图片库图片
     */
    public function delGalleryPhotosAction(){
        $param = array();
        $param['pids'] = $this->pids;
        if(isset($this->issystem)){
            $param['issystem'] = $this->issystem;   //相册类型,0普通相册,1系统相册,2首页装扮模板相册
        }
        return $this->albumsModel->delGalleryPhotos($param);
    }
    /**
     *获取图片库图片信息
     */
    public function getGalleryPhotosAction(){
        $param = array();
        $param['issystem'] = $this->issystem;   //相册类型,0普通相册,1系统相册,2首页装扮模板相册
        if(!empty($this->systype)){
            $param['systype'] = $this->systype;
        }
        if(!empty($this->aid)){
            $param['aid'] = $this->aid;
        }
        if(isset($this->q)){
            $param['q'] = $this->q;
        }
        if(!empty($this->notpage)){
            $param['notpage'] = $this->notpage;
        }
        if(!empty($this->toplevel)){
            $param['toplevel'] = $this->toplevel;   //toplevel等于1表示查询的是主类信息
        }
        if(isset($this->clienttype)){
            $param['clienttype'] = $this->clienttype;      //终端类型：0-电脑版,1-手机版
        }
        $param['pagesize'] = $this->pagesize;
        $param['page'] = $this->page;
        $photos = $this->albumsModel->getGalleryPhotos($param);
        $count = $this->albumsModel->getGalleryPhotosCount($param);
        $photos = !empty($photos) ? $photos : array();
        $count = !empty($count) ? $count : 0;
        return array('photos'=>$photos,'count'=>$count);
    }
    /**
     *获取图片库信息
     */
    public function getGallerysAction(){
        $param = array();
        $res = array();
        $paid = 0;
        $param['issystem'] = $this->issystem;
        if(isset($this->paid)){
            $param['paid'] = $this->paid;
            $paid = $param['paid'];
        }
        if(isset($this->q)){
            $param['q'] = $this->q;
        }
        if(!empty($this->systype)){
            $param['systype'] = $this->systype;//相册类型,0普通相册,1系统相册,2首页装扮模板相册
        }
        if(isset($this->ishide)){
            $param['ishide'] = $this->ishide;
        }
        if(isset($this->clienttype)){
            $param['clienttype'] = $this->clienttype;//终端类型：0-电脑版,1-手机版
        }
        $gallerys = $this->albumsModel->getGallerys($param);
        $res=$this->arraychild($gallerys,$paid);
        return $res;
    }
    /**
     *根据pid获取图片库信息
     */
    public function getPhotosByPidAction(){
        $param = array();
        $param['pid'] = $this->pid;          //图片id
        $param['issystem'] = $this->issystem;//相册类型,0普通相册,1系统相册,2首页装扮模板相册
        return $this->albumsModel->getPhotosByPid($param);
    }
    /**
     *根据aid获取图片库分类信息
     */
    public function getGalleryByAidAction(){
        $param = array();
        $param['aid'] = $this->aid;          //相册id
        $param['issystem'] = $this->issystem;//相册类型,0普通相册,1系统相册,2首页装扮模板相册
        return $this->albumsModel->getGalleryByAid($param);
    }
    //数组子集重新排列
    private function  arraychild($gallerys,$paid){
        $result = array();
        if(!empty($gallerys) && is_array($gallerys)){
            $i = 0;
            foreach ($gallerys as &$gallery){
                if((isset($gallery['paid'])) && ($gallery['paid'] == $paid)){
                    $result[$i] = $gallery;
                    foreach ($gallerys as &$child){
                        if(!empty($gallery['aid']) && ($child['paid'] == $gallery['aid'])){
                            $result[$i]['nums'] += $child['nums'];
                            $result[$i]['children'][]= $child;
                            unset($child);
                        }
                    }
                    $i++;
                    unset($gallery);
                }
            }
            return $result;
        }
    }
}