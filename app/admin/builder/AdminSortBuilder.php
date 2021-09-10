<?php
namespace app\admin\builder;

use think\facade\Db;
use think\facade\View;

class AdminSortBuilder extends AdminBuilder {

    private $_title;
    private $_list;
    private $_buttonList;
    private $_savePostUrl;

    public function __construct()
    {
        parent::__construct();
    }

    public function title(String $title) {
        $this->_title = $title;

        return $this;
    }

    public function data($list) {
        $this->_list = $list;

        return $this;
    }

    public function button($title, $attr=array()) {
        $this->_buttonList[] = ['title'=>$title, 'attr'=>$attr];

        return $this;
    }

    public function buttonSubmit($url, $title='确定') {
        $this->savePostUrl($url);

        $attr = [];
        $attr['class'] = "btn btn-success sort_confirm submit-btn";
        $attr['type'] = 'button';
        $attr['target-form'] = 'form-horizontal';

        return $this->button($title, $attr);
    }

    public function buttonBack($url=null, $title='返回') {
        //默认返回当前页面
        if(!$url) {
            $url = $_SERVER['HTTP_REFERER'];
        }

        //添加按钮
        $attr = array();
        $attr['href'] = $url;
        $attr['onclick'] = 'javascript: location.href=$(this).attr("href");';
        $attr['class'] = 'sort_cancel btn btn-return';

        return $this->button($title, $attr);
    }

    public function savePostUrl($url) {
        $this->_savePostUrl = $url;
    }

    /**
     * 输出至模板
     */
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {
        //编译按钮的属性
        foreach($this->_buttonList as &$e) {
            $e['attr'] = $this->compileHtmlAttr($e['attr']);
        }
        unset($e);

        //设置meta标题
        $this->setTitle($this->_title);
        //显示页面
        View::assign('title', $this->_title);
        View::assign('list', $this->_list);
        View::assign('buttonList', $this->_buttonList);
        View::assign('savePostUrl', $this->_savePostUrl);

        parent::display('sort');
    }

    /**
     * 排序
     */
    public function doSort($table, $ids) {
        $ids = explode(',', $ids);
        $res = 0;
        foreach ($ids as $key=>$value){
            $res += Db::name($table)->where(['id'=>$value])->setField('sort', $key+1);
        }
        //dump($res);exit;
        if(!$res) {
            $this->error('未修改排序或排序错误');
        } else {
            $this->success('排序成功', cookie('__SELF__'));
        }
    }
}