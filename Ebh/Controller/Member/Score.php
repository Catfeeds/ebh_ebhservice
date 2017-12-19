<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:32
 */
class ScoreController extends Controller{
    public function parameterRules(){
        return array(
            //学生学分排行榜
            'rankAction' => array(
                'classid' => array(
                    'name' => 'classid',
                    'type' => 'int',
                    'require' => true
                ),
                'orderType' => array(
                    'name' => 'orderType',
                    'type' => 'int',
                    'default' => 0
                ),
                'page' => array(
                    'name' => 'page',
                    'type' => 'int',
                    'default' => 1
                ),
                'pagesize' => array(
                    'name' => 'pagesize',
                    'type' => 'int',
                    'default' => 1
                )
            )
        );
    }

    /**
     * 学生学分排行榜
     * @return mixed
     */
    public function rankAction() {
        $model = new ClassstudentsModel();
        $students = $model->getClassStudentList($this->classid);
        if (empty($students)) {
            return array(
                'count' => 0,
                'students' => array()
            );
        }
        $classModel = new ClassesModel();
        $classDetail = $classModel->getDetail($this->classid);
        $studentids = array_keys($students);
        $playLogModel = new PlayLogModel();
        $logs = $playLogModel->getStudentScoreList($studentids, $classDetail['crid']);
        array_walk($students, function(&$student, $k, $logs) {
            if (isset($logs[$k])) {
                $student['score'] = $logs[$k]['score'];
                return;
            }
            $student['score'] = 0;
        }, $logs);
        $scores = array_column($students, 'score');
        $sort = $this->orderType == 0 ? SORT_DESC : SORT_ASC;
        array_multisort($scores, $sort, SORT_NUMERIC,
            $studentids, SORT_ASC, SORT_NUMERIC, $students);
        $count = count($students);
        $page = max($this->page, 1);
        $length = max($this->pagesize, 1);
        $students = array_slice($students, ($page - 1) * $length, $length);
        return array(
            'count' => $count,
            'students' => $students
        );
    }
}