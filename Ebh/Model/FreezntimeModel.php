<?php
/**
 * 金额冻结时间
 */
class FreezntimeModel {
    /**
     * 金额冻结时间，单位：天
     * @param int $crid 网校ID
     * @param int $default 默认值
     * @return int
     */
	public function getFreeznDay($crid, $default = 15) {
		$row = Ebh()->db->query('SELECT `fund_freezn` FROM `ebh_freezn_times` WHERE `crid`='.$crid.' ORDER BY `fid` DESC LIMIT 1')->row_array();
		if (empty($row)) {
			return $default;
		}
		return $row['fund_freezn'];
	}

    /**
     * 根据条件插入或更新
     * @param int $day 冻结天数
     * @param int $crid 网校ID
     * @param int $uid 操作用户ID
     * @return mixed
     */
	public function edit($day, $crid, $uid){
		//$sql = 'INSERT INTO `ebh_freezn_times`(`crid`,`fund_freezn`,`mdatetime`,`uid`) VALUES('.$crid.','.$day.','.SYSTIME.','.$uid.') ON DUPLICATE KEY UPDATE `fund_freezn`='.$day.',`mdatetime`='.SYSTIME.',`uid`='.$uid;
		//return $this->db->query($sql, false);
        return Ebh()->db->insert('ebh_freezn_times', array(
            'crid' => $crid,
            'fund_freezn' => $day,
            'dateline' => SYSTIME,
            'uid' => $uid
        ));
	}
}