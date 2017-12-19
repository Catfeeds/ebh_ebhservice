<?php

/**
 * 直播信息
 * Class LiveinfoModel
 */
class LiveinfoModel{

    /**
     * @param $cwid
     * @param $data
     * 添加直播信息
     */
    public function addLiveInfo($cwid,$data){
        if($this->getLiveInfoByCwid($cwid)){
            return Ebh()->db->update('ebh_course_liveinfos',$data,array('cwid'=>$cwid));
        }else{
            $data['cwid'] = $cwid;
            return Ebh()->db->insert('ebh_course_liveinfos',$data);
        }
    }

    /**
     * 通过cwid获取直播信息
     * @param $cwid
     * @return mixed
     */
    public function getLiveInfoByCwid($cwid){
        $sql = "select cwid,liveid,type,httppullurl,hlspullurl,rtmppullurl,pushurl from ebh_course_liveinfos where cwid=".$cwid;
        $row = Ebh()->db->query($sql)->row_array();
        return $row;
    }
}