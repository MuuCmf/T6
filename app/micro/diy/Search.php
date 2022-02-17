<?php
namespace app\micro\diy;

class Search
{
    // 名称
    public $_title   = '搜索';
    // 类型（唯一标识）
    public $_type    = 'search';
    // 图标
    public $_icon    = 'list-alt';
    // 模板文件
    public $_template     = [
        'script' =>  APP_PATH . 'micro/view/diy/search/script.html',
        'block' =>  APP_PATH . 'micro/view/diy/search/block.html',
        'view' =>  APP_PATH . 'micro/view/diy/search/view.html',
    ];
    // 静态资源
    public $_static = [
        'css' => '',
        'js' => ''
    ];
    // API接口
    public $_api = [];

    /**
     * 构造方法
     */
    public function __construct()
    {
        
    }

    /**
     * 约定数据处理方法
     */
    public function handle($data, $shopid)
    {    
        return $data;
    }
}