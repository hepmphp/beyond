<?php
namespace helpers;
class Page{
    private $_current_page; //当前页
    private $_current_url; //当前URL
    private $_total_num; //总记录数
    private $_pagesize = 10; //每页显示数目
    private $_pagelen = 5; //页面显示数目(长度)
    private $_pageclass = 'pages'; //所用分页样式类名
    private $_pagestring; //分页HTML字符串
    private $_total_pages; //总页数
    private $_pageoffset = 3; //页数偏移量
    public $total_num_str= '<span class="rows">共 %s条记录</span>';
    public $next_page  = '&gt;';
    public $last_page  = '&gt;&gt;';
    public $prev_page  = '&lt;';
    public $first_page = '&lt;&lt;';
    private  static $page_instance=null;
    public static function get_str($page,$total_num,$page_size=10,$current_url=''){
        if(!self::$page_instance){
            self::$page_instance = new self();
        }
        self::$page_instance ->setCurrentPage($page);
        self::$page_instance ->setTotalNum($total_num);
        self::$page_instance ->setPageSize($page_size);
        self::$page_instance ->setCurrentUrl();
        return self::$page_instance->output();
    }

    public function setCurrentPage($page) {
        $this->_current_page = intval($page);
    }
    public function setTotalNum($num) {
        $this->_total_num = intval($num);
    }
    public function setPageSize($pagesize) {
        $this->_pagesize = intval($pagesize);
    }
    public function setCurrentUrl($current_url='') {
        if ( ! $current_url) {
            $current_url = $_SERVER["REQUEST_URI"];
            $parse_url   = parse_url($current_url);

            if ( isset($parse_url["query"]) ) {
                $current_url = preg_replace('/page=[^&]*/',"page=%s",$current_url);

            } else {
                $current_url.="?page=%s";
            }
            $this->_current_url = $current_url;
        } else {
            $this->_current_url = rtrim($current_url, '/') . '/';
        }
    }
    public function output() {
        if ( ! $this->_current_url ) {
            $this->setCurrentUrl();
        }
        
        $this->_caculateParam();
        $this->_buildOutput();
        return $this->_pagestring;
    }
    private function _caculateParam() {
        if (!$this->_total_num) return array();
        $this->_total_pages = ceil($this->_total_num / $this->_pagesize);
        $this->_current_page < 1 && $this->_current_page = 1;
        $this->_current_page > $this->_total_pages && $this->_current_page = $this->_total_pages;
        //Make sure _pagelen is odd number.
        $this->_pagelen = $this->_pagelen % 2 ? $this->_pagelen : $this->_pagelen + 1;
        $this->_pageoffset = ($this->_pagelen - 1) / 2;
    }
    private function _buildOutput() {
        $this->_pagestring  = '';
        $this->_pagestring .= $this->_pageclass ? '<div class="' . $this->_pageclass . '">' : '<div>';
        $this->_pagestring.= sprintf($this->total_num_str,$this->_total_num);
        $this->_buildOutputPageList();
        $this->_pagestring .= '&nbsp;&nbsp;转到&nbsp;&nbsp;<input size="3" title="" type="text">&nbsp;&nbsp;页';
        $this->_pagestring .= '</div>';
    }
    private function _buildOutputPageList() {
        $pagemin = 1;
        $pagemax = $this->_total_pages;
        
        if( $this->_current_page != 1 ) {
            $prev = $this->_current_page-1 > 1 ? $this->_current_page-1 : 1;
            $this->_pagestring .= "<a href='".$this->getPageUrl(1)."'>".$this->first_page."</a>
            <a href='".$this->getPageUrl($prev)."'>".$this->prev_page."</a>";
        } else {
            $this->_pagestring .= "<a href=\"javascript:;\">".$this->first_page."</a>
            <a href=\"javascript:;\">".$this->prev_page."</a>";
        }
        
        //Ensure page offset number
        if($this->_total_pages > $this->_pagelen){
            if ($this->_current_page <= $this->_pageoffset) {
                $pagemin = 1;
                $pagemax = $this->_pagelen;
            } else {
                if($this->_current_page + $this->_pageoffset >= $this->_total_pages + 1){
                    $pagemin = $this->_total_pages - $this->_pagelen + 1;
                    $pagemax = $this->_total_pages;
                } else {
                    $pagemin = $this->_current_page - $this->_pageoffset;
                    $pagemax = $this->_current_page + $this->_pageoffset;
                }
            }
        }
        for($i = $pagemin; $i <= $pagemax; $i++){
            if($i == $this->_current_page){
                $this->_pagestring .= "<span class=\"current\">{$i}</span>"; //
            } else {
                $this->_pagestring .= "<a href='".$this->getPageUrl($i)."'>".$i."</a>";
            }
        }
        if( $this->_current_page != $this->_total_pages){
            $next = $this->_current_page+1 > $this->_total_pages ? $this->_total_pages : $this->_current_page+1;
            $this->_pagestring .= "<a href='".$this->getPageUrl($next)."'>".$this->next_page."</a>";
            $this->_pagestring .= "<a href='".$this->getPageUrl($this->_total_pages)."'>".$this->last_page."</a>";
        } else {
            $this->_pagestring .= "
                <a href=\"javascript:;\">".$this->next_page."</a>
                <a href=\"javascript:;\">".$this->last_page."</a>";
        }

    }

    public function getPageUrl($page){
        $url =  sprintf($this->_current_url,$page);
        return $url;
    }
}
