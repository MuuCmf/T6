<?php
namespace app\micro\service\diy;

class Member
{
    // 名称
    public $_title   = '会员';
    // 类型（唯一标识）
    public $_type    = 'member';
    // 图标
    public $_icon    = 'user-plus';
    // 模板文件
    public $_template     = [
        'script' =>  APP_PATH . 'micro/view/diy/member/script.html',
        'block' =>  APP_PATH . 'micro/view/diy/member/block.html',
        'view' =>  APP_PATH . 'micro/view/diy/member/view.html',
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
                'css' => PUBLIC_PATH . '/static/micro/diy/mobile/member.min.css',
                'js' => PUBLIC_PATH . '/static/micro/diy/mobile/member.min.js',
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
        return $data;
    }
}