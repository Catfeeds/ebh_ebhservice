<?php

/**
 * 签到
 * Created by PhpStorm.
 * User: ycq
 * Date: 2017/4/28
 * Time: 10:34
 */
class SignLogModel
{
    /**
     * 签到分析数据
     * @param $crid 网校ID
     * @param $params 分析参数
     * @param bool $setKey 输出是否设置键
     * @param int $baseTime 今天时间戳
     * @return mixed
     */
    public function signResolve($crid, $params, $setKey = false, $baseTime = 0) {
        $today = !empty($baseTime) ? $baseTime : strtotime(date('Y-m-d'));
        $starttime = isset($params['starttime']) ? intval($params['starttime']) : $today;
        $whereArr = array(
            '`crid`='.intval($crid),
            '`dateline`>='.$starttime
        );
        if (isset($params['endtime'])) {
            $whereArr[] = '`dateline`<='.intval($params['endtime']);
        }
        if ($starttime >= $today || isset($params['endtime']) && $params['endtime'] < $starttime + 86400) {
            $format = '\'%Y-%m-%d %H:00:00\'';
        } else {
            $format = '\'%Y-%m-%d\'';
        }
        $sql = 'SELECT FROM_UNIXTIME(`dateline`,'.$format.') AS `date`,COUNT(1) AS `signs` FROM `ebh_signlogs` WHERE '.
            implode(' AND ', $whereArr).' GROUP BY `date`';
        return Ebh()->db->query($sql)->list_array($setKey ? 'date' : '');
    }
	
	/*
	 * 记录日志
	 * @param array $param 
	*/
	public function addLog($param){
		if(empty($param['uid']) || empty($param['crid'])){
			return FALSE;
		}
		$sql = 'select dateline from ebh_signlogs where crid='.$param['crid'].' and uid='.$param['uid'];
		$sql.= ' order by dateline desc limit 1';
		$log = Ebh()->db->query($sql)->row_array();
		if(!empty($log) && $log['dateline']>=strtotime(Date('Y-m-d'))){
			return FALSE;
		}
		
		$setarr['uid'] = $param['uid'];
		$setarr['crid'] = $param['crid'];
		$setarr['dateline'] = SYSTIME;
		if(!empty($param['ip'])){
			$setarr['ip'] = $param['ip'];
		}
		if(!empty($param['citycode'])){
			$setarr['citycode'] = $param['citycode'];
		}
		if(!empty($param['parentcode'])){
			$setarr['parentcode'] = $param['parentcode'];
		}
		return Ebh()->db->insert('ebh_signlogs',$setarr);
	}
	
	/*
	获取用户签到列表
	*/
	public function getSignList($param){
		if(empty($param['crid']) || empty($param['uid'])){
			return FALSE;
		}
		if(empty($param['byday'])){
			$sql = 'select dateline from ebh_signlogs';
		} else {
			$sql = "select dateline,from_unixtime(dateline,'%Y-%m-%d') d from ebh_signlogs";
		}
		$wherearr[]= 'crid='.$param['crid'];
		$wherearr[]= 'uid='.$param['uid'];
		if(!empty($param['starttime'])){
			$wherearr[] = 'dateline>='.$param['starttime'];
		}
		if(!empty($param['endtime'])){
			$wherearr[] = 'dateline<='.$param['endtime'];
		}
		$sql.= ' where '.implode(' AND ',$wherearr);
		if(!empty($param['byday'])){
			$sql.= ' group by d';
		}
		$sql.= ' order by dateline desc';
		return Ebh()->db->query($sql)->list_array();
	}


	public function getSignCount($param){
        if(empty($param['crid']) || empty($param['uid'])){
            return 0;
        }
        $sql = 'select count(logid) as count from ebh_signlogs';

        $wherearr[]= 'crid='.$param['crid'];
        $wherearr[]= 'uid='.$param['uid'];
        if(!empty($param['starttime'])){
            $wherearr[] = 'dateline>='.$param['starttime'];
        }
        if(!empty($param['endtime'])){
            $wherearr[] = 'dateline<='.$param['endtime'];
        }
        $sql.= ' where '.implode(' AND ',$wherearr);

        $rs =  Ebh()->db->query($sql)->row_array();
        return $rs['count'];
    }
}