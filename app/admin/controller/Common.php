<?php
namespace app\admin\Controller;

use think\Controller;

class Common
{
    /* 退出登录 */
    public function logout(){

        if(is_login()){
            model('Member')->logout();
            session('[destroy]');
            $this->success(lang('_EXIT_SUCCESS_'), url('login'));
        } else {
            $this->error('error');
        }
    }

    /**
     * 清理缓存 clear cache
     * @return [type] [description]
     */
    public function clear_cache(){

        $dirname = ROOT_PATH.'runtime/';

        //echo $dirname;exit;

        //清文件缓存
        $dirs   =   array($dirname);

        if(function_exists('memcache_init')){
            $mem = memcache_init();
            $mem->flush();
        }
        header('Content-Type:text/html;charset=utf-8');
        //清理缓存
        foreach($dirs as $value) {
            rmdirr($value);
            echo "".$value."\" 已经被删除!缓存清理完毕。 ";
        }

        @mkdir($dirname,0777,true);

    }

}