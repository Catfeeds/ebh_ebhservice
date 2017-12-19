<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:23
 */
class SnsVisitorModel{
    public function __construct(){
        $this->db = Ebh()->snsdb;
    }
    //访客设置
    public function visitor($user,$vuser=null){
        $cache = Ebh()->cache->getRedis();
        if(!empty($vuser)){
            //设置访客信息
            $key = 'visitor_'.$vuser['uid'].'_'.md5($vuser['uid']);
            $data = $cache->lrange($key,0,-1);
            $exist = -1;
            if(!empty($data)){
                //查找是否存在于访客列表
                foreach ($data as $k=> $value){
                    $v = unserialize($value);
                    if($v['uid'] == $user['uid']){
                        $exist = $k;
                        break;
                    }
                }
            }
            if($exist > -1){
                //如果存在先删除，后插入到表头
                $cache->lrem($key,$data[$k]);
                $cache->lpush($key,$data[$exist]);
            }else{
                $cachearr['uid'] = $user['uid'];
                $cachearr['name'] = !empty($user['realname']) ? $user['realname'] : $user['username'];
                $cachearr['face'] = getavater($user,'50_50');
                $cachearr['time'] = time();
                $value = serialize($cachearr);
                $cache->lpush($key,$value);
            }
            //超出部分裁剪
            $cache->ltrim($key,0,20);

            //空间访问次数+1
            $countkey = 'count_'.$vuser['uid'].'_'.md5($vuser['uid']);
            $cache->incr($countkey);
            //达到500更新到数据库
            $count = $cache->get($countkey);
            if($count > 500){
                $setarr['viewsnum'] = "viewsnum+$count";
                $result = $this->db->update("ebh_sns_baseinfos",array(),array('uid'=>$vuser['uid']),$setarr);
                $cache->mset(array($countkey=>0));
            }
        }
    }
    //获取用户访客信息
    public function getvisitor($uid){
        if(empty($uid)) return false;
        $cache = Ebh()->cache->getRedis();
        $mykey = 'visitor_'.$uid.'_'.md5($uid);
        $mycountkey = 'count_'.$uid.'_'.md5($uid);
        $arr['list'] = $cache->lrange($mykey,0,20);
        $arr['visitornum'] = $cache->get($mycountkey);
        return $arr;
    }
}