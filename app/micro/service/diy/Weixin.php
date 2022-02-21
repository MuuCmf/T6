<?php
namespace app\micro\service\diy;

class Weixin
{
    // 名称
    public $_title   = '关注公众号';
    // 类型（唯一标识）
    public $_type    = 'weixin';
    // 图标
    public $_icon    = 'weixin';
    // 模板文件
    public $_template     = [
        'script' =>  APP_PATH . 'micro/view/diy/weixin/script.html',
        'block' =>  APP_PATH . 'micro/view/diy/weixin/block.html',
        'view' =>  APP_PATH . 'micro/view/diy/weixin/view.html',
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
                'css' => PUBLIC_PATH . '/static/micro/diy/mobile/weixin.min.css',
                'js' => PUBLIC_PATH . '/static/micro/diy/mobile/weixin.min.js',
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
        if(empty($data['style'])) $data['style'] = 0; //样式默认为0
        $data['data']['title'] = config('system.WEB_SITE_NAME');
        $data['data']['desc'] = config('system.WEB_SITE_DESCRIPTION');
        $data['data']['logo'] = get_attachment_src(config('system.WEB_SITE_LOGO'));

        return $data;
    }
}