<?php
namespace app\micro\diy;

class Category
{
    // 名称
    public $_title   = '分类';
    // 类型（唯一标识）
    public $_type    = 'category';
    // 图标
    public $_icon    = 'list-alt';
    // 模板文件
    public $_template     = [
        'script' =>  APP_PATH . 'micro/view/diy/category/script.html',
        'block' =>  APP_PATH . 'micro/view/diy/category/block.html',
        'view' =>  APP_PATH . 'micro/view/diy/category/view.html',
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