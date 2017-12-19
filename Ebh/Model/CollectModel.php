<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 17:24
 */
class CollectModel{
    /**
     *收藏列表的删除
     *@param  $uid,$crid,$folderid
     *
     */
    public function del($uid,$crid,$folderid){
        $where = array(
            'uid'=>$uid,
            'folderid'=>$folderid,
            'crid'=>$crid
        );
        return  Ebh()->db->delete('ebh_collects',$where);
    }
}