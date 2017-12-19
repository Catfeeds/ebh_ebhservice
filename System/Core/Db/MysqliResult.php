<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Db_MysqliResult implements  Db_Result {
    public $resultobj = NULL;
    public function __construct($obj) {
        $this->resultobj = $obj;
    }
    public function row_array() {
        if(empty($this->resultobj) || !is_object($this->resultobj)) {
            return false;
        }
        $row = $this->resultobj->fetch_array(MYSQLI_ASSOC);
        return $row;
    }
    public function list_array($key = '', $prefix = '') {
        if(empty($this->resultobj) || !is_object($this->resultobj)) {
            return false;
        }
        $resultarr = array();
        if(empty($key)) {
            while($row = $this->resultobj->fetch_array(MYSQLI_ASSOC)) {
                $resultarr[] = $row;
            }
        } else {
            while($row = $this->resultobj->fetch_array(MYSQLI_ASSOC)) {
                $k = !empty($prefix) ? $prefix.$row[$key] : $row[$key];
                $resultarr[$k] = $row;
            }
        }
        return $resultarr;
    }
    /**
     * 返回查询一维数组
     * @param string $field 值字段名，为空时以查询的第一个字段为值
     * @param string $key 键字段名,为空时以顺序数字为键
     * @return mixed
     */
    public function list_field($field = '', $key = '') {
        if(empty($this->resultobj) || !is_object($this->resultobj)) {
            return false;
        }
        $ret = array();
        if (!empty($field)) {
            if (!empty($key)) {
                while($row = $this->resultobj->fetch_array(MYSQLI_ASSOC)) {
                    $ret[$row[$key]] = $row[$field];
                }
                return $ret;
            }
            while($row = $this->resultobj->fetch_array(MYSQLI_ASSOC)) {
                $ret[] = $row[$field];
            }
            return $ret;
        }

        while($row = $this->resultobj->fetch_array(MYSQLI_NUM)) {
            $ret[] = $row[0];
        }
        return $ret;
    }
    public function __destruct() {
        $this->close();
    }
    public function close() {
        if(!empty($this->resultobj) && is_object($this->resultobj)) {
            $this->resultobj->free();
        }
    }
}