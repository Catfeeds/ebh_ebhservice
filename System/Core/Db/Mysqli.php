<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Db_Mysqli implements Db_Driver{
    public $conn_id = FALSE;
    public $connect_error = FALSE;
    function __construct($dbhost,$dbuser,$dbpw,$dbname,$dbport = ''){
        if(empty($dbport)) {
            $conn_id = @new mysqli($dbhost, $dbuser,$dbpw, $dbname);
        } else {
            $conn_id = @new mysqli($dbhost, $dbuser,$dbpw, $dbname, $dbport);
        }
        if ($conn_id->connect_error) {
            $this->connect_error = $conn_id->connect_error;
            $conn_id = NULL;
        }
        $this->conn_id = $conn_id;

    }

    function __destruct() {
        $this->close();
    }

    public function _set_charset($charset) {
        return $this->conn_id->set_charset($charset);
    }


    public function _execute($sql) {
        $result= $this->conn_id->query($sql);
        return $result;
    }

    public function error_msg() {
        return $this->conn_id->error;
    }


    public function _insert_id() {
        return $this->conn_id->insert_id;
    }
    public function _affected_rows() {
        return $this->conn_id->affected_rows;
    }

    /**
     *
     * @param type $str
     */
    public function escape_str($str,$like = FALSE) {
        if(is_array($str)) {
            foreach($str as $key =>$value) {
                $str[$key] = $this->escape_str($value, $like);
            }
            return $str;
        }
        if(is_object($this->conn_id) && method_exists($this->conn_id, 'real_escape_string')) {
            $str = $this->conn_id->real_escape_string($str);
        }
        return $str;
    }
    //关闭数据库连接
    function close() {
        if(!empty($this->conn_id) && !is_null($this->conn_id)) {
            $this->conn_id->close();
        }
    }
}