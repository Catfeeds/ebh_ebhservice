<?php
/**
 * 课程
 */
class CourseController extends Controller{
    public $spmodel;
    public $sortmodel;
    public $foldermodel;
    public $pimodel;
    public function init(){
        $this->spmodel = new PaypackageModel();
        $this->sortmodel = new PaysortModel();
        $this->foldermodel = new FolderModel();
        $this->pimodel = new PayitemModel();
        parent::init();

    }
    public function parameterRules(){
        return array(
            'courseListAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'roominfo'  =>  array('name'=>'roominfo','require'=>TRUE,'type'=>'array'),
                'q'  =>  array('name'=>'q','type'=>'string'),
                'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
                'page' =>	array('name'=>'page','default'=>1,'type'=>'int'),
                'issimple' =>	array('name'=>'issimple','type'=>'int'),
                'folderids' => array('name'=>'folderids','default'=>'','type'=>'string'),
                'itemids' => array('name'=>'itemids','default'=>'','type'=>'string')
            ),
            'courseDetailAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','require'=>TRUE,'type'=>'int'),
            ),
            'payitemDetailAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','require'=>TRUE,'type'=>'int'),
            ),
            'editAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>TRUE,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','require'=>TRUE,'type'=>'int'),
                'roominfo'  =>  array('name'=>'roominfo','require'=>TRUE,'type'=>'array'),
                'foldername'  =>  array('name'=>'foldername','require'=>TRUE,'type'=>'string'),
                'summary'  =>  array('name'=>'summary','default'=>'','type'=>'string'),
                'speaker'  =>  array('name'=>'speaker','default'=>'','type'=>'string'),
                'img'  =>  array('name'=>'img','default'=>'','type'=>'string'),
                'detail'  =>  array('name'=>'detail','default'=>'','type'=>'string'),
                'fprice'  =>  array('name'=>'fprice','default'=>0,'type'=>'int'),
                'iprice'  =>  array('name'=>'iprice','default'=>0,'type'=>'int'),
                'isimonthday'  =>  array('name'=>'isimonthday','default'=>0,'type'=>'int'),
                'imonth'  =>  array('name'=>'imonth','default'=>0,'type'=>'int'),
                'iday'  =>  array('name'=>'iday','default'=>0,'type'=>'int'),
                'power'  =>  array('name'=>'power','default'=>0,'type'=>'int'),
                'playmode'  =>  array('name'=>'playmode','default'=>FALSE,'type'=>'boolean'),
                'creditmode'  =>  array('name'=>'creditmode','default'=>0,'type'=>'int'),
                'view_mode'  =>  array('name'=>'view_mode','default'=>0,'type'=>'int'),
                'showmode'  =>  array('name'=>'showmode','default'=>0,'type'=>'int'),
                'iscredit'  =>  array('name'=>'iscredit','default'=>FALSE,'type'=>'boolean'),
                'credit'  =>  array('name'=>'credit','default'=>0,'type'=>'float'),
                'isremind'  =>  array('name'=>'isremind','default'=>FALSE,'type'=>'boolean'),
                'remindarr'  =>  array('name'=>'remindarr','default'=>array(),'type'=>'array'),
                'credittime'  =>  array('name'=>'credittime','default'=>0,'type'=>'int'),
                'cwcredit'  =>  array('name'=>'cwcredit','default'=>0,'type'=>'float'),
                'cwpercredit'  =>  array('name'=>'cwpercredit','default'=>0,'type'=>'float'),
                'examcredit'  =>  array('name'=>'examcredit','default'=>0,'type'=>'float'),
                'exampercredit'  =>  array('name'=>'exampercredit','default'=>0,'type'=>'float'),
                'pid'  =>  array('name'=>'pid','default'=>0,'type'=>'int'),
                'sid'  =>  array('name'=>'sid','default'=>0,'type'=>'int'),
                'grade'  =>  array('name'=>'grade','default'=>0,'type'=>'int'),
                'introtype' => array('name'=>'introtype', 'type'=>'int','default'=>0),
                'attachment' => array('name'=>'attachment','type'=>'array'),
                'slides' => array('name'=>'slides','type'=>'array', 'default' => array()),
				'limitnum' => array('name'=>'limitnum', 'type'=>'int','default'=>0),
				'islimit' => array('name'=>'islimit', 'type'=>'int','default'=>0),
            ),
            'editzjdlrAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>TRUE,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','require'=>TRUE,'type'=>'int'),
                'roominfo'  =>  array('name'=>'roominfo','require'=>TRUE,'type'=>'array'),
                'foldername'  =>  array('name'=>'foldername','require'=>TRUE,'type'=>'string'),
                'summary'  =>  array('name'=>'summary','default'=>'','type'=>'string'),
                'speaker'  =>  array('name'=>'speaker','default'=>'','type'=>'string'),
                'img'  =>  array('name'=>'img','default'=>'','type'=>'string'),
                'detail'  =>  array('name'=>'detail','default'=>'','type'=>'string'),
                'fprice'  =>  array('name'=>'fprice','default'=>0,'type'=>'int'),
                'iprice'  =>  array('name'=>'iprice','default'=>0,'type'=>'int'),
                'isimonthday'  =>  array('name'=>'isimonthday','default'=>0,'type'=>'int'),
                'imonth'  =>  array('name'=>'imonth','default'=>0,'type'=>'int'),
                'iday'  =>  array('name'=>'iday','default'=>0,'type'=>'int'),
                'power'  =>  array('name'=>'power','default'=>0,'type'=>'int'),
                'playmode'  =>  array('name'=>'playmode','default'=>FALSE,'type'=>'boolean'),
                'creditmode'  =>  array('name'=>'creditmode','default'=>0,'type'=>'int'),
                'view_mode'  =>  array('name'=>'view_mode','default'=>0,'type'=>'int'),
                'showmode'  =>  array('name'=>'showmode','default'=>0,'type'=>'int'),
                'iscredit'  =>  array('name'=>'iscredit','default'=>FALSE,'type'=>'boolean'),
                'credit'  =>  array('name'=>'credit','default'=>0,'type'=>'int'),
                'isremind'  =>  array('name'=>'isremind','default'=>FALSE,'type'=>'boolean'),
                'remindarr'  =>  array('name'=>'remindarr','default'=>array(),'type'=>'array'),
                'credittime'  =>  array('name'=>'credittime','default'=>0,'type'=>'int'),
                'coursecredit'  =>  array('name'=>'coursecredit','default'=>0,'type'=>'int'),
                'examcredit'  =>  array('name'=>'examcredit','default'=>0,'type'=>'int'),
                'pid'  =>  array('name'=>'pid','default'=>0,'type'=>'int'),
                'sid'  =>  array('name'=>'sid','default'=>0,'type'=>'int'),
                'grade'  =>  array('name'=>'grade','default'=>0,'type'=>'int'),
                'introtype' => array('name'=>'introtype', 'type'=>'int','default'=>0),
                'attachment' => array('name'=>'attachment','type'=>'array'),
                'slides' => array('name'=>'slides','type'=>'array', 'default' => array())
            ),
            'addAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>TRUE,'type'=>'int'),
                'roominfo'  =>  array('name'=>'roominfo','require'=>TRUE,'type'=>'array'),
                'foldername'  =>  array('name'=>'foldername','require'=>TRUE,'type'=>'string'),
                'summary'  =>  array('name'=>'summary','default'=>'','type'=>'string'),
                'speaker'  =>  array('name'=>'speaker','default'=>'','type'=>'string'),
                'img'  =>  array('name'=>'img','default'=>'','type'=>'string'),
                'detail'  =>  array('name'=>'detail','default'=>'','type'=>'string'),
                'fprice'  =>  array('name'=>'fprice','default'=>0,'type'=>'int'),
                'iprice'  =>  array('name'=>'iprice','default'=>0,'type'=>'int'),
                'isimonthday'  =>  array('name'=>'isimonthday','default'=>0,'type'=>'int'),
                'imonth'  =>  array('name'=>'imonth','default'=>0,'type'=>'int'),
                'iday'  =>  array('name'=>'iday','default'=>0,'type'=>'int'),
                'power'  =>  array('name'=>'power','default'=>0,'type'=>'int'),
                'playmode'  =>  array('name'=>'playmode','default'=>FALSE,'type'=>'boolean'),
                'creditmode'  =>  array('name'=>'creditmode','default'=>0,'type'=>'int'),
                'view_mode'  =>  array('name'=>'view_mode','default'=>0,'type'=>'int'),
                'showmode'  =>  array('name'=>'showmode','default'=>0,'type'=>'int'),
                'iscredit'  =>  array('name'=>'iscredit','default'=>FALSE,'type'=>'boolean'),
                'credit'  =>  array('name'=>'credit','default'=>0,'type'=>'float'),
                'isremind'  =>  array('name'=>'isremind','default'=>FALSE,'type'=>'boolean'),
                'remindarr'  =>  array('name'=>'remindarr','default'=>array(),'type'=>'array'),
                'credittime'  =>  array('name'=>'credittime','default'=>0,'type'=>'int'),
                'cwcredit'  =>  array('name'=>'cwcredit','default'=>0,'type'=>'float'),
                'cwpercredit'  =>  array('name'=>'cwpercredit','default'=>0,'type'=>'float'),
                'examcredit'  =>  array('name'=>'examcredit','default'=>0,'type'=>'float'),
                'exampercredit'  =>  array('name'=>'exampercredit','default'=>0,'type'=>'float'),
                'pid'  =>  array('name'=>'pid','default'=>0,'type'=>'int'),
                'sid'  =>  array('name'=>'sid','default'=>0,'type'=>'int'),
                'grade'  =>  array('name'=>'grade','default'=>0,'type'=>'int'),
                'introtype' => array('name'=>'introtype', 'type'=>'int','default'=>0),
                'attachment' => array('name'=>'attachment','type'=>'array'),
                'slides' => array('name'=>'slides','type'=>'array', 'default' => array()),
				'limitnum' => array('name'=>'limitnum', 'type'=>'int','default'=>0),
				'islimit' => array('name'=>'islimit', 'type'=>'int','default'=>0),
            ),
            'addzjdlrAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'uid'  =>  array('name'=>'uid','require'=>TRUE,'type'=>'int'),
                'roominfo'  =>  array('name'=>'roominfo','require'=>TRUE,'type'=>'array'),
                'foldername'  =>  array('name'=>'foldername','require'=>TRUE,'type'=>'string'),
                'summary'  =>  array('name'=>'summary','default'=>'','type'=>'string'),
                'speaker'  =>  array('name'=>'speaker','default'=>'','type'=>'string'),
                'img'  =>  array('name'=>'img','default'=>'','type'=>'string'),
                'detail'  =>  array('name'=>'detail','default'=>'','type'=>'string'),
                'fprice'  =>  array('name'=>'fprice','default'=>0,'type'=>'int'),
                'iprice'  =>  array('name'=>'iprice','default'=>0,'type'=>'int'),
                'isimonthday'  =>  array('name'=>'isimonthday','default'=>0,'type'=>'int'),
                'imonth'  =>  array('name'=>'imonth','default'=>0,'type'=>'int'),
                'iday'  =>  array('name'=>'iday','default'=>0,'type'=>'int'),
                'power'  =>  array('name'=>'power','default'=>0,'type'=>'int'),
                'playmode'  =>  array('name'=>'playmode','default'=>FALSE,'type'=>'boolean'),
                'creditmode'  =>  array('name'=>'creditmode','default'=>0,'type'=>'int'),
                'view_mode'  =>  array('name'=>'view_mode','default'=>0,'type'=>'int'),
                'showmode'  =>  array('name'=>'showmode','default'=>0,'type'=>'int'),
                'iscredit'  =>  array('name'=>'iscredit','default'=>FALSE,'type'=>'boolean'),
                'credit'  =>  array('name'=>'credit','default'=>0,'type'=>'int'),
                'isremind'  =>  array('name'=>'isremind','default'=>FALSE,'type'=>'boolean'),
                'remindarr'  =>  array('name'=>'remindarr','default'=>array(),'type'=>'array'),
                'credittime'  =>  array('name'=>'credittime','default'=>0,'type'=>'int'),
                'coursecredit'  =>  array('name'=>'coursecredit','default'=>0,'type'=>'int'),
                'examcredit'  =>  array('name'=>'examcredit','default'=>0,'type'=>'int'),
                'pid'  =>  array('name'=>'pid','default'=>0,'type'=>'int'),
                'sid'  =>  array('name'=>'sid','default'=>0,'type'=>'int'),
                'grade'  =>  array('name'=>'grade','default'=>0,'type'=>'int'),
                'introtype' => array('name'=>'introtype', 'type'=>'int','default'=>0),
                'attachment' => array('name'=>'attachment','type'=>'array'),
                'slides' => array('name'=>'slides','type'=>'array', 'default' => array())
            ),
            'introduceSaveAction' =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','require'=>TRUE,'type'=>'int'),
                'introduce'  =>  array('name'=>'introduce','require'=>TRUE,'type'=>'string'),
            ),
            'cwListAction' =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','require'=>TRUE,'type'=>'string'),
                'pagesize'  =>  array('name'=>'pagesize','default'=>20,'type'=>'int'),
                'page'  =>  array('name'=>'page','default'=>1,'type'=>'int'),
                'issimple'  =>  array('name'=>'issimple','type'=>'int'),
                'starttime'  =>  array('name'=>'starttime','type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','type'=>'int'),
                'videoonly'  =>  array('name'=>'videoonly','detaul'=>0,'type'=>'int'),
                's' => array('name' => 's', 'type' => 'string'),
                'sid' => array('name' => 'sid', 'type' => 'int')
            ),
            'teacherListAction' =>  array(
                'uids' => array('name'=>'uids','require'=>TRUE,'type'=>'string'),
            ),
            'filterTeacherAction' =>  array(
                'crid' => array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'tids' => array('name'=>'tids','require'=>TRUE,'type'=>'string'),
            ),
            'chooseTeacherAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'tids'  =>  array('name'=>'tids','require'=>TRUE,'type'=>'string'),
                'folderid'  =>  array('name'=>'folderid','require'=>TRUE,'type'=>'int'),
            ),
            'itemListAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'pids'  =>  array('name'=>'pids','require'=>TRUE,'type'=>'string'),
                'issimple'  =>  array('name'=>'issimple','type'=>'int'),
            ),
            'classCourseListAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'classids'  =>  array('name'=>'classids','require'=>TRUE,'type'=>'string'),
            ),
            'courseTeacherListAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'folderids'  =>  array('name'=>'folderids','require'=>TRUE,'type'=>'string'),
            ),
            'studyListAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'folderids'  =>  array('name'=>'folderids','require'=>TRUE,'type'=>'string'),
                'starttime'  =>  array('name'=>'starttime','detaul'=>0,'type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','detaul'=>0,'type'=>'int'),
            ),
            'saveClassCourseAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'classid'  =>  array('name'=>'classid','require'=>TRUE,'type'=>'int'),
                'folderids'  =>  array('name'=>'folderids','default'=>array(),'type'=>'array'),
                'uids'  =>  array('name'=>'uids','default'=>array(),'type'=>'array'),
                'isclear'  =>  array('name'=>'isclear','default'=>0,'type'=>'int'),
            ),
            'cwCountAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','require'=>TRUE,'type'=>'string'),
                'needgroup'  =>  array('name'=>'needgroup','default'=>0,'type'=>'string'),
                'starttime'  =>  array('name'=>'starttime','type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','type'=>'int'),
            ),
            'reviewCountAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','require'=>TRUE,'type'=>'string'),
                'starttime'  =>  array('name'=>'starttime','type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','type'=>'int'),
            ),
            'zanCountAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','require'=>TRUE,'type'=>'string'),
                'starttime'  =>  array('name'=>'starttime','type'=>'int'),
                'endtime'  =>  array('name'=>'endtime','type'=>'int'),
            ),
            'delAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'folderid'  =>  array('name'=>'folderid','require'=>TRUE,'type'=>'int'),
                'isschool'  =>  array('name'=>'isschool','require'=>TRUE,'type'=>'int'),
            ),
            'cwDetailAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'cwid'  =>  array('name'=>'cwid','require'=>TRUE,'type'=>'int'),
            ),
            'cwDelAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'cwid'  =>  array('name'=>'cwid','require'=>TRUE,'type'=>'int'),
            ),
            'studyDetailAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'cwid'  =>  array('name'=>'cwid','require'=>TRUE,'type'=>'int'),
                'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
                'page' =>	array('name'=>'page','default'=>1,'type'=>'int'),
            ),
            'studyUsersAction'   =>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'uids'  =>  array('name'=>'uids','require'=>TRUE,'type'=>'string'),
            ),
            'hotCourseListAction' => array(
                'crid' => array('name' => 'crid', 'require' => TRUE, 'type' => 'int'),
                'num' => array('name' => 'num', 'require' => TRUE, 'type' => 'int')
            ),
            'saveClassCourseStatsAction' => array(
                'classid'  =>  array('name'=>'classid','require'=>TRUE,'type'=>'int'),
                'itemids'  =>  array('name'=>'itemids','default'=>array(),'type'=>'array'),
                'isclear'  =>  array('name'=>'isclear','default'=>0,'type'=>'int'),
            ),
            'statsClassAction'	=>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
                'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
                'page' =>	array('name'=>'page','default'=>1,'type'=>'int'),
            ),
            'statsClassCourseAction'	=>  array(
                'crid'  =>  array('name'=>'crid','require'=>TRUE,'type'=>'int'),
            ),
            'rankCoursewareInSectionAction' => array(
                'cwid' => array('name' => 'cwid', 'require' => TRUE, 'type' => 'int'),
                'crid' => array('name' => 'crid', 'require' => TRUE, 'type' => 'int'),
                'step' => array('name' => 'step', 'require' => TRUE, 'type' => 'int')
            ),
            'rankCourseAction' => array(
                'folderid' => array('name' => 'folderid', 'require' => TRUE, 'type' => 'int'),
                'scope' => array('name' => 'scope', 'require' => TRUE, 'type' => 'int'),
                'crid' => array('name' => 'crid', 'require' => TRUE, 'type' => 'int'),
                'step' => array('name' => 'step', 'require' => TRUE, 'type' => 'int')
            ),
            'batchRankCoursewaresAction' => array(
                'crid' => array('name' => 'crid', 'type' => 'int', 'require' => true),
                'ranks' => array('name' => 'ranks', 'type' => 'array', 'require' => true),
                'fromexcel' => array('name' => 'fromexcel', 'type' => 'int', 'default' => 0),
                'folderid' => array('name' => 'folderid', 'type' => 'int', 'default' => 0)
            ),
            'batchRankCoursesAction' => array(
                'crid' => array('name' => 'crid', 'type' => 'int', 'require' => true),
                'ranks' => array('name' => 'ranks', 'type' => 'array', 'require' => true),
                'fromexcel' => array('name' => 'fromexcel', 'type' => 'int', 'default' => 0)
            ),
            'importCourseRankTplAction' => array(
                'crid' => array('name' => 'crid', 'type' => 'int', 'require' => true),
                'pid' => array('name' => 'pid', 'type' => 'int', 'default' => 0),
                'sid' => array('name' => 'sid', 'type' => 'int')
            ),
            'importCoursewareRankTplAction' => array(
                'crid' => array('name' => 'crid', 'type' => 'int', 'require' => true),
                'folderid' => array('name' => 'folderid', 'type' => 'int', 'requre' => true),
                'sid' => array('name' => 'sid', 'type' => 'int'),
                's' => array('name' => 's', 'type' => 'string', 'default' => '')
            ),
            'unfitCourseListAction' => array(
                'crid' => array('name' => 'crid', 'type' => 'int', 'require' => true),
                'q'  =>  array('name'=>'q','type'=>'string'),
                'pagesize' =>	array('name'=>'pagesize','default'=>20,'type'=>'int'),
                'page' =>	array('name'=>'page','default'=>1,'type'=>'int'),
            ),
            'doStudyCreditAction' => array(
                'crid' => array('name' => 'crid', 'type' => 'int', 'require' => true),
                'folderid' => array('name' => 'folderid', 'type' => 'int', 'requre' => true),
            ),
            'singleValueableCourseListAction' => array('crid' => array('name' => 'crid', 'type' => 'int', 'require' => true)),
            'courseCategoryAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            'schCourseAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'sourcecrid' => array(
                    'name' => 'sourcecrid',
                    'type' => 'int',
                    'require' => true
                ),
                'pid' => array(
                    'name' => 'pid',
                    'type' => 'int',
                    'default' => 0
                ),
                'sid' => array(
                    'name' => 'sid',
                    'type' => 'int'
                ),
                'search' => array(
                    'name' => 'search',
                    'type' => 'string'
                ),
                'page' => array(
                    'name' => 'page',
                    'type' => 'int'
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int'
                )
            ),
            'getClassAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                )
            ),
            'getCourseDataAction' => array(
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require' => true
                ),
                'q' => array(
                    'name' => 'q',
                    'type' => 'string',

                ),
                'pid' => array(
                    'name' => 'pid',
                    'type' => 'int',

                ),
                'orderBy' => array(
                    'name' => 'orderBy',
                    'type' => 'int'
                ),
                'curr' => array(
                    'name' => 'curr',
                    'type' => 'int',
                    'require' => true
                ),
                'listRows' => array(
                    'name' => 'listRows',
                    'type' => 'int',
                    'require' => true
                )
            ),
            'getTeacherHeadAction' => array(
                'folderid' => array(
                    'name' => 'folderid',
                    'type' => 'string',
                    'require' => true
                )
            ),
            'courseStudentSortAction'=>array(
                'folderid'=>array(
                    'name'=>'folderid',
                    'type'=>'int',
                    'require'=>true
                ),
                'orderBy'=>array(
                    'name'=>'orderBy',
                    'type'=>'int'
                ),  'crid'=>array(
                    'name'=>'crid',
                    'type'=>'int',
                    'require'=>true
                ),
                'school_type'=>array(
                    'name'=>'school_type',
                    'type'=>'int',
                    'require'=>true
                )
            ),
            'getCoreClassAction'=>array(
                'attach'=>array(
                    'name'=>'attach',
                    'type'=>'string',
                    'require'=>true
                )
            ),
            'fileCountAction' => array(

                'folderid' => array(
                    'name' => 'folderid',
                    'type' => 'int',
                    'require'=>true
                ),
                'crid' => array(
                    'name' => 'crid',
                    'type' => 'int',
                    'require'=>true
                ),
            )  ,
            'getFolderidMsgAction' => array(

                'folderids' => array(
                    'name' => 'folderids',
                    'type' => 'string',
                    'require'=>true
                ),
                'crid'=>array(
                    'name'=>'crid',
                    'type'=>'int',
                    'require'=>true
                )
            ),
            'getCoursesMsgAction'=>array(
                'cwids'=>array(
                    'name'=>'cwids',
                    'type'=>'string',
                    'require'=>true
                )
            ),
            'cwlengthCountToFolderidAction'=>array(
                'folderids'=>array(
                    'name'=>'folderids',
                    'type'=>'string',
                    'require'=>true
                ),
                'crid'=>array(
                    'name'=>'crid',
                    'type'=>'int',
                    'require'=>true
                )
            )

        );
    }
    /*
    课程列表
    */
    public function courseListAction() {
        $roominfo = $this->roominfo;
        $crid = $this->crid;
        $foldermodel = $this->foldermodel;
        $q = $this->q;
        $param['pagesize'] = $this->pagesize;
        $param['page'] = $this->page;
        $issimple = $this->issimple;
        if ($roominfo['isschool'] == 7) {
            $condition = array();
            $condition['q'] = $q;
            $condition['folderids'] = $this->folderids;
            $condition['itemids'] = $this->itemids;
            if (!empty($roominfo['pid'])) {
                $condition['pid'] = intval($roominfo['pid']);
                if (isset($roominfo['sid'])) {
                    $condition['sid'] = intval($roominfo['sid']);
                }
            }
            $courselist = $foldermodel->getFolderWithItem($crid, $condition);
            $coursecount = $foldermodel->getFolderCountWithItem($crid, $condition);

        } else {
            $param['crid'] = $crid;
            $param['nosubfolder'] = 1;
            $param['order'] = 'f.displayorder asc,f.folderid desc';
            $param['q'] = $q;
            if(empty($issimple)){
                $courselist = $foldermodel->getFolderChapterList($param);
            } else {
                $courselist = $foldermodel->getFolderList($param);
            }
            $coursecount = $foldermodel->getCount($param);

        }
        if($coursecount == 0 && count($courselist) ==0)
            return array('courselist'=>array(),'coursecount'=>0);
        $mychaptermodel = new MychapterModel();
        if (!empty($courselist) && empty($issimple)) {
            foreach($courselist as $k=>$course){
                //获取版本id
                if(empty($course['chapterpath']))
                    $courselist[$k]['versionid'] = 0;
                else{
                    $temparr = explode('/', $course['chapterpath']);
                    $courselist[$k]['versionid'] = $temparr[1];
                }
                $courselist[$k]['chapterfullname'] = !empty($course['chapterpath']) ? $mychaptermodel->getFullName($course['chapterpath']) : '';
            }
        }
        return array('courselist'=>$courselist,'coursecount'=>$coursecount);

    }

    /*
    服务项列表
    */
    public function itemListAction(){
        $power = '0';
        $itemparam = array('crid'=>$this->crid,'issimple'=>$this->issimple,'limit'=>1000,'pidlist'=>$this->pids,'displayorder'=>'s.sdisplayorder is null,sdisplayorder,i.pid,f.displayorder','power'=>$power);
        $itemlist = $this->pimodel->getItemFolderList($itemparam);
        return $itemlist;
    }
    /*
    获取课程详情，供编辑
    */
    public function courseDetailAction(){
        $folderid = $this->folderid;
        $coursedetail = $this->foldermodel->getFolderById($folderid);
        if (!empty($coursedetail)) {
            //开场内容
            //视频开场内容
            $attachmentModel = new AttachmentModel();
            $source = $attachmentModel->getIntro($coursedetail['attid']);
            if (!empty($source)) {
                $coursedetail['intro'] = $source;
            }
            //图片开场内容
            $coursedetail['slides'] = json_decode($coursedetail['slides'], true);
            if (!empty($coursedetail['slides'])) {
                $coursedetail['slides'] = array_values($coursedetail['slides']);
            } else {
                $coursedetail['slides'] = array();
            }
        }
        return $coursedetail;
    }
    /*
    获取课程的服务项信息
    */
    public function payitemDetailAction(){
        $payitem = $this->pimodel->getDefineItemByFolderid($this->folderid,$this->crid);
        return $payitem;
    }

    /*
    课程介绍保存
    */
    public function introduceSaveAction(){
        $param['introduce'] = $this->introduce;
        $param['folderid'] = $this->folderid;
        $param['crid'] = $this->crid;
        $res = $this->foldermodel->editCourse($param);
        return $res;
    }

    /*
    删除课程
    */
    public function delAction(){
        $param['crid'] = $this->crid;
        $param['folderid'] = $this->folderid;
        $delitem = $this->isschool == 7;
        $ret = $this->foldermodel->deleteCourse($param, $delitem);
        //触发课程排序
        $folderModel = new FolderModel();
        $folderModel->batchRankCourses(array(), $this->crid);
        return $ret;
    }

    /*
    课程的课件列表
    */
    public function cwListAction(){
        $param['folderid'] = $this->folderid;
        $param['crid'] = $this->crid;
        $param['pagesize'] = $this->pagesize;
        $param['page'] = $this->page;
        $param['issimple'] = $this->issimple;
        $param['starttime'] = $this->starttime;
        $param['endtime'] = $this->endtime;
        $param['videoonly'] = $this->videoonly;
        if (!empty($this->s)) {
            $param['s'] = $this->s;
        }
        $cwmodel = new CoursewareModel();
        $cwlist = $cwmodel->getCwListByFolderid($param);

        if (isset($this->sid)) {
            $param['sid'] = intval($this->sid);
        }
        $cwcount = $cwmodel->getCwCountByFolderid($param);
        return array('cwlist'=>$cwlist,'cwcount'=>$cwcount);
    }

    /*
    课件数量(按课程分组)
    */
    public function cwCountAction(){
        $param['folderid'] = $this->folderid;
        $param['crid'] = $this->crid;
        $param['starttime'] = $this->starttime;
        $param['endtime'] = $this->endtime;
        $param['needgroup'] = $this->needgroup;
        $cwmodel = new CoursewareModel();
        return $cwmodel->getCwCountByFolderid($param);
    }
    /*
    评论数(按课程分组)
    */
    public function reviewCountAction(){
        $param['folderid'] = $this->folderid;
        $param['crid'] = $this->crid;
        $param['starttime'] = $this->starttime;
        $param['endtime'] = $this->endtime;

        $reviewmodel = new ReviewModel();
        return $reviewmodel->getReviewCountByFolderid($param);
    }
    /*
    评论数(按课程分组)
    */
    public function zanCountAction(){
        $param['folderid'] = $this->folderid;
        $param['crid'] = $this->crid;
        $param['starttime'] = $this->starttime;
        $param['endtime'] = $this->endtime;
        return $this->foldermodel->zanCount($param);
    }
    /*
    老师列表
    */
    public function teacherListAction(){
        $usermodel = new UserModel();
        $userlist = $usermodel->getUserByUids($this->uids);
        return $userlist;
    }

    /*
    编辑课程
    */
    public function editAction(){
        $ret = $this->doCourse(TRUE);
        //触发课程排序
        $folderModel = new FolderModel();
        $folderModel->batchRankCourses(array(), $this->crid);
        return $ret;
    }
    /*
    编辑课程zjdlr
    */
    public function editzjdlrAction(){
        $ret = $this->doCoursezjdlr(TRUE);
        //触发课程排序
        $folderModel = new FolderModel();
        $folderModel->batchRankCourses(array(), $this->crid);
        return $ret;
    }
    /*
    添加课程
    */
    public function addAction(){
        $ret = $this->doCourse(FALSE);
        //触发课程排序
        $folderModel = new FolderModel();
        $folderModel->batchRankCourses(array(), $this->crid);
        return $ret;
    }
    /*
    添加课程
    */
    public function addzjdlrAction(){
        $ret = $this->doCoursezjdlr(FALSE);
        //触发课程排序
        $folderModel = new FolderModel();
        $folderModel->batchRankCourses(array(), $this->crid);
        return $ret;
    }

    /*
    处理添加编辑课程
    */
    private function doCourse($isedit = FALSE){
        $roominfo = $this->roominfo;
        $param['crid'] = $roominfo['crid'];
        $param['uid'] = $this->uid;
        if($isedit){//仅编辑时有
            $folderid = $this->folderid;
            if(empty($folderid))
                return FALSE;
            $param['folderid'] = $folderid;
        } else {
            $roomfolder = $this->foldermodel->getFolderList(array('crid'=>$this->crid,'folderlevel'=>1));
            if (empty($roomfolder)) {
                //数据不完整，重新调整
                $ret = $this->foldermodel->initIntact($roominfo['crname'], $roominfo['crid'], $roominfo['uid']);
                if (empty($ret)) {
                    return FALSE;
                }
                $roomfolder = $this->foldermodel->getFolderList(array('crid'=>$this->crid,'folderlevel'=>1));
            }
            $param['folderpath'] = $roomfolder[0]['folderpath'];
            $displayorder = $this->foldermodel->getCurDisplayorder(array('crid'=>$this->crid,'folderlevel'=>2));
            if($displayorder == null)
                $param['displayorder'] = 200;
            else
                $param['displayorder'] = $displayorder - 1;
        }
        if($roominfo['isschool'] != 7){
            $param['grade'] = $this->grade;
        }
        $param['foldername'] = $this->foldername;
        $param['summary'] = $this->summary;
        $param['img'] = $this->img;
        $param['speaker'] = $this->speaker;
        $param['detail'] = $this->detail;
        $param['power'] = $this->power;
        $param['fprice'] = $this->fprice;
        $param['showmode'] = $this->showmode;//课件显示方式
        $updatecredit = TRUE;//是否更新学分数据
        if($roominfo['iscollege']) {	//如果是大学版，则处理学分等信息	//学分获取方式，如按学习进度，则获取课程和作业占比，按累计时长则设置累计需要时长
            if($this->iscredit){
                $credit = round($this->credit,1);	//学分
                $creditmode = intval($this->creditmode);
                if($creditmode == 1) {
                    $credittime = intval($this->credittime);
                    $credittime = $credittime * 60;	//换算成秒
                    $param['credittime'] = $credittime;
                } else {
                    $param['cwcredit'] = round($this->cwcredit,1);
                    $param['cwpercredit'] = round($this->cwpercredit,1);
                    $param['examcredit'] = round($this->examcredit,1);
                    $param['exampercredit'] = round($this->exampercredit,1);
                    // return $param;
                    if($credit != round($param['cwcredit']+$param['examcredit'],1) || $param['cwcredit']<$param['cwpercredit'] || $param['examcredit']<$param['exampercredit']){//课件,作业相加要等于总学分,单个课件/作业要不大于课件/作业学分
                        return FALSE;
                    }
                }
            } else {
                $credit = 0;
                $creditmode = 0;
                $param['cwcredit'] = 0;
                $param['cwpercredit'] = 0;
                $param['examcredit'] = 0;
                $param['exampercredit'] = 0;
            }
            if($isedit){//编辑,查看是否和上一次一样
                $thisfolder = $this->foldermodel->getFolderById($folderid,$roominfo['crid']);

                if($thisfolder['credit'] == $credit && $thisfolder['creditmode'] == $creditmode && $thisfolder['cwcredit'] == $param['cwcredit'] && $thisfolder['cwpercredit'] == $param['cwpercredit'] && $thisfolder['examcredit'] == $param['examcredit'] && $thisfolder['exampercredit'] == $param['exampercredit'] ){//与上次一样,或者修改时间过短|| (SYSTIME - $thisfolder['creditdate']<86400)
                    $updatecredit = FALSE;
                } else {
                    $param['creditdate'] = SYSTIME;
                }
            }

            $playmode = intval($this->playmode);
            $isremind = intval($this->isremind);
            $remindarr = $this->remindarr;

            $remindtimearr = array();
            $remindmsgarr = array();
            if(!empty($remindarr) && count($remindarr)>0) {
                foreach($remindarr as $remind) {
                    $remindtimearr[] = intval($remind['remindtime'])*60;
                    $remindmsgarr[] = $remind['remindmsg'];
                }
                $remindmsgstr = implode('#',$remindmsgarr);
                $remindtimestr = implode(',',$remindtimearr);
            }
            $param['updatecredit'] = $updatecredit;
            $param['credit'] = $credit;
            $param['creditmode'] = $creditmode;
            $param['playmode'] = $playmode;
            $param['isremind'] = $isremind;
            $param['remindtime'] = empty($remindtimestr)?'':$remindtimestr;
            $param['remindmsg'] = empty($remindmsgstr)?'':$remindmsgstr;
            $param['coursewarelogo'] = 1;
        }

        if($this->roominfo['isschool'] == 7){
            $pid = $this->pid;
            if($isedit){
                $payitem = $this->pimodel->getDefineItemByFolderid($folderid,$roominfo['crid']);
            }
            if(!empty($pid) && (!empty($payitem) || !$isedit)){//新版添加编辑课程,判断所选服务包/分类 是否还存在
                $check = $this->checkSp($roominfo);
                if($check['status'] != 1){
                    return $check;
                }
            }
        }
        Ebh()->db->set_con(0);
        if($isedit){
            $res = $this->foldermodel->editCourse($param);
            $ifolderid = $param['folderid'];
        } else {
            $res = $ifolderid = $folderid = $this->foldermodel->addFolder($param);
        }
        $introParams = array(
            'introtype' => $this->introtype
        );
        //图片开场内容
        $slides = array_filter($this->slides, function($slide) {
            return !empty($slide['src']) && isset($slide['interval']);
        });
        $slides = array_map(function($slide) {
            return array(
                'src' => $slide['src'],
                'interval' => max(0, intval($slide['interval']))
            );
        }, $slides);
        $slides = array_values($slides);
        $introParams['slides'] = json_encode($slides);
        //视频开场内容
        if (!empty($this->attachment['originalName']) && !empty($this->attachment['url'])
            && !empty($this->attachment['server']) && !empty($this->attachment['md5'])) {
            $attachment = array(
                'checksum' => trim($this->attachment['md5']),
                'url' => trim($this->attachment['url']),
                'title' => trim($this->attachment['originalName']),
                'filename' => trim($this->attachment['originalName']),
                'source' => $this->attachment['server']
            );
            if (isset($this->attachment['size'])) {
                $attachment['size'] = intval($this->attachment['size']);
            }
            if (isset($this->attachment['type'])) {
                $attachment['suffix'] = trim($this->attachment['type'], '.');
            }
            $attachmentModel = new AttachmentModel();
            $attachment['status'] = 1;
            $attid = $attachmentModel->add($param['uid'], $param['crid'], $attachment);
            if (!empty($attid)) {
                $introParams['attid'] = $attid;
            }
        } else {
            $introParams['attid'] = intval($this->attachment['attid']);
        }
        $introModel = new IntroModel();
        $introModel->set($ifolderid, $introParams);

        if(($res !== FALSE && !empty($payitem) || !$isedit && $folderid) && $roominfo['isschool'] == 7){//编辑或者添加课程后处理服务项
            // return 111;
            $res = $this->doPayitem($roominfo,$folderid,$isedit);
            if($res != FALSE) {
                $res = $folderid;
            }
        }
        if(!empty($updatecredit) && $isedit && $res !== FALSE){//学分改动，批量处理
            return array('status'=>$res,'folderid'=>$folderid);
            // $this->doStudyCredit($folderid,$roominfo['crid']);
        }
        return $res;
    }


    /*
    处理添加编辑课程zjdlr
    */
    private function doCoursezjdlr($isedit = FALSE){
        $roominfo = $this->roominfo;
        $param['crid'] = $roominfo['crid'];
        $param['uid'] = $this->uid;
        if($isedit){//仅编辑时有
            $folderid = $this->folderid;
            if(empty($folderid))
                return FALSE;
            $param['folderid'] = $folderid;
        } else {
            $roomfolder = $this->foldermodel->getFolderList(array('crid'=>$this->crid,'folderlevel'=>1));
            if (empty($roomfolder)) {
                //数据不完整，重新调整
                $ret = $this->foldermodel->initIntact($roominfo['crname'], $roominfo['crid'], $roominfo['uid']);
                if (empty($ret)) {
                    return false;
                }
                $roomfolder = $this->foldermodel->getFolderList(array('crid'=>$this->crid,'folderlevel'=>1));
            }
            $param['folderpath'] = $roomfolder[0]['folderpath'];
            $displayorder = $this->foldermodel->getCurDisplayorder(array('crid'=>$this->crid,'folderlevel'=>2));
            if($displayorder == null)
                $param['displayorder'] = 200;
            else
                $param['displayorder'] = $displayorder - 1;
        }
        if($roominfo['isschool'] != 7){
            $param['grade'] = $this->grade;
        }
        $param['foldername'] = $this->foldername;
        $param['summary'] = $this->summary;
        $param['img'] = $this->img;
        $param['speaker'] = $this->speaker;
        $param['detail'] = $this->detail;
        $param['power'] = $this->power;
        $param['fprice'] = $this->fprice;
        $param['showmode'] = $this->showmode;//课件显示方式
        if($roominfo['iscollege']) {	//如果是大学版，则处理学分等信息	//学分获取方式，如按学习进度，则获取课程和作业占比，按累计时长则设置累计需要时长
            if($this->iscredit){
                $credit = intval($this->credit);	//学分
                $creditmode = intval($this->creditmode);
                if($creditmode == 1) {
                    $credittime = intval($this->credittime);
                    $credittime = $credittime * 60;	//换算成秒
                    $param['credittime'] = $credittime;
                } else {
                    $coursecredit = intval($this->coursecredit);
                    if($coursecredit < 0)
                        $coursecredit = 0;
                    $examcredit = intval($this->examcredit);
                    if($examcredit < 0)
                        $examcredit = 0;
                    if(($coursecredit + $examcredit) < 100) {
                        $coursecredit = 100 - $examcredit;
                    }
                    $param['creditrule'] = $coursecredit.':'.$examcredit;
                    // $param['creditrule'] = $this->creditrule;
                }
            } else {
                $credit = 0;
                $creditmode = 0;
            }

            $playmode = intval($this->playmode);
            $isremind = intval($this->isremind);
            $remindarr = $this->remindarr;

            $remindtimearr = array();
            $remindmsgarr = array();
            if(!empty($remindarr) && count($remindarr)>0) {
                foreach($remindarr as $remind) {
                    $remindtimearr[] = intval($remind['remindtime'])*60;
                    $remindmsgarr[] = $remind['remindmsg'];
                }
                $remindmsgstr = implode('#',$remindmsgarr);
                $remindtimestr = implode(',',$remindtimearr);
            }

            $param['credit'] = $credit;
            $param['creditmode'] = $creditmode;
            $param['playmode'] = $playmode;
            $param['isremind'] = $isremind;
            $param['remindtime'] = empty($remindtimestr)?'':$remindtimestr;
            $param['remindmsg'] = empty($remindmsgstr)?'':$remindmsgstr;
            $param['coursewarelogo'] = 1;
        }

        if($this->roominfo['isschool'] == 7){
            $pid = $this->pid;
            if($isedit){
                $payitem = $this->pimodel->getDefineItemByFolderid($folderid,$roominfo['crid']);
            }
            if(!empty($pid) && (!empty($payitem) || !$isedit)){//新版添加编辑课程,判断所选服务包/分类 是否还存在
                $check = $this->checkSp($roominfo);
                if($check['status'] != 1){
                    return $check;
                }
            }
        }
        if($isedit){
            $param['iszjdlr'] = 1;
            $res = $this->foldermodel->editCourse($param);
            $ifolderid = $param['folderid'];
        } else {
            $param['iszjdlr'] = 1;
            $res = $ifolderid = $folderid = $this->foldermodel->addFolder($param);
        }
        $introParams = array(
            'introtype' => $this->introtype
        );
        //图片开场内容
        $slides = array_filter($this->slides, function($slide) {
            return !empty($slide['src']) && isset($slide['interval']);
        });
        $slides = array_map(function($slide) {
            return array(
                'src' => $slide['src'],
                'interval' => max(0, intval($slide['interval']))
            );
        }, $slides);
        $slides = array_values($slides);
        $introParams['slides'] = json_encode($slides);
        //视频开场内容
        if (!empty($this->attachment['originalName']) && !empty($this->attachment['url'])
            && !empty($this->attachment['server']) && !empty($this->attachment['md5'])) {
            $attachment = array(
                'checksum' => trim($this->attachment['md5']),
                'url' => trim($this->attachment['url']),
                'title' => trim($this->attachment['originalName']),
                'filename' => trim($this->attachment['originalName']),
                'source' => $this->attachment['server']
            );
            if (isset($this->attachment['size'])) {
                $attachment['size'] = intval($this->attachment['size']);
            }
            if (isset($this->attachment['type'])) {
                $attachment['suffix'] = trim($this->attachment['type'], '.');
            }
            $attachmentModel = new AttachmentModel();
            $attachment['status'] = 1;
            $attid = $attachmentModel->add($param['uid'], $param['crid'], $attachment);
            if (!empty($attid)) {
                $introParams['attid'] = $attid;
            }
        } else {
            $introParams['attid'] = intval($this->attachment['attid']);
        }
        $introModel = new IntroModel();
        $introModel->set($ifolderid, $introParams);

        if(($res !== FALSE && !empty($payitem) || !$isedit && $folderid) && $roominfo['isschool'] == 7){//编辑或者添加课程后处理服务项
            // return 111;
            $res = $this->doPayitem($roominfo,$folderid,$isedit);
            if($res != FALSE) {
                $res = $folderid;
            }
        }
        return $res;
    }
    /*
    新版编辑课程,同时编辑或添加服务项
    */
    private function doPayitem($roominfo,$folderid,$isedit = FALSE){
        if($isedit){
            $iteminfo = $this->pimodel->getDefineItemByFolderid($folderid,$roominfo['crid']);
            $itemarr['itemid'] = $iteminfo['itemid'];
        }
        $itemarr['pid'] = $this->pid;
        $itemarr['sid'] = $this->sid;
        $itemarr['iname'] = $this->foldername;
        $itemarr['isummary'] = $this->summary;
        $itemarr['crid'] = $roominfo['crid'];
        $itemarr['folderid'] = $folderid;
		$itemarr['limitnum'] = $this->limitnum;
		$itemarr['islimit'] = $this->islimit;
		//限制人数范围1-9999
		if($itemarr['limitnum'] > 9999){
			$itemarr['limitnum'] = 9999;
		} elseif($itemarr['limitnum'] < 1 && $itemarr['islimit'] == 1){
			$itemarr['limitnum'] = 1;
		}
        $itemarr['iprice'] = empty($this->fprice)?0:$this->iprice;
        if(empty($iteminfo['roomfee']) && empty($iteminfo['comfee']) || intval($iteminfo['iprice']) == 0){//上一次没有分成信息,按照总后台设置的分成比例来
            $crmodel = new ClassRoomModel();
            $roomdetail = $crmodel->getDetailClassroom($roominfo['crid']);
            if(!empty($roomdetail['profitratio'])){
                $profitratio = unserialize($roomdetail['profitratio']);
                $compercent = $profitratio['company']/100;
                $roompercent = 1-$compercent;
            }else{
                $compercent = 0.3;
                $roompercent = 0.7;
            }
        }else{//上一次有分成信息,按上一次的比例来
            $compercent = intval($iteminfo['comfee'])/intval($iteminfo['iprice']);
            $roompercent = 1-$compercent;
        }
        $itemarr['comfee'] = round($itemarr['iprice']*$compercent,2);
        $itemarr['roomfee'] = $itemarr['iprice'] - $itemarr['comfee'];

        $itemarr['view_mode'] = max(intval($this->view_mode), 0);
        $isimonthday = $this->isimonthday; //1按月，0按天
        if($isimonthday == 1){
            $itemarr['imonth'] = $this->imonth;
        } else {
            $itemarr['iday'] = $this->iday;
        }
        if($isedit){
            $res = $this->pimodel->edit($itemarr);
        } else {
            $itemarr['defind_course'] = 1;
            $res = $this->pimodel->add($itemarr);
            if($res != FALSE) {
                $res = 1;
            }
        }
        return $res;
    }

    /*
    新版添加课程,判断所选服务包/分类 是否还存在
    */
    private function checkSp($roominfo){

        $pid = $this->pid;
        $sid = $this->sid;
        $checkarr = array('crid'=>$roominfo['crid'],'pid'=>$pid);
        $res = $this->spmodel->hasCheck($checkarr);
        $addavailable = TRUE;
        $status = 1;
        if(empty($res['pid'])){//服务包被删除
            $msg = '所选分类 [name] 已不存在';
            $idtype = 'sp';
            $status = -1;
            $addavailable = FALSE;
        }elseif(!empty($sid)){//分类被删除
            $checkarr = array('crid'=>$roominfo['crid'],'pid'=>$pid,'sid'=>$sid);
            $res = $this->sortmodel->hasCheck($checkarr);
            if(empty($res)){
                $msg = '所选二级分类 [name] 已不存在';
                $idtype = 'sort';
                $status = -2;
                $addavailable = FALSE;
            }
        }

        return array('status'=>$status);
    }

    /*
    课程老师列表
    */
    public function courseTeacherListAction(){
        $teachermodel = new TeacherModel();
        $courseteacherlist = $teachermodel->getCourseTeacherList($this->crid,$this->folderids);
        return $courseteacherlist;
    }
    /*
    课程学习数列表
    */
    public function studyListAction(){
        $studylogmodel = new StudylogModel();
        $param['crid'] = $this->crid;
        $param['folderids'] = $this->folderids;
        $param['starttime'] = $this->starttime;
        $param['endtime'] = $this->endtime;
        $studylist = $studylogmodel->getCourseStudyCount($param);
        return $studylist;
    }

    /*
    班级课程列表
    */
    public function classCourseListAction(){
        $param['crid'] = $this->crid;
        $param['classids'] = $this->classids;
        $classcoursemodel = new ClasscourseModel();
        $classcourselist = $classcoursemodel->getFolders($param);
        return $classcourselist;
    }

    /*
    课件详情
    */
    public function cwDetailAction(){
        $cwmodel = new CoursewareModel();
        $cwdetail = $cwmodel->getCourseByCwid($this->cwid);
        return $cwdetail;
    }

    /*
    课件删除
    */
    public function cwDelAction(){
        $cwmodel = new CoursewareModel();
        $cwdetail = $cwmodel->getCourseByCwid($this->cwid);
        if (empty($cwdetail) || $cwdetail['crid'] != $this->crid) {
            return false;
        }
        $folderModel = new FolderModel();
        $ret = $cwmodel->del($this->cwid);
        //重置章节内课程排序号
        $cwmodel->resetRankSectionCoursewares($cwdetail['folderid'], $cwdetail['sid'], $this->crid);
        //统计所在课程的课件数
        $folderModel->statsCourseware($cwdetail['folderid'], $this->crid);
        //统计网校的课程数
        $roomModel = new ClassRoomModel();
        $roomModel->statsCourseware($this->crid);
        return $ret;
    }

    /*
    课件的学习详情
    */
    public function studyDetailAction(){
        $studylogmodel = new StudylogModel();
        $param['crid'] = $this->crid;
        $param['cwid'] = $this->cwid;
        $param['page'] = $this->page;
        $param['pagesize'] = $this->pagesize;
        $studylist = $studylogmodel->getStudyDetailByCwid($param);
        $studycount = $studylogmodel->getStudyCountByCwid($param);
        return array('studylist'=>$studylist,'studycount'=>$studycount);
    }
    /*
    学习记录的学生信息
    */
    public function studyUsersAction(){
        $usermodel = new UserModel();
        $userlist = $usermodel->getUserByUids($this->uids);
        $classlist = $usermodel->getUserClassByUids(array('uids'=>$this->uids,'crid'=>$this->crid));
        return array('userlist'=>$userlist,'classlist'=>$classlist);
    }


    /*
    保存班级课程
    */
    public function saveClassCourseAction(){
        $classcoursemodel = new ClasscourseModel();
        return $classcoursemodel->saveClasscourse(array('classid'=>$this->classid,'folderids'=>$this->folderids,'crid'=>$this->crid,'uids'=>$this->uids,'isclear'=>$this->isclear));
    }


    /*
    选择课程任课教师
    */
    public function chooseTeacherAction(){
        $param['folderid'] = $this->folderid;
        $param['crid'] = $this->crid;
        $param['tids'] = $this->tids;
        return $this->foldermodel->chooseTeacher($param);


    }

    /**
     *教师参数处理,剔除非本校的教师,返回合法的教师id数组
     *@param String $tids 格式 tid1,tid2,tid3
     *@return Array
     */
    public function filterTeacherAction(){
        $teachermodel = new TeacherModel();
        $roomteacherlist = $teachermodel->getroomteacherlist($this->crid,array('limit'=>1000));
        //所有在该校的教师id数组
        $trueTidArr = array_column($roomteacherlist,'uid');
        $tidArr = explode(',', $this->tids);
        return array_intersect($trueTidArr,$tidArr);
    }

    /**
     * 热门课程
     */
    public function hotCourseListAction() {
        $model = new FolderModel();
        return $model->hotList($this->crid, $this->num);
    }

    /**
     * 修改章节下课件排序号
     */
    public function rankCoursewareInSectionAction() {
        $model = new CoursewareModel();
        return $model->rankCoursewareInSection($this->cwid, $this->crid, $this->step);
    }

    /**
     * 修改课程排序号
     */
    public function rankCourseAction() {
        $model = new FolderModel();
        return $model->rankCourse($this->folderid, $this->crid, $this->step, $this->scope);
    }

    /**
     * 批量修改课件排序号
     */
    public function batchRankCoursewaresAction() {
        $model = new CoursewareModel();
        if ($this->fromexcel > 0) {
            //从excel表格中导入的数据，用章节、课件名称查询出课件ID
            $ret = $model->getCwids($this->ranks, $this->crid);
            if (empty($ret)) {
                return false;
            }
            $realRanks = array();
            foreach ($ret as $item) {
                foreach ($this->ranks as $s) {
                    if ($item['title'] == $s['title'] && $item['sname'] == $s['sname']) {
                        $realRanks[$item['cwid']] = array(
                            'cwid' => $item['cwid'],
                            'rank' => $s['rank']
                        );
                        break;
                    }
                }
            }
            $this->ranks = $realRanks;
            unset($realRanks, $ret);
        }
        return $model->batchRankCoursewares($this->folderid, $this->ranks, $this->crid);
    }

    /**
     * 批量修改课程排序号
     */
    public function batchRankCoursesAction() {
        $model = new FolderModel();
        if ($this->fromexcel > 0) {
            //从excel表格中导入的数据，用课程主类、课程子类、课程名称查询出课程ID
            $ret = $model->getFolderids($this->ranks, $this->crid);
            if (empty($ret)) {
                return false;
            }
            $realRanks = array();
            foreach ($ret as $item) {
                foreach ($this->ranks as $r) {
                    if ($r['pname'] == $item['pname'] && $r['sname'] == $item['sname'] && $r['foldername'] == $item['foldername']) {
                        $realRanks[$item['folderid']] = array(
                            'folderid' => $item['folderid'],
                            'grank' => $r['grank'],
                            'prank' => $r['prank'],
                            'srank' => $r['srank']
                        );
                        break;
                    }
                }
            }
            $this->ranks = $realRanks;
            unset($ret, $realRanks);
        }
        return $model->batchRankCourses($this->ranks, $this->crid);
    }

    /**
     * 课程排序列表For Excel表格
     */
    public function importCourseRankTplAction() {
        $model = new FolderModel();
        return $model->batchRankTpl($this->pid, isset($this->sid) ? intval($this->sid) : null, $this->crid);
    }

    /**
     * 课件排序列表For Excel表格
     */
    public function importCoursewareRankTplAction() {
        $model = new CoursewareModel();
        return $model->coursewareRankTpl($this->folderid, $this->crid, isset($this->sid) ? intval($this->sid) : null, $this->s);
    }

    /*
    无效课程数据
    */
    public function unfitCourseListAction(){
        $param['crid'] = $this->crid;
        $param['q'] = $this->q;
        $coursecount = $this->foldermodel->getUnfitCourseCount($param);
        $param['pagesize'] = $this->pagesize;
        $param['page'] = $this->page;
        $courselist = $this->foldermodel->getUnfitCourseList($param);
        return array('courselist'=>$courselist,'coursecount'=>$coursecount);
    }

    /*
    学分改动，批量处理
    */
    public function doStudyCreditAction(){
        $plmodel = new PlayLogModel();
        return $plmodel->doStudyCredit($this->folderid,$this->crid);
    }

    /**
     * 价格大于０的零售课程
     * @return array
     */
    public function singleValueableCourseListAction() {
        $model = new PayitemModel();
        return $model->getValueableCourseList($this->crid);
    }

    /**
     * 网校课程分层统计
     * @return array
     */
    public function courseCategoryAction() {
        $ret = array();
        $itemModel = new PayitemModel();
        $packageModel = new PaypackageModel();
        $sortModel = new PaysortModel();
        $items = $itemModel->getSchoolItems($this->crid);
        if (!empty($items)) {
            $pids = array_unique(array_column($items, 'pid'));
            $packages = $packageModel->getPackageMenuList($pids);
            $packages = array_map(function($package) {
                $package['coursenum'] = 0;
                $package['ocoursenum'] = 0;
                return $package;
            }, $packages);
            $sids = array_unique(array_column($items, 'sid'));
            $sorts = $sortModel->getSortMenuList($sids);
            $sorts = array_map(function($sort) {
                $sort['coursenum'] = 0;
                return $sort;
            }, $sorts);
            foreach ($items as $item) {
                if (!isset($packages[$item['pid']])) {
                    continue;
                }
                $packages[$item['pid']]['coursenum']++;
                if (isset($sorts[$item['sid']])) {
                    $sorts[$item['sid']]['coursenum']++;
                    continue;
                }
                if ($item['sid'] == 0) {
                    $packages[$item['pid']]['ocoursenum']++;
                }
            }
            foreach ($sorts as $sort) {
                if (!isset($packages[$sort['pid']])) {
                    continue;
                }
                $packages[$sort['pid']]['sorts'][] = $sort;
            }
            array_walk($packages, function(&$package) {
                if (!empty($package['ocoursenum'])) {
                    $package['sorts'][] = array(
                        'sid' => 0,
                        'sname' => '其他课程',
                        'coursenum' => $package['ocoursenum']
                    );
                }
                unset($package['ocoursenum']);
            });
            $ret[0] = array_values($packages);
        } else {
            $ret[0] = array();
        }
        $schItems = $itemModel->getSchItems($this->crid);
        if (empty($schItems)) {
            return $ret;
        }
        $sourcecrids = array_unique(array_column($schItems, 'sourcecrid'));
        $schModel = new SchsourceModel();
        $schSources = $schModel->getSourceSchoolList($this->crid, $sourcecrids);
        $schSources = array_map(function($school) {
            $school['coursenum'] = 0;
            return $school;
        }, $schSources);
        $pids = array_unique(array_column($schItems, 'pid'));
        $packages = $packageModel->getPackageMenuList($pids);
        $packages = array_map(function($package) {
            $package['coursenum'] = 0;
            $package['ocoursenum'] = 0;
            return $package;
        }, $packages);
        $sids = array_unique(array_column($schItems, 'sid'));
        $sorts = $sortModel->getSortMenuList($sids);
        $sorts = array_map(function($sort) {
            $sort['coursenum'] = 0;
            return $sort;
        }, $sorts);
        foreach ($schItems as $item) {
            if (!isset($packages[$item['pid']])) {
                continue;
            }
            $packages[$item['pid']]['coursenum']++;
            $packages[$item['pid']]['sourcecrid'] = $item['sourcecrid'];
            if (isset($sorts[$item['sid']])) {
                $sorts[$item['sid']]['coursenum']++;
                continue;
            }
            if ($item['sid'] == 0) {
                $packages[$item['pid']]['ocoursenum']++;
            }
        }
        foreach ($sorts as $sort) {
            if (!isset($packages[$sort['pid']])) {
                continue;
            }
            $packages[$sort['pid']]['sorts'][] = $sort;
        }
        array_walk($packages, function(&$package) {
            if (!empty($package['ocoursenum'])) {
                $package['sorts'][] = array(
                    'sid' => 0,
                    'sname' => '其他课程',
                    'coursenum' => $package['ocoursenum']
                );
            }
            unset($package['ocoursenum']);
        });
        foreach ($packages as $package) {
            $scourcecrid = $package['sourcecrid'];
            if (isset($schSources[$scourcecrid])) {
                unset($package['sourcecrid']);
                $schSources[$scourcecrid]['packages'][] = $package;
                $schSources[$scourcecrid]['coursenum'] += $package['coursenum'];
            }
        }
        $ret[1] = !empty($schSources) ? array_values($schSources) : array();
        return $ret;
    }

    /**
     * 企业选课课程列表
     * @return array
     */
    public function schCourseAction() {
        $model = new CourseModel();
        $limit = null;
        if ($this->page !== null || $this->pagesize !== null) {
            $limit = array(
                'page' => intval($this->page),
                'pagesize' => intval($this->pagesize)
            );
        }
        $count = $model->getSchCourseCount($this->crid, $this->sourcecrid, $this->pid, $this->sid, $this->search);
        $list = array();
        if ($count > 0) {
            $list = $model->getSchCourse($this->crid, $this->sourcecrid, $this->pid, $this->sid, $this->search, $limit);
            $folderids = array_column($list, 'folderid');
            $coursewareModel = new CoursewareModel();
            $counts = $coursewareModel->getCoursewareCounts($this->sourcecrid, $folderids);
            unset($folderids);
            array_walk($list, function(&$folder, $fid, $counts) {
                if (!isset($counts[$folder['folderid']])) {
                    $folder['coursewareCount'] = 0;
                    return;
                }
                $folder['coursewareCount'] = $counts[$folder['folderid']]['c'];
            }, $counts);
        }

        return array(
            'count' => $count,
            'list' => $list
        );
    }

    /**
     * @describe:获取课程主类
     * @User:tzq
     * @Date:2017/11/21
     * @param int $crid    网校id
     * @return json/array;
     */
    public function getClassAction(){
        $params['crid'] = $this->crid;
        $ret = $this->foldermodel->getClass($params);
        return $ret;

    }


    /**
     * @describe:根据课程id获取教师信息
     * @User:tzq
     * @Date:2017/11/21
     * @param string $foldersid 课程id，多个用,号隔开
     * @return  json/array
     */
    public function getTeacherHeadAction(){
        $params['folderid'] = $this->folderid;
        if(empty($params)){
            return ['code'=>1,'msg'=>'参数错误'];
        }
        $ret = $this->foldermodel->getTeacherHead($params);
        return $ret;
    }


    /**
     * @describe:课程-学生排名-获取学生班级，地址，学分
     * @User:tzq
     * @Date:2017/11/23
     * @param string $attach  附加字段用于取缓存
     * @return json/array
     */
    public function getCoreClassAction(){
        $params['attach'] = $this->attach;
        $data = Ebh()->cache->get($params['attach']);//获取缓存中的数据
        if($data){
            $data = $data['attach'];
        }
        $ret = $this->foldermodel->getCoreClass($data);
        return $ret;
    }

    /**
     * @describe:课程-学生排名
     * @User:tzq
     * @Date:2017/11/22
     * @param int $folderid 课程id
     * @param int  $orderBY  排序
     * 1 积分从高到低
     * 2 积分从低到高
     * 3 学分从高到低
     * 4 学分从低到高
     * 5 学时从高到低
     * 6 学时从低到高
     * @return json/array
     */
    public function courseStudentSortAction(){
        $params['folderid'] = $this->folderid;
        $params['orderBy']  = $this->orderBy;
        $params['crid']     = $this->crid;
        $params['school_type'] = $this->school_type;

        $params['apiName'] = 'courseStudentSortAction';//防止和上面的cacheKey冲突
        switch ($params['orderBy']){
            case 1:
                $params['orderBy'] = array('credit','SORT_DESC');

                break;
            case 2:
                $params['orderBy'] =array('credit' ,'SORT_ASC') ;

                break;
            case 3:
                $params['orderBy'] = array('score','SORT_DESC');

                break;
            case 4:

                $params['orderBy'] =array('score' ,'SORT_ASC') ;

                break;
            case 5:
                $params['orderBy'] = array('ltime','SORT_DESC');


                break;
            case 6:
                $params['orderBy'] = array('ltime','SORT_ASC');


                break;
            default:
                $params['orderBy'] = '';

        }


        $params['type'] = isset($params['type'])?$params['type']:1;
        //log_message('参数：'.json_encode($params));
        $foldermodel = new FolderModel();
        $ret =$foldermodel->courseStudentSort($params);
        return $ret;

    }

    /**
     * @describe:课程-文件统计
     * @User:tzq
     * @Date:2017/11/23
     * @param int $folderid 课程id
     * @return json/array
     */
    public function fileCountAction(){
        $params['folderid'] = $this->folderid;
        $params['crid']     = $this->crid;
        $ret = $this->foldermodel->fileCount($params);
        //课件数量统一获取
        $cwmodel = new CoursewareModel();
        $cws = $cwmodel->getCwCountByFolderid($params);
        $reviewmodel = new ReviewModel();
        $reviews = $reviewmodel->getReviewCountByFolderid($params);
        $zans =  $this->foldermodel->zanCount($params);
        $ret['zan']  = isset($zans[$params['folderid']])?$zans[$params['folderid']]['count']:0;
        $ret['reNum'] = isset($reviews[$this->folderid])?$reviews[$this->folderid]['count']:0;
        $ret['courseNum'] = isset($cws)?$cws:0;
        return $ret;
    }

    /**
     * @describe:根据课程id获取时长，点赞，评论，学分
     * @User:tzq
     * @Date:2017/12/1
     */
    public function getFolderidMsgAction(){
        $params['folderids'] = $this->folderids;
        $params['crid']      = $this->crid;

        $folder_model = new FolderModel();

        $ret = $folder_model->getFolderidMsg($params);
        return $ret;
    }

    /**
     * @describe:根据课程获取点赞,评论,价格,时长
     * @User:tzq
     * @Date:2017/12/2
     * @param string $cwids 课件id
     */
    public function getCoursesMsgAction(){
        $cwids = $this->cwids;
        $cw_model = new FolderModel();
        $ret = $cw_model->getCoursesMsg($cwids);
        return $ret;
    }

    /**
     * @describe:用课程id获取课程总时长
     * @User:tzq
     * @Date:2017/12/4
     * @param string $folderids 课程id多个,号隔开
     * @param int    $crid   网校id
     * @return  array
     */
    public function cwlengthCountToFolderidAction(){
        $params['folderids'] = $this->folderids;
        $params['crid']      = $this->crid;
        $folder_model = new FolderModel();
        $ret  = $folder_model->cwlengthCountToFolderid($params);
        return $ret;
        
    }
	
	
}