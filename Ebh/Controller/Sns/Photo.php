<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:44
 */
class PhotoController extends Controller {
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'page'  =>  array('name'=>'page','type'=>'int','default'=>1),
                'pagesize' =>	array('name'=>'pagesize','default'=>9,'type'=>'int'),
                'format'  =>  array('name'=>'format','default'=>'json'),
            ),
        );
    }

    /**
     * 获取图片列表
     * @return array
     */
    public function listAction(){
        $imageModel = new SnsImageModel();
        $page = $this->page;
        $inum = ($page-1) % 2;
        $pagesize = $this->pagesize;

        $param['limit'] = max(0,($page - 1) * $pagesize).",$pagesize";
        $param['uid'] = $this->uid;
        $param['status'] = 0;

        $total = $imageModel->getImgCount($param);
        $list = $imageModel->getimglist($param);

        if($this->format == 'html'){
            Ebh()->helper->load('feedhtml');
            $result = '';
            if(!empty($list)){
                $result = getphotohtml($inum,$list);
            }
        }else{
            $result = $list;
        }

        return array(
            'total' =>  $total,
            'list'  =>  $result
        );
    }
}