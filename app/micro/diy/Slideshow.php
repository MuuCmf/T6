<?php
namespace app\micro\diy;

use app\micro\service\Diy;

class Slideshow
{
    // 名称
    public $_title   = '轮播';
    // 类型（唯一标识）
    public $_type    = 'slideshow';
    // 图标
    public $_icon    = 'list-alt';
    // 模板文件
    public $_template     = [
        'script' =>  APP_PATH . 'micro/view/diy/slideshow/script.html',
        'block' =>  APP_PATH . 'micro/view/diy/slideshow/block.html',
        'view' =>  APP_PATH . 'micro/view/diy/slideshow/view.html',
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
        foreach($data['data'] as &$v){
            $v['img_url'] = get_attachment_src($v['img_url']);
            if(!empty($v['link'])){
                $v['link']['url'] = (new Diy())->linkToUrl($v['link']);
            }
        }

        return $data;
    }
}