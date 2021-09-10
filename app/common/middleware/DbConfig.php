<?php

namespace app\common\middleware;

use think\App;
use think\Response;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Db;

class DbConfig
{
    public function handle($request, \Closure $next)
    {
        //获取数据库内配置数据
        if(strtolower(App('http')->getName())!='install'){
            //动态添加系统配置,非模块配置
            $sys_config = Cache::get('MUUCMF_SYS_CONFIG_DATA');
            if (empty($sys_config)) {
                $map[] = ['status','=',1];
                //$map[] = ['group','>',0];
                $data = Db::name('Config')->where($map)->field('type,name,value')->select()->toArray();
                foreach ($data as $value) {
                    $sys_config[$value['name']] = self::parse($value['type'], $value['value']);
                }
                Cache::set('MUUCMF_SYS_CONFIG_DATA', $sys_config);
            }
            if (!empty($sys_config)) {
                Config::set($sys_config,'system');
            }
            
            //动态添加扩展配置,非模块配置
            $ext_config = Cache::get('MUUCMF_EXT_CONFIG_DATA');
            if (empty($ext_config)) {
                $map[] = ['status','=',1];
                //$map[] = ['group','>',0];
                $data = Db::name('ExtendConfig')->where($map)->field('type,name,value')->select()->toArray();
                foreach ($data as $value) {
                    $ext_config[$value['name']] = self::parse($value['type'], $value['value']);
                }
                Cache::set('MUUCMF_EXT_CONFIG_DATA', $ext_config);
            }
            if (!empty($ext_config)) {
                Config::set($ext_config,'extend');
            }
        }
        
        // 判断站点是否关闭
        if (strtolower(App('http')->getName()) != 'install' && strtolower(App('http')->getName()) != 'admin') {
            
            if (!Config::get('system.WEB_SITE_CLOSE')) {
                header("Content-Type: text/html; charset=utf-8");
                return Config::get('system.WEB_SITE_CLOSE_HINT');
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
            case 'entity': //解析成数组
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