<?php
namespace app\micro\diy;

use app\micro\service\Diy;

class SingleImg
{
    // 名称
    public $_title   = '单图';
    // 类型（唯一标识）
    public $_type    = 'single_img';
    // 图标
    public $_icon    = 'image';
    // 模板文件
    public $_template     = [
        'script' =>  APP_PATH . 'micro/view/diy/single_img/script.html',
        'block' =>  APP_PATH . 'micro/view/diy/single_img/block.html',
        'view' =>  APP_PATH . 'micro/view/diy/single_img/view.html',
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
                'css' => request()->domain() . '/static/micro/diy/mobile/single_img.min.css',
                'js' => request()->domain() . '/static/micro/diy/mobile/single_img.min.js',
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
        
        $data['data']['img_url'] = get_attachment_src($data['data']['img_url']);
        if(!empty($data['data']['link'])){
            $data['data']['link']['url'] = (new Diy())->linkToUrl($data['data']['link']);
        }
        

        return $data;
    }
}