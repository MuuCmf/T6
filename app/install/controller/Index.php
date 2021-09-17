<?php
namespace app\install\controller;

use think\facade\Db;
use think\facade\View;

class Index extends Base
{
    //安装首页
    public function index(){
        //dump(config());exit;
        if (is_file(root_path() . 'data/install.lock'))
        {
            // 已经安装过了 执行更新程序
            $msg = '请删除install.lock文件后再运行安装程序!';
            return $this->error($msg);
        }

        return View::fetch();
    }

    //安装完成
    public function complete(){
        clearstatcache();
        
        // 写入安装锁定文件
        $lockFile = root_path() .'data/install.lock';
        $result = @file_put_contents($lockFile, 'lock');
        //创建配置文件
        View::assign('info', session('config_file'));
        session('step', null);
        session('error', null);
        session('update',null);
        
        return View::fetch();
    }
}