<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:46
 */
class NoteController extends Controller{
    public function parameterRules(){
        return array(
            'listAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
            ),
            'setAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>true,'type'=>'int','min'=>1),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int','min'=>1),
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int','min'=>1),
                'ftext'  =>  array('name'=>'ftext','require'=>true),
            ),
        );
    }

    /**
     * 获取笔记列表
     * @return array
     */
    public function listAction(){
        $parameters['crid'] = $this->crid;
        $parameters['uid'] = $this->uid;
        $parameters['onlyText'] = 1;
        $noteModel = new NoteModel();

        $total = $noteModel->getCount($parameters);
        $pageClass  = new Page($total);
        $parameters['limit'] = $pageClass->firstRow.','.$pageClass->listRows;
        $list = $noteModel->getList($parameters);
        return returnData(1,'',array('total'=>$total,'list'=>$list));
    }

    /**
     * 设置笔记
     */
    public function setAction(){
        $parameters['crid'] = $this->crid;
        $parameters['uid'] = $this->uid;
        $parameters['cwid'] = $this->cwid;
        $noteModel = new NoteModel();
        $note = $noteModel->getNote($parameters);
        if($note){
            $res = $noteModel->updateNote(array(
                'noteid' =>  $note['noteid'],
                'ftext' =>  $this->ftext
            ));
        }else{
            $parameters['ftext'] = $this->ftext;
            $res = $noteModel->addNote($parameters);
        }
        if($res !== false){
            return returnData(1,'设置成功');
        }else{
            return returnData(0,'设置失败');
        }
    }
}