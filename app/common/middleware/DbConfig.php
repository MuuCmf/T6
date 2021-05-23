<?php

namespace app\common\middleware;

use think\App;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Db;
use think\Response;

class DbConfig
{
    public function handle($request, \Closure $next)
    {
        //获取数据库内配置数据
        if(strtolower(App('http')->getName())!='install'){
            //动态添加系统配置,非模块配置
            $config = Cache::get('DB_CONFIG_DATA');
            //dump($config);exit;
            if (!$config) {
                $map[] = ['status','=',1];
                $map[] = ['group','>',0];
                $data = Db::name('Config')->where($map)->field('type,name,value')->select();
                foreach ($data as $value) {
                    $config[$value['name']] = self::parse($value['type'], $value['value']);
                }
                Cache::set('DB_CONFIG_DATA', $config);
            }
            Config::set($config);
        }
        
        // 判断站点是否关闭
        if (strtolower(App('http')->getName()) != 'install' && strtolower(App('http')->getName()) != 'admin') {
            
            if (!$config['WEB_SITE_CLOSE']) {
                header("Content-Type: text/html; charset=utf-8");
                return $config['WEB_SITE_CLOSE_HINT'];exit;
            }
        }

        return $next($request);
    }

    /**
     * 根据配置类型解析配置
     * @param  integer $type  配置类型
     * @param  string  $value 配置值
     */
    private static function parse($type, $value){
        switch ($type) {
            case 3: //解析数组
                $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
                if(strpos($value,':')){
                    $value  = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value[$k]   = $v;
                    }
                }else{
                    $value =    $array;
                }
                break;
        }
        return $value;
    }   
}