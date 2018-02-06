<?php
/**
 * ebhservice.
 * Author: ckx
 * 作业相关
 */
class ExamController extends Controller{
    public $forumModel;
    public function init(){
        parent::init();
        
    }
    //参数规则
    public function parameterRules(){
        return array(
            'schCwListAction'   =>  array(
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int'),
            ),
			'courseNoteAction'   =>  array(
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'qid'  =>  array('name'=>'qid','require'=>true,'type'=>'int')
            ),
			'delUpanswerAction' =>  array(
                'qid'  =>  array('name'=>'qid','require'=>true,'type'=>'int'),
                'cwid'  =>  array('name'=>'cwid','require'=>true,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
                'upanswer'  =>  array('name'=>'upanswer','require'=>true,'type'=>'string')
            ),
			'updateAidAction' => array(
				'uid'  =>  array('name'=>'uid','require'=>true,'type'=>'int'),
				'aid'  =>  array('name'=>'aid','require'=>true,'type'=>'int'),
				'cwidlist'  =>  array('name'=>'cwidlist','require'=>true,'type'=>'array'),
				'qidlist'  =>  array('name'=>'qidlist','require'=>true,'type'=>'array'),
			)
        );
    }

    
    /**
     * 主观题列表
     * @return array
     */
    public function schCwListAction(){
        $scwmodel = new SchcoursewareModel();
		return $scwmodel->getCourseList(array('cwid'=>$this->cwid));
    }
	/**
     * 主观题答题信息
     * @return array
     */
    public function courseNoteAction(){
        $scwmodel = new SchcoursewareModel();
		return $scwmodel->getCourseNote(array('cwid'=>$this->cwid,'uid'=>$this->uid,'qid'=>$this->qid));
    }
	
	/*
	删除主观题上传答案
	*/
	public function delUpanswerAction(){
		$scwmodel = new SchcoursewareModel();
		return $scwmodel->delUpanswer(array('qid'=>$this->qid,'cwid'=>$this->cwid,'uid'=>$this->uid,'upanswer'=>$this->upanswer));
	}
	
	/*
	主观题更新aid，wap提交作业用到
	*/
	public function updateAidAction(){
		$scwmodel = new SchcoursewareModel();
		return $scwmodel->updateaid(array('uid'=>$this->uid,'aid'=>$this->aid,'cwidlist'=>$this->cwidlist,'qidlist'=>$this->qidlist));
	}
}