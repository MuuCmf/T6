<?php
/**
 * 系统配置项数据处理
 */
namespace app\common\logic;

class config
{
    public function handle()
    {   
        $config = config();
        if(!empty($config['system']['WEB_SITE_LOGO'])){
            $width = 100;
            $height = 100;
            $config['system']['WEB_SITE_LOGO_100'] = get_thumb_image($config['system']['WEB_SITE_LOGO'], intval($width), intval($height));
            $config['system']['WEB_SITE_LOGO_200'] = get_thumb_image($config['system']['WEB_SITE_LOGO'], intval($width*2), intval($height*2));
            $config['system']['WEB_SITE_LOGO_300'] = get_thumb_image($config['system']['WEB_SITE_LOGO'], intval($width*3), intval($height*3));
            $config['system']['WEB_SITE_LOGO_400'] = get_thumb_image($config['system']['WEB_SITE_LOGO'], intval($width*4), intval($height*4));
            $config['system']['WEB_SITE_LOGO_800'] = get_thumb_image($config['system']['WEB_SITE_LOGO'], intval($width*8), intval($height*8));
        }

        if(!empty($config['system']['SERVICE_QRCODE'])){
            $width = 100;
            $height = 100;
            $config['system']['SERVICE_QRCODE_100'] = get_thumb_image($config['system']['SERVICE_QRCODE'], intval($width), intval($height));
            $config['system']['SERVICE_QRCODE_200'] = get_thumb_image($config['system']['SERVICE_QRCODE'], intval($width*2), intval($height*2));
            $config['system']['SERVICE_QRCODE_300'] = get_thumb_image($config['system']['SERVICE_QRCODE'], intval($width*3), intval($height*3));
            $config['system']['SERVICE_QRCODE_400'] = get_thumb_image($config['system']['SERVICE_QRCODE'], intval($width*4), intval($height*4));
            $config['system']['SERVICE_QRCODE_800'] = get_thumb_image($config['system']['SERVICE_QRCODE'], intval($width*8), intval($height*8));
        }
        

        return $config;
    }
}