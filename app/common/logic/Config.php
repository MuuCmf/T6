<?php
namespace app\common\logic;

use app\channel\logic\OfficialAccount;
use app\channel\model\WechatConfig;
use app\common\model\Module;

class Config extends Base
{
    //前端所需参数
    protected $_frontend_params = [
        //站点基础配置
        'SITE_CLOSE',
        'SITE_CLOSE_HINT',
        'WEB_SITE_STYLE',
        'WEB_SITE_NAME',
        'WEB_SITE_DESCRIPTION',
        'WEB_SITE_LOGO',
        'WEB_SITE_LOGO_ORIGIN',
        'WEB_SITE_LOGO_100',
        'WEB_SITE_LOGO_200',
        'WEB_SITE_LOGO_300',
        'WEB_SITE_LOGO_400',
        'WEB_SITE_LOGO_800',
        'WEB_SITE_ICP',
        'WEB_SITE_GICP',
        'COPYRIGHT_MAIN',
        'COPYRIGHT_WEBSITE',
        //客服信息
        'SERVICE_TEL',
        'SERVICE_CONSULT',
        'SERVICE_BUSINESS',
        'SERVICE_KF_QRCODE',
        'SERVICE_KF_QRCODE_ORIGIN',
        'SERVICE_KF_QRCODE_100',
        'SERVICE_KF_QRCODE_200',
        'SERVICE_KF_QRCODE_300',
        'SERVICE_KF_QRCODE_400',
        'SERVICE_KF_QRCODE_800',
        'SERVICE_WEIXIN_QRCODE',
        'SERVICE_WEIXIN_QRCODE_ORIGIN',
        'SERVICE_WEIXIN_QRCODE_100',
        'SERVICE_WEIXIN_QRCODE_200',
        'SERVICE_WEIXIN_QRCODE_300',
        'SERVICE_WEIXIN_QRCODE_400',
        'SERVICE_WEIXIN_QRCODE_800',
        'SERVICE_WEIXINKF',
        //提现
        'WITHDRAW_STATUS',
        'WITHDRAW_TAX_RATE',
        'WITHDRAW_DAY_NUM',
        'WITHDRAW_MIN_PRICE',
        'WITHDRAW_MAX_PRICE',
        //用户
        'USER_REG_AGREEMENT',
        'USER_REG_SWITCH',
        'USER_LOGIN_SWITCH',
        'USER_MOBILE_BIND'
    ];


    /**
     * @title 前台数据处理
     * @param $shopid
     * @return array
     */
    public function frontend($shopid = 0){
        $config = [];
        //获取基础配置
        $base_config = $this->handle()['system'];
        foreach ($this->_frontend_params as $key){
            if (array_key_exists($key,$base_config)){
                $config[$key] = $base_config[$key];
            }
        }

        //获取提现配置
        $withdraw_config = config('extend');
        foreach ($this->_frontend_params as $key){
            if (array_key_exists($key,$withdraw_config)){
                $config[$key] = $withdraw_config[$key];
            }
        }

        //获取公众号配置
        //$config['weixin_h5'] = $this->weixinH5($shopid );

        //获取已安装模块列表
        //$config['module'] = $this->app();
        return $config;
    }

    /**
     * 获取公众号配置
     */
    public function weixinH5($shopid = 0)
    {
        //获取公众号配置
        $weixin_h5 = (new WechatConfig())->where('shopid',$shopid)->field('title,desc,cover,qrcode,appid')->find();
        if ($weixin_h5){
            $weixin_h5 = $weixin_h5->toArray();
            $weixin_h5 = (new OfficialAccount())->formatData($weixin_h5);
        }
        $config['weixin_h5'] = $weixin_h5 ?? [];

        return $weixin_h5;
    }

    /**
     * 获取已安装应用列表
     */
    public function app()
    {
        //获取已安装模块列表
        $map = [
            ['is_setup', '=', 1]
        ];
        $list = (new Module())->where($map)->field('name')->select()->toArray();
        $arr = array_column($list,'name');

        return $arr;
    }

    public function handle()
    {
        $config = config();
        if(!empty($config['system']['WEB_SITE_LOGO'])){
            
            $width = 100;
            $config['system']['WEB_SITE_LOGO_ORIGIN'] = get_attachment_src($config['system']['WEB_SITE_LOGO']);
            $config['system']['WEB_SITE_LOGO_100'] = get_thumb_image($config['system']['WEB_SITE_LOGO'], intval($width));
            $config['system']['WEB_SITE_LOGO_200'] = get_thumb_image($config['system']['WEB_SITE_LOGO'], intval($width*2));
            $config['system']['WEB_SITE_LOGO_300'] = get_thumb_image($config['system']['WEB_SITE_LOGO'], intval($width*3));
            $config['system']['WEB_SITE_LOGO_400'] = get_thumb_image($config['system']['WEB_SITE_LOGO'], intval($width*4));
            $config['system']['WEB_SITE_LOGO_800'] = get_thumb_image($config['system']['WEB_SITE_LOGO'], intval($width*8));
        }

        if(!empty($config['system']['SERVICE_KF_QRCODE'])){
            $width = 100;
            $height = 100;
            $config['system']['SERVICE_KF_QRCODE_ORIGIN'] = get_attachment_src($config['system']['SERVICE_KF_QRCODE']);
            $config['system']['SERVICE_KF_QRCODE_100'] = get_thumb_image($config['system']['SERVICE_KF_QRCODE'], intval($width), intval($height));
            $config['system']['SERVICE_KF_QRCODE_200'] = get_thumb_image($config['system']['SERVICE_KF_QRCODE'], intval($width*2), intval($height*2));
            $config['system']['SERVICE_KF_QRCODE_300'] = get_thumb_image($config['system']['SERVICE_KF_QRCODE'], intval($width*3), intval($height*3));
            $config['system']['SERVICE_KF_QRCODE_400'] = get_thumb_image($config['system']['SERVICE_KF_QRCODE'], intval($width*4), intval($height*4));
            $config['system']['SERVICE_KF_QRCODE_800'] = get_thumb_image($config['system']['SERVICE_KF_QRCODE'], intval($width*8), intval($height*8));
        }

        if(!empty($config['system']['SERVICE_WEIXIN_QRCODE'])){
            $width = 100;
            $height = 100;
            $config['system']['SERVICE_WEIXIN_QRCODE_ORIGIN'] = get_attachment_src($config['system']['SERVICE_WEIXIN_QRCODE']);
            $config['system']['SERVICE_WEIXIN_QRCODE_100'] = get_thumb_image($config['system']['SERVICE_WEIXIN_QRCODE'], intval($width), intval($height));
            $config['system']['SERVICE_WEIXIN_QRCODE_200'] = get_thumb_image($config['system']['SERVICE_WEIXIN_QRCODE'], intval($width*2), intval($height*2));
            $config['system']['SERVICE_WEIXIN_QRCODE_300'] = get_thumb_image($config['system']['SERVICE_WEIXIN_QRCODE'], intval($width*3), intval($height*3));
            $config['system']['SERVICE_WEIXIN_QRCODE_400'] = get_thumb_image($config['system']['SERVICE_WEIXIN_QRCODE'], intval($width*4), intval($height*4));
            $config['system']['SERVICE_WEIXIN_QRCODE_800'] = get_thumb_image($config['system']['SERVICE_WEIXIN_QRCODE'], intval($width*8), intval($height*8));
        }


        return $config;
    }
}