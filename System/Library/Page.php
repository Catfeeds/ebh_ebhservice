<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 * 分页类
 */
class Page{
    public $firstRow; // 起始行数
    public $listRows; // 列表每页显示行数
    public $totalRows; // 总行数
    public $totalPages; // 分页总页面数
    public $nowPage = 1;

    private $p       = 'p'; //分页参数名


    public function __construct($totalRows, $listRows=20, $parameter = array()) {
        getConfig('system.page.var_page') && $this->p = getConfig('system.page.var_page'); //设置分页参数名称
        $this->totalRows  = $totalRows; //设置总记录数
        $this->listRows   = $listRows;  //设置每页显示行数
        $this->nowPage    = empty($_REQUEST[$this->p]) ? 1 : intval($_REQUEST[$this->p]);
        $this->nowPage    = $this->nowPage>0 ? $this->nowPage : 1;
        $this->firstRow   = $this->listRows * ($this->nowPage - 1);
        $this->totalPages = ceil($this->totalRows / $this->listRows); //总页数
        $this->totalPages = $this->totalPages>0 ?$this->totalPages : 1;
    }
}