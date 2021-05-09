<?php

namespace app\common\middleware;

use think\facade\Session;
use think\facade\Request;
use think\facade\Config;
use think\facade\Request;
use think\Response;
use think\exception\HttpResponseException;

class Config
{
    public function handle()
    {
        dump(App('http'));exit;
        if(strtolower(App('http')->getName())!='install'){
            //动态添加系统配置,非模块配置
            $config = Cache::get('DB_CONFIG_DATA');
            if (!$config) {
                $map['status'] = 1;
                $map['group']=['>',0];
                $data = Db::name('Config')->where($map)->field('type,name,value')->select();
                
                foreach ($data as $value) {
                    $config[$value['name']] = self::parse($value['type'], $value['value']);
                }
                Cache::set('DB_CONFIG_DATA', $config);
            }
            Config::set($config); //动态添加配置
        }
        // 判断站点是否关闭
        if (strtolower(App('http')->getName()) != 'install' && strtolower(App('http')->getName()) != 'admin') {
            if (!Config::get('WEB_SITE_CLOSE')) {
                header("Content-Type: text/html; charset=utf-8");
                echo Config::get('WEB_SITE_CLOSE_HINT');exit;
            }
        }

        // app_trace 调试模式后台设置
        if (Config::get('show_page_trace'))
        {
            Config::set('app_trace', true);
        }
        // app_debug 开发者调试模式
        if (Config::get('develop_mode'))
        {
            Config::set('app_debug', true);
        }
        // 如果是开发模式那么将异常模板修改成官方的
        if (Config::get('app_debug'))
        {
            Config::set('exception_tmpl', THINK_PATH . 'tpl' . DS . 'think_exception.tpl');
        }
        // 如果是trace模式且Ajax的情况下关闭trace
        if (Config::get('app_trace') && $request->isAjax())
        {
            Config::set('app_trace', false);
        }
    }
}