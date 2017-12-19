<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 16:05
 */
class ImageController  extends Controller {
    public function parameterRules(){
        return array(
            'addAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),//当前用户的UID
                'ip'  =>  array('name'=>'ip','require'=>true),
                'img'  =>  array('name'=>'img','require'=>true,'type'=>'array'),
            ),
            'delAction'   =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),//当前用户的UID
                'gid'  =>  array('name'=>'gid','require'=>true,'type'=>'int'),//图片ID
            ),
            'dealAction'    =>  array(
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),//当前用户的UID
                'images'  =>  array('name'=>'images','require'=>true,'type'=>'array'),
            )
        );
    }

    private function getKey($user){
        $uid = $user['uid'];
        $pwd = $user['password'];
        $ip = getip();
        $time = SYSTIME;
        $skey = "$pwd\t$uid\t$ip\t$time";
        $auth = authcode($skey, 'ENCODE');
        return $auth;
    }
    /**
     * 处理图片
     */
    public function dealAction(){
        $_UP = Ebh()->config->get('upconfig');
        Ebh()->helper->load('image');
        //获取每张图片尺寸,并裁剪
        $size = $this->getsize(count($this->images));
        $imageModel = new SnsImageModel();
        $images = $imageModel->getimgs($this->images);
        //调用crul处理图片裁剪
        $post_url = $_UP['snspic']['server'][0];
        $post_url = str_replace('snsupload/findex.html', '', $post_url);
        $post_url .= 'snsupload/cutimgs.html';

        $userModel = new UserModel();
        $userinfo = $userModel->getUserByUid($this->uid);

        $k = $this->getKey($userinfo);

        //一张图片单独处理
        if(count($images) == 1){
            $orisize = $images[0]['sizes'];
            $img[] = $_UP['snspic']['savepath'].$images[0]['path'];
            $res = $this->do_post($post_url, array('imglist'=>$img,'k'=>$k,'ismyroom'=>1));
            $result = json_decode($res,true);
            if(strpos($result[0]['path'], '886_477') !== false){
                //更新裁剪尺寸
                $setimg['sizes'] = $orisize.','.$size;
                $imageModel->update($setimg,$images[0]['gid']);
            }
        }else{
            foreach ($images as $key=>$value){
                $imgs[] = $_UP['snspic']['savepath'].$value['path'];
            }
            $result = $this->do_post($post_url, array('imglist'=>$imgs,'k'=>$k,'ismyroom'=>1));
            if(!empty($result)){
                $data = json_decode($result,true);
                foreach ($images as $key=>$value){
                    $orisize = $value['sizes'];
                    $imgs[] = $_UP['snspic']['savepath'].$value['path'];
                    //更新裁剪尺寸
                    $setimg[$key]['sizes'] = $orisize.','.$size;
                    $imageModel->update($setimg[$key],$value['gid']);
                }
            }
        }

        return true;
    }
    /**
     * 删除图片
     * @return mixed
     */
    public function delAction(){
        $imageModel = new  SnsImageModel();
        $row = $imageModel->getimgs(array($this->gid));
        $path = $row[0]['path'];
        $sizearr = explode(',',$row[0]['sizes']);
        $fileinfo = pathinfo($path);
        $file_extension = $fileinfo['extension'];
        $_UP = Ebh()->config->get('upconfig');
        if(!empty($sizearr)){
            foreach ($sizearr as $key=> $size){
                //忽略原图
                if($key == 0) continue;
                $thumpath = str_replace('.'.$file_extension,"_$size.".$file_extension,$path);
                $thumpath = $_UP['snspic']['savepath'].$thumpath;
                $delurl[] = $thumpath;
            }
        }
        $delurl[] = $_UP['snspic']['savepath'].$path;
        //删除数据库记录
        $isdel = $imageModel->delete(array('gid'=>$row[0]['gid'],'uid'=>$this->uid));
        $result['success'] = $isdel ? true : false;

        //调用crul删除图片
        $post_url = $_UP['snspic']['server'][0];
        $post_url = str_replace('snsupload/findex.html', '', $post_url);
        $post_url .= 'snsupload/delimg.html';
        do_post($post_url, $delurl);

        return $result;
    }

    /**
     * 添加图片
     * @return mixed
     */
    public function addAction(){
        $_UP = Ebh()->config->get('upconfig');
        $url = $_UP['snspic']['server'][0];
        $savepath = $_UP['snspic']['savepath'];
        $showpath = $_UP['snspic']['showpath'];
        //返回的图片信息
        $imgarr = $this->img;
        if(!empty($imgarr['path'])){
            $model = new SnsImageModel();
            //增加ip来源记录
            $ip = $this->ip;
            for($i=0;$i<count($imgarr['path']);$i++){
                //入库
                $param['uid'] = $this->uid;
                $param['path'] = str_replace($showpath, '', $imgarr['path'][$i]);
                $param['sizes'] = $imgarr['sizes'][$i];
                $param['dateline'] = SYSTIME;
                $param['ip'] = $ip;
                $addresult = $model->add($param);
                $gids[$i] = $addresult;
            }
            $result = $imgarr;
            $result['success'] = true;
            $result['gid'] = $gids;
        }else{
            $result['success'] = false;
        }
        return $result;
    }

    /**
     * 从内容中提取图片
     * @param $content
     */
    public function fetchImgs($content,$uid,$ip){
        preg_match_all("/<img[\s\S]*src=\"(((?!formula).)*)\"[\s\S]*\/>/U", $content, $matchs);
        $_UP = Ebh()->config->get('upconfig');
        $savepath = $_UP['snspic']['savepath'];
        $showpath = $_UP['snspic']['showpath'];
        $imgids = array();
        //过滤掉不是本地上传的
        if($matchs){
            foreach ($matchs[1] as $k=>$v){
                if(strpos($v,$showpath) === false){
                    unset($matchs[1][$k]);
                }
            }
        }
        if($matchs[1]){
            $userModel = new UserModel();
            $userinfo = $userModel->getUserByUid($uid);

            $k = $this->getKey($userinfo);
            $imgarr = $matchs[1];
            $size = $this->getsize(count($imgarr));
            $dateline = SYSTIME;
            $post_url = $_UP['snspic']['server'][0];
            $post_url = str_replace('snsupload/findex.html', '', $post_url);
            $post_url .= 'snsupload/cutimgs.html';
            $imageModel = new SnsImageModel();
            //增加ip来源记录
            $ip = $ip;
            //一张图片单独提取
            if(count($imgarr) == 1){
                //裁剪放接口处理
                $path = pathinfo($imgarr[0]);
                $extension = $path['extension'];
                $fullpath = substr($imgarr[0], strlen($showpath));
                $img[] = $savepath . $fullpath;
                $res = $this->do_post($post_url, array('imglist'=>$img,'k'=>$k,'ismyroom'=>1));
                if(!empty($res)){
                    $result = json_decode($res,true);
                    //入库
                    if(strpos($result[0]['path'], $size) !== false){
                        $param['sizes'] = $result[0]['orsize'].','.$size;
                    }else{
                        $param['sizes'] = $result[0]['path'];
                    }
                    $param['ip'] = $ip;
                    $param['uid'] = $uid;
                    $param['path'] = $fullpath;
                    $param['dateline'] = $dateline;
                    $imgids[] = $imageModel->add($param);
                }
            }else{
                foreach ($imgarr as $key=> $value){
                    if($key > 8) break;
                    //裁剪
                    $path = pathinfo($value);
                    $extension = $path['extension'];
                    $fullpath = substr($value, strlen($showpath));
                    $img[$key] = $savepath . $fullpath;
                    //入库
                    $param[$key]['uid'] = $uid;
                    $param[$key]['path'] = $fullpath;
                    $param[$key]['dateline'] = $dateline;
                    $param[$key]['sizes'] = $size;
                    $param[$key]['ip'] = $ip;
                }
                $result = $this->do_post($post_url, array('imglist'=>$img,'k'=>$k,'ismyroom'=>1));
                if(!empty($result)){
                    $data = json_decode($result,true);
                    foreach ($imgarr as $key=> $value){
                        if($key > 8) break;
                        $param[$key]['sizes'] = $data[$key]['orsize'].','.$param[$key]['sizes'];
                        $imgids[] = $imageModel->add($param[$key]);
                    }
                }
            }
        }

        return $imgids;
    }


    /**
     * 获取上传图片的尺寸
     */
    private function getsize($count){
        switch ($count){
            case 1:
                $size = '886_477';
                break;
            case 2:
                $size = '435_230';
                break;
            case 3:
                $size = '280_146';
                break;
            case 4:
                $size = '435_230';
                break;
            case 5:
                $size = '280_146';
                break;
            case 6:
                $size = '280_146';
                break;
            case 7:
                $size = '280_146';
                break;
            case 8:
                $size = '435_230';
                break;
            case 9:
                $size = '280_146';
                break;
            default:
                $size = '160_110';
        }
        return $size;
    }

    private function do_post($url, $data , $retJson = true){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //有文件上传
        if(!empty($data['upfile']) || !empty($data['Filedata'])){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data );
        }else{//无文件上传
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data) );
        }
        //  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data) );
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);
        curl_close($ch);
        if($retJson == false){
            $ret = json_decode($ret);
        }
        return $ret;
    }
}