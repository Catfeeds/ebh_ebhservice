<?php
/**
 * ebhservice.
 * User: ckx
 * 学生学分学时缓存
 */
class UserStudyInfo{


	/**
	 * 获取缓存
	 * @param $crid
	 * @param $uid
	 * @return mixed
	 */
	public function getCache($crid,$uid){
		$redis_name = 'userstudyinfo_'.$crid;
		$redis_key = $uid;
		return Ebh()->cache->hGet($redis_name,$redis_key,TRUE);
	}
	
	/**
	 * 设置缓存
	 * @param $crid
	 * @param $uid
	 * @param $value
	 */
	public function setCache($crid,$uid,$value){
		$redis_name = 'userstudyinfo_'.$crid;
		$redis_key = $uid;
		Ebh()->cache->hSet($redis_name,$redis_key,$value);
	}
	
	/**
	 * 清除缓存
	 * @param $crid
	 * @param $uid
	 */
	public function clearCache($crid,$uid = null){
		$redis_name = 'userstudyinfo_'.$crid;
		$redis_key = $uid;
		Ebh()->cache->hDel($redis_name,$redis_key);
	}
}

