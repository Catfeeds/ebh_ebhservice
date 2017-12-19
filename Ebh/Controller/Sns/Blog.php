<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:57
 */
class BlogController extends Controller {
    public function parameterRules(){
        return array(
            'addAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'content'  =>  array('name'=>'content','require'=>true),
                'title'  =>  array('name'=>'title','require'=>true),
                'cate'  =>  array('name'=>'cate','require'=>true,'type'=>'int'),
                'permission'  =>  array('name'=>'permission','require'=>true,'type'=>'int'),
                'ip'  =>  array('name'=>'ip','require'=>true),
            ),
            'editAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'bid'  =>  array('name'=>'bid','require'=>true,'type'=>'int'),
                'content'  =>  array('name'=>'content','require'=>true),
                'title'  =>  array('name'=>'title','require'=>true),
                'cate'  =>  array('name'=>'cate','require'=>true,'type'=>'int'),
                'permission'  =>  array('name'=>'permission','require'=>true,'type'=>'int'),
                'ip'  =>  array('name'=>'ip','require'=>true),
            ),
            'categoryAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
            ),
            'addCategoryAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'catename'  =>  array('name'=>'catename','require'=>true),
            ),
            'listAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),//要获取用户的UID
                'page'  =>  array('name'=>'page','type'=>'int','default'=>1),
                'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
                'userid'  =>  array('name'=>'userid','type'=>'int','default'=>0),//申请获取的用户ID
            ),
            'detailAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'bid'  =>  array('name'=>'bid','require'=>true,'type'=>'int'),//日志ID
            ),
            'delAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'bid'  =>  array('name'=>'bid','require'=>true,'type'=>'int'),//日志ID
            ),
            'transferAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'bid'  =>  array('name'=>'bid','require'=>true,'type'=>'int'),//日志ID
                'cate'  =>  array('name'=>'cate','require'=>true,'type'=>'int'),
                'permission'  =>  array('name'=>'permission','require'=>true,'type'=>'int'),
                'ip'  =>  array('name'=>'ip','require'=>true),
            ),
            'upclickAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'bid'  =>  array('name'=>'bid','require'=>true,'type'=>'int'),//日志ID
            ),
        );
    }

    /**
     * 获取当前用户日志分类
     * @return mixed
     */
    public function categoryAction(){
        return $this->getcate();
    }

    /**
     * 添加日志分类
     * @return array
     */
    public function addCategoryAction(){
        $param['uid']  = $this->uid;
        $param['catename'] = $this->catename;
        $param['dateline'] = SYSTIME;
        $blogModel = new SnsBlogModel();
        //检测是否已存在
        $exist = $blogModel->getcate($param);
        if(!empty($exist)){
            return returnData(0,'分类已存在');
        }

        $result = $blogModel->addcate($param);

        if(!$result){
            return returnData(0,'添加失败');
        }

        return returnData(1,'添加成功',array('id'=>$result));
    }

    /**
     * 修改日志
     * @return array
     */
    public function editAction(){
        $content = $this->content;
        $uid = $this->uid;
        $bid = $this->bid;
        $title = $this->title;
        $cate = $this->cate;
        $permission = $this->permission;
        $blogModel = new SnsBlogModel();
        $tutor = strip_tags($content);
        $tutor = shortstr(trim($tutor),300);


        $tmpblog = $blogModel->getlist(array('uid'=>$this->uid,'bid'=>$bid));
        $imageModel = new SnsImageModel();

        if(!empty($tmpblog[0]['images'])){
            $gids = explode(',', $tmpblog[0]['images']);
            $imageModel->delete(array('gids'=>$gids));
        }

        //提取图片  查看图片控制器类是否存在 不存在则引入 实例化
        if(!class_exists('ImageController')){
            require_once APP_PATH . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . 'Sns' . DIRECTORY_SEPARATOR . 'Image.php';
            $imageController = new ImageController();
        }

        $imgids = $imageController->fetchImgs($content,$this->uid,$this->ip);

        $param['images'] = !empty($imgids) ?  implode(',', $imgids) : '';
        $param['tutor'] = $tutor;
        $param['uid'] = $this->uid;
        $param['title'] = $title;

        $param['content'] = $content;
        $param['cid'] = intval($cate);
        $param['permission'] = intval($permission);
        $param['dateline'] = SYSTIME;
        $param['ip'] = $this->ip;
        $result = $blogModel->update($param,array('bid'=>$bid,'uid'=>$this->uid));
        if($result>0){
            //修改对应新鲜事
            $feedsModel = new SnsFeedsModel();
            if($param['permission'] == 4){
                $feedsModel->delete(array('toid'=>$bid));
            }else{
                $feeds = $feedsModel->getfeedslist(array('condition'=>'toid = '.$bid));
                if(!empty($feeds)){
                    foreach ($feeds as $feed){
                        $fdata['fid'] = $feed['fid'];
                        $message = json_decode($feed['message'],1);
                        if(isset($message['refer'])){
                            $message['refer']['title'] = $param['title'];
                            $message['refer']['tutor'] = $param['tutor'];
                            $message['refer']['images'] = $param['images'];
                        }
                        $message['title'] = $param['title'];
                        $message['tutor'] = $param['tutor'];
                        $message['images'] = $param['images'];
                        $fdata['message'] = json_encode($message);
                        $feedsModel->update(array('message'=>$fdata['message'],'ip'=>$param['ip']),array('fid'=>$fdata['fid']));
                    }
                }
            }
            return returnData(1,'修改成功');
        }else{
            return returnData(0,'修改失败');
        }

    }
    /**
     * 添加日志
     * @return array
     */
    public function addAction(){
        $content = $this->content;
        $uid = $this->uid;
        $title = $this->title;
        $cate = $this->cate;
        $permission = $this->permission;
        $blogModel = new SnsBlogModel();

        $tutor = strip_tags($content);
        $tutor = shortstr(trim($tutor),300);
        //提取图片  查看图片控制器类是否存在 不存在则引入 实例化
        if(!class_exists('ImageController')){
            require_once APP_PATH . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . 'Sns' . DIRECTORY_SEPARATOR . 'Image.php';
            $imageController = new ImageController();
        }
        $imgids = $imageController->fetchImgs($content,$this->uid,$this->ip);

        $param['images'] = !empty($imgids) ?  implode(',', $imgids) : '';
        $param['tutor'] = $tutor;
        $param['uid'] = $uid;
        $param['title'] = $title;

        $param['content'] = $content;
        $param['cid'] = intval($cate);
        $param['permission'] = intval($permission);
        $param['dateline'] = time();
        $param['ip'] = $this->ip;

        $result = $blogModel->add($param);
        //发布一条动态消息,日志权限仅自己时不推送动态
        if($result>0 && $param['permission']<4){
            $dynamicmodel = new SnsDynamicModel();
            $feeds = array(
                'fromuid'=>	$uid,
                'message'=>json_encode(array(
                    'title'=>$title,
                    'tutor'=>!empty($param['tutor']) ? $param['tutor'] : '',
                    'images'=>!empty($param['images']) ? $param['images'] : '',
                    'type'=>'blog'
                )),
                'category'=>2,
                'toid'=>$result,
                'dateline'=>$param['dateline'],
                'ip'    =>  $this->ip
            );
            $dynamicmodel->publish($feeds,$uid);
        }

        //日志数+1
        if($result > 0){//添加成功
            //更新通知数
            $baseModel = new SnsBaseinfoModel();
            $baseModel->updateone(array(),$uid,array('blogsnum'=>'blogsnum + 1'));
        }

        if($result > 0){
            return returnData(1,'添加成功',array('bid'=>$result));
        }else{
            return returnData(0,'添加失败');
        }
    }


    /**
     * 获取日志列表
     * @return array
     */
    public function listAction(){
        $blogModel = new SnsBlogModel();

        $page = $this->page;
        $pagesize = $this->pagesize;
        $param['uid'] = $this->uid;
        $param['limit'] =  max(0,($page-1)*$pagesize)." , $pagesize";
        if($this->uid != $this->userid){
            $param['permission'] = 0;
        }
        $count = $blogModel->getlistcount($param);
        $list = $blogModel->getlist($param);
        $cates = $this->getcate();

        return array(
            'cates' =>  $cates,
            'count' =>  $count,
            'list'  =>  $list
        );
    }

    /**
     * 获取日志详情
     * @return array
     */
    public function detailAction(){
        $param['bid'] = $this->bid;
        $cates = $this->getcate();
        $blogModel = new SnsBlogModel();
        $blog = $blogModel->getlist($param);
        if(empty($blog) || $blog[0]['status'] > 0){
            return returnData(0,'日志不存在或已删除，请刷新空间重试');
        }
        //过滤图片
        if(!empty($blog[0]['images'])){
            $imageModel = new SnsImageModel();
            $gids = explode(',',$blog[0]['images']);
            $images = $imageModel->getimgs($gids);
            $_UP = Ebh()->config->get('upconfig');
            $rurl = 'http://static.ebanhui.com/sns/images/jin_650_350.png';

            foreach ($images as $item){
                if($item['status'] == 1){
                    $furl = $_UP['pic']['showpath'].$item['path'];
                    $blog[0]['content'] = str_ireplace($furl, $rurl, $blog[0]['content']);
                }
            }

        }

        $param['toid'] = $param['bid'];
        $param['category'] = $blog[0]['iszhuan'] == 1 ? 4 : 2;
        $param['fid'] = 0;
        $commentModel = new SnsCommentModel();
        $comments = $commentModel->getcommentlist($param);
        $blog[0]['replys'] = $comments;


        $user[0]['uid'] = $this->uid;
        $baseinfoModel = new SnsBaseinfoModel();
        $user = $baseinfoModel->getUserinfo($user);
        //博客最近访问*（新版暂时不需要这个）
        //$this->lastvisit($user,$blog[0]);

        return returnData(1,'获取成功',array(
            'cates' =>  $cates,
            'blog'  =>  $blog[0]
        ));
    }

    /**
     * 删除日志操作
     * @return array
     */
    public function delAction(){
        $blogModel = new SnsBlogModel();
        $bid = $this->bid;
        $where['bid'] = $bid;
        $where['uid'] = $this->uid;
        $param['status'] = 1;
        $result = $blogModel->update($param,$where);
        if($result){
            $blogfeeds = $blogModel->getblogfeed($bid);
            $feedsModel = new SnsFeedsModel();
            $feedsModel->delete(array('fid'=>$blogfeeds['fid'],'category'=>array(2,4)));

            //网校/班级动态标记
            $roommodel =  new SnsClassroomFeedsModel();
            $roommodel->delroomandclassfeeds($blogfeeds['fid'],$this->uid);


            $param['toid'] = $bid;
            $param['uid'] = $this->uid;
            $param['type'] = 2;
            $param['dateline'] = time();
            $dModel = new SnsDelModel();
            $dModel->add($param);
            //删除日志 --日志数减1
            //更新通知数
            $baseModel = new SnsBaseinfoModel();
            $baseModel->updateone(array(),$this->uid,array('blogsnum'=>'blogsnum - 1'));
            return returnData(1,'删除成功');
        }else{
            return returnData(0,'删除失败');
        }
    }

    /**
     * 转载日志
     */
    public function transferAction(){
        $bid = $this->bid;
        $cate = $this->cate;
        $permission = $this->permission;
        $uid = $this->uid;
        $blogModel = new SnsBlogModel();
        $zhwhere['uid'] = $uid;
        $zhwhere['pbid'] = $bid;
        $zhwhere['iszhuan'] = 1;
        $zhuan = $blogModel->getlist($zhwhere);
        if(count($zhuan) > 0){
            return returnData(0,'你已经转载过此日志');
        }
        $blog = $blogModel->getlist(array('bid'=>$bid));
        if($blog[0]['uid'] == $uid){
            return returnData(0,'你不能转载自己的日志');
        }
        if(empty($blog)){
            return returnData(0,'要转载的日志不存在');
        }
        $setarr['uid'] = $uid;
        $setarr['pbid'] = $bid;
        $setarr['tbid'] = ($blog[0]['tbid'] > 0) ? $blog[0]['tbid'] : $bid;
        $setarr['iszhuan'] = 1;
        $setarr['title'] = $blog[0]['title'];
        $setarr['content'] = $blog[0]['content'];
        $setarr['tutor'] = $blog[0]['tutor'];
        $setarr['cid'] = $cate;
        $setarr['permission'] = $permission;
        $setarr['dateline'] = time();
        $setarr['images'] = $blog[0]['images'];
        $setarr['ip'] = $this->ip;
        $result = $blogModel->add($setarr);
        if(!$result){
            return returnData(0,'转载失败');
        }

        //更新上级转载的日志转载数
        $where['uid'] = $blog[0]['uid'];
        $where['bid'] = $bid;
        $sparam['zhcount'] = 'zhcount + 1';
        $pupdate = $blogModel->update(array(),$where,$sparam);
        //更新顶级转载的日志转载数
        if($blog[0]['iszhuan']){
            $wheres['bid'] = $blog[0]['tbid'];
            $tupdate = $blogModel->update(array(),$wheres,$sparam);
        }
        //提取博主信息
        $baseinfoModel = new SnsBaseinfoModel();
        $author = $baseinfoModel->getUserinfo(array(array('uid'=>$blog[0]['uid'])));
        $other['uid'] = $author[0]['uid'];
        $other['realname'] = !empty($author[0]['realname']) ? $author[0]['realname'] : $author[0]['username'];
        $other['bid'] = $setarr['pbid'];

        $newfeeds = array(
            'fromuid'=>$uid,
            'message'=>json_encode(array(
                'title'=>$setarr['title'],
                'tutor'=>!empty($setarr['tutor']) ? $setarr['tutor'] : '',
                'images'=>!empty($setarr['images']) ? $setarr['images'] : '',
                'type'=>'blog',
                'referuser'=>$other,
            )),
            'category'=>4,
            'toid'=>$setarr['pbid'],
            'dateline'=>time(),
            'ip'    =>  $this->ip
        );
        $dynamicModel = new SnsDynamicModel();
        $dynamicModel->publish($newfeeds,$uid);

        if($pupdate > 0){
            $info['touid'] = $blog[0]['uid'];
            $info['title'] = $blog[0]['title'];
            $info['toid'] = $blog[0]['bid'];
            $info['category'] = $blog[0]['iszhuan'] == 1 ? 4 :2;
            $ntModel = new SnsNoticeModel();
            //发布一条通知
            $notice = array(
                'fromuid'=>$uid,
                'touid'=>$info['touid'],
                'message'=>json_encode(
                    array(
                        'fid'=>0,
                        'content'=>''
                    )
                ),
                'type'=>4,
                'category'=>$info['category'],
                'toid'=>$info['toid'],
                'dateline'=>time(),
            );
            $ntModel->add($notice);
            $baseinfoModel->updateone(array(),$info['touid'],array('nfcount'=>'nfcount + 1'));
        }
        return returnData(1,'转载成功');

    }

    /**
     * 日志点赞
     */
    public function upclickAction(){
        $bid = $this->bid;
        $blogUpClickModel = new SnsBlogUpClick();
        //验证重复性
        $checked = $blogUpClickModel->checkclicked($this->uid,$bid);
        if($checked==true){
            return returnData(0,'您已经赞过了');
        }
        $data = array(
            'uid'=>$this->uid,
            'bid'=>$bid,
            'dateline'=>time()
        );
        $upck = $blogUpClickModel->addredislist($data);

        if($upck){
            //获取一条日志详情
            $blogModel = new SnsBlogModel();
            $blog = $blogModel->getlist(array('bid'=>$bid));
            $info['touid'] = $blog[0]['uid'];
            $info['title'] = $blog[0]['title'];
            $info['toid'] = $blog[0]['bid'];
            $info['category'] = $blog[0]['iszhuan'] == 1 ? 4 :2;
            if($info['touid'] != $this->uid){
                //发布一条通知
                $ntModel = new SnsNoticeModel();
                //发布一条通知
                $notice = array(
                    'fromuid'=>	$this->uid,
                    'touid'=>$info['touid'],
                    'message'=>json_encode(
                        array(
                            'fid'=>0
                        )
                    ),
                    'type'=>3,
                    'category'=>$info['category'],
                    'toid'=>$info['toid'],
                    'dateline'=>time(),
                );
                $ntModel->add($notice);
                //更新通知数
                $baseModel = new SnsBaseinfoModel();
                $baseModel->updateone(array(),$info['touid'],array('nzcount'=>'nzcount + 1'));
            }

            return returnData(1,'点赞成功');
        }else{
            return returnData(0,'点赞失败');
        }
    }
    /**
     * 最近看过该篇日志处理
     */
    protected function lastvisit($user,$blog){
        $redis = Ebh()->cache->getRedis();
        $visitlist = $redis->get('blog_visit_list_'.$blog['bid']);
        $visitarr = unserialize($visitlist);
        if(!empty($visitlist)){
            if($user['uid']!=$blog['uid']){
                foreach($visitarr as $key => $visit ){
                    if($visit['uid']==$user['uid']){
                        unset($visitarr[$key]);
                        break;
                    }
                }
                if(count($visitarr)>=20){
                    array_pop($visitarr);
                }
                array_push($visitarr, $user);
                $redis->set('blog_visit_list_'.$blog['bid'],serialize($visitarr));
            }
        }else{
            $visitarr = array(0=>$user);
            $redis->set('blog_visit_list_'.$blog['bid'],serialize($visitarr));
        }

    }
    private function getcate(){
        $blogModel = new SnsBlogModel();
        $cates = $blogModel->getcate(array('uid'=>$this->uid));
        return $cates;
    }
}