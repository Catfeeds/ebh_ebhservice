<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:48
 */
class NoteModel {
    /**
     * 获取日志列表
     * @param $parameters
     */
    public function getList($parameters){
        $sql = 'select n.noteid,n.uid,n.cwid,n.crid,n.ftext,n.fdateline,n.dateline,cw.logo,cw.title,cw.cwurl,cw.cwname from ebh_notes n left join ebh_coursewares cw on cw.cwid=n.cwid';
        $wherearr = array();
        if(isset($parameters['crid'])){
            $wherearr[] = 'n.crid='.$parameters['crid'];
        }
        if(isset($parameters['uid'])){
            $wherearr[] = 'n.uid='.$parameters['uid'];
        }
        if(isset($parameters['cwid'])){
            $wherearr[] = 'n.cwid='.$parameters['cwid'];
        }
        if(isset($parameters['onlyText'])){
            $wherearr[] = 'n.ftext!= \'\'';
        }

        $sql.= ' where '.implode(' and ',$wherearr);
        if(!empty($parameters['order'])){
            $sql.= ' order by '.$parameters['order'];
        }else{
            $sql.= ' order by fdateline desc,noteid desc';
        }
        if(!empty($parameters['limit'])) {
            $sql .= ' limit '. $parameters['limit'];
        }

        return Ebh()->db->query($sql)->list_array();
    }

    /**
     * 获取条数
     * @return mixed
     */
    public function getCount($parameters){
        $sql = 'select count(n.noteid) as count from ebh_notes n';
        $wherearr = array();
        if(isset($parameters['crid'])){
            $wherearr[] = 'n.crid='.$parameters['crid'];
        }
        if(isset($parameters['uid'])){
            $wherearr[] = 'n.uid='.$parameters['uid'];
        }
        if(isset($parameters['cwid'])){
            $wherearr[] = 'n.cwid='.$parameters['cwid'];
        }
        if(isset($parameters['onlyText'])){
            $wherearr[] = 'n.ftext!= \'\'';
        }

        $sql.= ' where '.implode(' and ',$wherearr);

        $count = Ebh()->db->query($sql)->row_array();
        return $count['count'];
    }

    /**
     * 获取单条笔记
     * @param $parameters
     * @return mixed
     */
    public function getNote($parameters){
        $sql = 'select n.noteid,n.uid,n.cwid,n.crid,n.ftext,n.fdateline,n.dateline from ebh_notes n';
        $wherearr = array();
        if(isset($parameters['noteid'])){
            $wherearr[] = 'n.noteid='.$parameters['noteid'];
        }
        if(isset($parameters['crid'])){
            $wherearr[] = 'n.crid='.$parameters['crid'];
        }
        if(isset($parameters['uid'])){
            $wherearr[] = 'n.uid='.$parameters['uid'];
        }
        if(isset($parameters['cwid'])){
            $wherearr[] = 'n.cwid='.$parameters['cwid'];
        }
        $sql.= ' where '.implode(' and ',$wherearr);
        return Ebh()->db->query($sql)->row_array();
    }

    /**
     * 更新笔记
     * @param $parameters
     * @return bool
     */
    public function updateNote($parameters){
        if(!isset($parameters['noteid'])){
            return false;
        }
        $updateArr = array();
        if(isset($parameters['ftext'])){
            $updateArr['ftext'] = $parameters['ftext'];
        }
        $updateArr['fdateline'] = time();
        return Ebh()->db->update('ebh_notes',$updateArr,array('noteid'=>$parameters['noteid']));
    }

    /**
     * 添加笔记
     * @param $parameters
     * @return mixed
     */
    public function addNote($parameters){
        $addData = array();
        if(isset($parameters['uid'])){
            $addData['uid'] = $parameters['uid'];
        }
        if(isset($parameters['cwid'])){
            $addData['cwid'] = $parameters['cwid'];
        }
        if(isset($parameters['crid'])){
            $addData['crid'] = $parameters['crid'];
        }
        if(isset($parameters['ftext'])){
            $addData['ftext'] = $parameters['ftext'];
        }
        $addData['fdateline'] = time();
        $addData['dateline'] = time();

        return Ebh()->db->insert('ebh_notes',$addData);
    }
}