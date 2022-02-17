<?php
namespace app\micro\diy;

class CategoryNav
{
    // 名称
    public $_title   = '图文导航';
    // 类型（唯一标识）
    public $_type    = 'category_nav';
    // 图标
    public $_icon    = 'list-alt';
    // 模板文件
    public $_template     = [
        'script' =>  APP_PATH . 'micro/view/diy/category_nav/script.html',
        'block' =>  APP_PATH . 'micro/view/diy/category_nav/block.html',
        'view' =>  APP_PATH . 'micro/view/diy/category_nav/view.html',
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