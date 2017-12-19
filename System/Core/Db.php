<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 * 数据库接口
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class DB{
    public $config = array();
    public $dbdriver = 'mysqli';
    public $master_conn = NULL;
    public $slave_conns = array();
    public $cur_con = NULL;

    public $_trans_status = false;
    /**
     * 配置数据库
     * @param array $config
     */
    function __construct($config = array()) {
        if (is_array($config)) {
            $this->config = $config;
            foreach ($config as $key => $value) {
                $this->$key = $value;
            }
        }

        $this->init();
    }

    function __destruct() {
        $this->master_conn = NULL;
        unset($this->slave_conns);
    }
    //Db类初始化
    public function init(){
        //实例化响应的DbDriver类
        $dbDriverClass = 'Db_'.ucfirst($this->dbdriver);

        //如果master库已连接 直接返回
        if (is_resource($this->master_conn) OR is_object($this->master_conn)) {
            return TRUE;
        }

        $this->master_conn  = new $dbDriverClass($this->dbhost,$this->dbuser,$this->dbpw,$this->dbname,$this->dbport);


        if(!$this->master_conn || $this->master_conn->connect_error !== FALSE) {
            Ebh()->log->error('数据库连接错误'.$this->master_conn->connect_error);
            throw new Exception_InternalServerError('数据库连接错误');
            return false;
        }
        //设置数据库字符集
        $this->master_conn->_set_charset($this->dbcharset);

        //检查是否配置从库
        if(!empty($this->slave)) {
            foreach ($this->slave as $slaveConfig) {
                $slaveConnect = new $dbDriverClass($slaveConfig['dbhost'],$slaveConfig['dbuser'],$slaveConfig['dbpw'],$slaveConfig['dbname'],$slaveConfig['dbport']);
                if (!$slaveConnect || $slaveConnect->connect_error !== FALSE) {
                    Ebh()->log->error('slave数据库连接错误:'.$slaveConnect->connect_error);
                    continue;
                }
                $slaveConnect->_set_charset($this->dbcharset);
                $this->slave_conns[] = $slaveConnect;
            }
        }
        return true;
    }


    /**
     * 执行sql语句
     * @param $sql
     * @return mixed
     */
    public function query($sql,$return_object = TRUE){
        $result = false;
        if (!empty($this->slave_conns) && strtoupper(substr($sql, 0,6)) == 'SELECT' && $this->cur_con !== 0) {
            if (count($this->slave_conns) == 1){
                $connent = $this->slave_conns[0];
            }else{
                $slavekey = mt_rand(0, count($this->slave_conns) - 1);
                $connent = $this->slave_conns[$slavekey];
            }
        }else{
            $connent = $this->master_conn;
        }
		
		if ($this->cur_con === 0 && strtoupper(substr($sql, 0,6)) == 'SELECT') {	//兼容kingshard强制从主库查询
			$sql = substr($sql, 0,6).'/*master*/'.substr($sql, 6);
		}
        $result = $connent->_execute($sql);

        if(!$result) {
            $this->_trans_status = false;
            $this->error = $connent->error_msg();
            Ebh()->log->error('Query error:'.$this->error."\r\n SQL:".$sql);
        }
        if(!$return_object){
            return $result;
        }else{
            $resultClassName = 'Db_'.ucfirst($this->dbdriver).'Result';
            return new $resultClassName($result);
        }

    }

    //插入数据
    public function insert($tablename,$param) {
        if(empty($tablename) || empty($param))
            return false;
        $keys = array();
        $values = array();
        foreach ($param as $key=>$value) {
            $keys[] = '`'.$key.'`';
            $values[] = $this->escape($value);
        }
        $sql = 'insert into '.$tablename.'('.  implode(',', $keys).') values ('.  implode(',', $values).')';
        $this->query($sql);
        return $this->insert_id();
    }

    /**
     * 转义变量，使得变量可用于数据库查询
     * @param mix $str
     * @return string
     */
    public function escape($str) {
        if(is_string($str)) {
            $str = "'".$this->master_conn->escape_str($str)."'";
        } else if(is_bool($str)) {
            $str = ($str == true) ? 1 : 0;
        } else if(is_null($str)) {
            $str = 'NULL';
        }
        return $str;
    }
    /**
     * 单独转义字符串
     * @param mix $str
     * @return string
     */
    public function escape_str($str) {
        return $this->master_conn->escape_str($str);
    }


    /**
     * 更新表记录
     * @param string $talename表名
     * @param array $param更新字段值对应的数组
     * @param array $where 更新的条件
     * @param array $sparam 更新字段值对应的数组,它与@param的不同是不对字段和字段的值进行处理，
     * 主要用于字段值得自增等处理，如@sparam = array('viewnum'=>'viewnum + 1')
     * @return boolean
     */
    public function update($talename,$param = array(),$where,$sparam = array()) {
        if(empty($talename) || (empty($param) && (empty($sparam))) || empty($where))
            return false;
        $wherearr = array();
        if(is_array($where)) {
            foreach ($where as $wkey=>$wvalue) {
                $wherearr[] = $wkey.' = '.$this->escape($wvalue);
            }
        } else {
            $wherearr[] = $where;
        }
        $fieldlist = array();
        foreach ($param as $key=>$value) {
            $fieldlist[] = $key .'='. $this->escape($value);
        }
        foreach ($sparam as $key=>$value) {
            $fieldlist[] = $key .'='. $value;
        }
        $sql = 'UPDATE '.$talename.' SET '.implode(',', $fieldlist).' WHERE '.  implode(' AND ', $wherearr);
        $result = $this->query($sql,FALSE);
        if($result === FALSE)
            return FALSE;
        return $this->affected_rows();
    }


    /**
     * 删除表记录
     * @param string $talename表名
     * @param type $where where条件
     * @return boolean 返回影响行数
     */
    public function delete($talename,$where) {
        if(empty($where))
            return false;
        $wherearr = array();
        if(is_array($where)) {
            foreach ($where as $wkey=>$wvalue) {
                $wherearr[] = $wkey.' = '.$this->escape($wvalue);
            }
        } else {
            $wherearr[] = $where;
        }
        $sql = 'DELETE FROM '.$talename.' WHERE '.  implode(' AND ', $wherearr);
        $this->query($sql,FALSE);
        return $this->affected_rows();
    }





    /**
     * 返回新生成的记录id
     * @return int 返回新生成的记录id
     */
    public function insert_id() {
        return $this->master_conn->_insert_id();
    }

    /**
     * 返回上次SQL语句影响行数
     * @return int 返回上次SQL语句影响行数
     */
    public function affected_rows() {
        return $this->master_conn->_affected_rows();
    }
	
	/**
     * 获取事务执行状态
     * @return boolean 
     */
    public function trans_status() {
        return $this->_trans_status;
    }

    /**
     * 开始事务
     * @return boolean
     */
    public function begin_trans() {
        $this->_trans_status = TRUE;
        $this->query('SET AUTOCOMMIT=0');
        $this->query('START TRANSACTION'); // can also be BEGIN or BEGIN WORK
        return TRUE;
    }
    /**
     * 提交事务
     * @return boolean
     */
    public function commit_trans() {
        $this->query('COMMIT');
        $this->query('SET AUTOCOMMIT=1');
        return TRUE;
    }
    /**
     * 事务回滚
     * @return boolean
     */
    public function rollback_trans() {
        $this->query('ROLLBACK');
        $this->query('SET AUTOCOMMIT=1');
        return TRUE;
    }

    /**
     * 是否执行成功
     * @return bool
     */
    public function is_fail() {
        return !empty($this->error);
    }
	/**
	*设置当前连接的数据库源编号，目前只支持设置为0的情况，即使用主服务器功能
	*@param int $cur 当前连接的数据库源编号
	*/
	public function set_con($cur = NULL) {
		if($cur === 0) {
			$this->cur_con = 0;
		}
	}
	/**
	*重置当前连接的数据库源编号
	*/
	public function reset_con() {
		$this->cur_con = NULL;
	}
}