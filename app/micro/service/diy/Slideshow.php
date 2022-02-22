<?php
namespace app\micro\service\diy;

use app\micro\service\Diy;
use app\micro\service\Link;

class Slideshow
{
    // 名称
    public $_title   = '轮播';
    // 类型（唯一标识）
    public $_type    = 'slideshow';
    // 图标
    public $_icon    = 'file-powerpoint-o';
    // 模板文件
    public $_template     = [
        'script' =>  APP_PATH . 'micro/view/diy/slideshow/script.html',
        'block' =>  APP_PATH . 'micro/view/diy/slideshow/block.html',
        'view' =>  APP_PATH . 'micro/view/diy/slideshow/view.html',
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
                'css' => PUBLIC_PATH . '/static/micro/diy/mobile/slideshow.min.css',
                'js' => PUBLIC_PATH . '/static/micro/diy/mobile/slideshow.min.js',
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
        if(!empty($data['data'])){//数据不为空时执行
            foreach($data['data'] as &$v){
                $v['img_url'] = get_attachment_src($v['img_url']);
                if(!empty($v['link'])){
                    $v['link']['url'] = (new Link())->linkToUrl($v['link']);
                }
            }
        }
        return $data;
    }
}