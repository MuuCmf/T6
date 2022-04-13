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

    //安装第一步，检测运行所需的环境设置
    public function step1(){
        //初始session
        session('db_config',null);
        session('admin_info',null);
        session('config_file',null);
        session('error', false);
        //环境检测
        $muu_env = check_env();
        //函数依赖检测
        $func = check_func();
        //数据库配置文件
        $root = root_path();
        
        //目录文件读写检测
        if(is_really_writable($root)){
            $dirfile = check_dirfile();
            View::assign('dirfile', $dirfile);
        }
        session('step', 1);
        if(isset($muu_env)){
            View::assign('muu_env', $muu_env);
        }
        View::assign('func', $func);
        return View::fetch();
    }

    //安装第二步，创建数据库
    public function step2($db = null, $admin = null){
        
        if(request()->isPost()){

            //检测管理员信息
            if(!is_array($admin) || empty($admin[0]) || empty($admin[1]) || empty($admin[3])){
                return $this->error('请填写完整管理员信息');
            } else if($admin[1] != $admin[2]){
                return $this->error('确认密码和密码不一致');
            } else {
                $info = array();
                list($info['username'], $info['password'], $info['repassword'], $info['email']) = $admin;
                //缓存管理员信息
                session('admin_info', $info);
            }

            //检测数据库配置
            if(!is_array($db) || empty($db[0]) ||  empty($db[1]) || empty($db[2]) || empty($db[3])){
                return $this->error('请填写完整的数据库配置');
            } else {
                
                //$dbname = $db[2];
                //数据库配置
                $dbconfig['type']     = $db[0];
                $dbconfig['hostname'] = $db[1];
                $dbconfig['database'] = $db[2];
                $dbconfig['username'] = $db[3];
                $dbconfig['password'] = $db[4];
                $dbconfig['hostport'] = $db[5];
                $dbconfig['prefix'] = $db[6];
                $dbconfig['charse'] = 'utf8';
                //设置数据库配置
                set_database_config($dbconfig);
                // 创建数据库连接
                $db_instance = Db::connect('mysql');
                //dump($db_instance);exit;
                // 检测数据库连接
                try {
                    $db_instance->execute('select version()');
                } catch (\Exception $e) {
                    return $this->error('数据库连接失败，请检查数据库配置！','');
                }

                //建立数据库
                $sql = "CREATE DATABASE IF NOT EXISTS `{$dbconfig['database']}` DEFAULT CHARACTER SET utf8";
                if(!$db_instance->execute($sql)){
                    return $this->error($db_instance->getError(), '' , '/install.php/index/step2');
                }
                
                //暂存数据库配置
                session('db_config', $dbconfig);
                session('step',2);
            }
            return $this->success('配置成功，进入下一步','', '/install.php/index/step3');

            //跳转到数据库安装页面
            //$this->redirect('step3');
        } else {
            if(session('error')) {
                return $this->error('环境检测没有通过，请调整环境后重试！');
            }
            session('step', 2);
            return View::fetch();
        }
    }

    // 安装第三步，安装数据表，创建配置文件
    public function step3(){
        ob_start();
        if(session('step') != 2){
            $this->redirect('step2');
        }

        echo View::fetch();

        sleep(1);
        //连接数据库
        $dbconfig = session('db_config');
        //设置数据库配置
        set_database_config($dbconfig);
        //动态连接数据库
        $db_instance = Db::connect('mysql');
        //创建数据表
        create_tables($db_instance, $dbconfig['prefix']);
        //注册创始人帐号
        $admin = session('admin_info');
        register_administrator($db_instance, $dbconfig['prefix'], $admin);
        //更新配置文件
        $conf   =   write_config($dbconfig);
        session('config_file',$conf);

        if(session('error')){
            error_btn('很遗憾，安装失败，请检测后重新安装！','btn btn-warning btn-large btn-block');
        } else {
            session('step', 3);
            echo "<script type=\"text/javascript\">setTimeout(function(){location.href='/install.php/index/complete'},5000)</script>";
            ob_flush();
            flush();
        }
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



    public function tip($info,$title='很遗憾，安装失败，失败原因'){
        View::assign('info',$info);// 提示信息
        View::assign('title',$title);
        return View::fetch('error');
    }

    public function debug()
    {
        $config = [
            "type" => "mysql",
            "hostname" => "127.0.0.1",
            "database" => "demo_t6_muucmf_c",
            "username" => "demo_t6_muucmf_c",
            "password" => "HhPwW2M2G4sZSfcT",
            "hostport" => "3306",
            "prefix" => "muucmf_",
            "charse" => "utf8"
        ];
        $dbConfigFile = root_path() . '.env';
        //读取配置内容
        $db_conf = @file_get_contents($dbConfigFile);
        //dump($db_conf);
        //dump($config);
        //把auth字串写入数组
        $callback = function($matches) use($config) {
            
            $field = $matches[1];
            $replace = $config[strtolower($field)];
            dump($matches[1]);
            return "{$matches[1]} = {$replace}";
        };
        
        //修改数据库相关配置
        $db_conf = preg_replace_callback("/(HOSTNAME|DATABASE|USERNAME|PASSWORD|HOSTPORT|PREFIX)(\s+)=(\s+)(.*)/", $callback, $db_conf);

        //$db_conf = preg_replace("/(HOSTNAME)(\s+)=(\s+)(.*)/",'HOSTNAME = '.$config['hostname'],$db_conf);
        dump($db_conf);
    }
}