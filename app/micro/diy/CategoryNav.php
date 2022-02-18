<?php
namespace app\micro\diy;

use app\micro\service\Diy;

class CategoryNav
{
    // 名称
    public $_title   = '图文导航';
    // 类型（唯一标识）
    public $_type    = 'category_nav';
    // 图标
    public $_icon    = 'th';
    // 模板文件
    public $_template     = [
        'script' =>  APP_PATH . 'micro/view/diy/category_nav/script.html',
        'block' =>  APP_PATH . 'micro/view/diy/category_nav/block.html',
        'view' =>  APP_PATH . 'micro/view/diy/category_nav/view.html',
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
                'css' => request()->domain() . '/static/micro/diy/mobile/category_nav.min.css',
                'js' => request()->domain() . '/static/micro/diy/mobile/category_nav.min.js',
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
        foreach($data['data'] as &$v){

            if(empty($v['icon_url'])){
                $v['icon_url'] = request()->domain() . '/static/classroom/images/diy/noimg.png';
            }else{
                $v['icon_url'] = get_attachment_src($v['icon_url']);
            }
            if(!empty($v['link'])){
                $v['link']['url'] = (new Diy())->linkToUrl($v['link']);
            }

        }
        return $data;
    }
}