<?php
namespace app\micro\diy;

use app\common\model\module;

class Category
{
    // 名称
    public $_title   = '分类';
    // 类型（唯一标识）
    public $_type    = 'category';
    // 图标
    public $_icon    = 'exchange';
    // 模板文件
    public $_template     = [
        'script' =>  APP_PATH . 'micro/view/diy/category/script.html',
        'block' =>  APP_PATH . 'micro/view/diy/category/block.html',
        'view' =>  APP_PATH . 'micro/view/diy/category/view.html',
    ];
    // 静态资源
    public $_static = [];
    // API接口
    public $_api = [];

/**
     * 构造方法
     */
    public function __construct()
    {
        $this->_static = $this->setStatic();
    }

    public function setStatic()
    {
        return [
            'mobile' => [
                'css' => request()->domain() . '/static/micro/diy/mobile/category.min.css',
                'js' => request()->domain() . '/static/micro/diy/mobile/category.min.js',
            ],
            'pc' => [
                'css' => '',
                'js' => ''
            ]
        ];
    }

    /**
     * 约定数据处理方法
     */
    public function handle($data, $shopid)
    {    
        $category_tree = [];
        // 默认给文章模块分类数据
        $app = !empty($data['data']['app'])?$data['data']['app']:'articles';
        // 判断APP是否安装并启用
        $installed = (new module())->checkInstalled($app);
        // 应用已安装
        if($installed){
            // 绑定到容器
            bind($app . '\\category_tree', 'app\\' . $app . '\\model\\' .ucwords($app).  'Category');

            if(app($app . '\\category_tree')){
                //$category_tree = app($app . '\\category_tree')->getTree(1);
                $map[] = ['status', '=', 1];
                $list = app($app . '\\category_tree')->getList($map, 999, 'sort desc,create_time desc');
                $list = $list->toArray();
                $list = list_to_tree($list);
                $data['data']['tree'] = $list;
            }
        }
        return $data;
    }
}