<?php

namespace app\admin\controller;

use think\Exception;
use think\facade\Db;
use think\facade\View;
use think\File;
use think\facade\Filesystem;
use muucmf\Database as MuucmfDb;

/**
 * 升级包制作规则
 * 压缩方式：zip
 * 升级文件命名规则：将升级文件压缩至update.zip
 * 数据库文件路径：./sql/update.sql
 */
class Update extends Admin
{
    protected $api;

    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->_initialize();
        $this->api = config('muucmf.cloud_api');
    }

    public function _initialize()
    {

    }

    /**
     * 系统升级首页
     * @return [type] [description]
     */
    public function index()
    {
        //读取本地版本号
        $localVersion = $this->version();
        //读取云端最新版本号
        $cloudVersion = $this->cloudVersion()['data'];
        $upgrade = $localVersion != $cloudVersion['version'] ? true : false;
        $this->setTitle('系统在线更新');
        View::assign('localVersion', $localVersion);
        View::assign('cloudVersion', $cloudVersion);
        View::assign('upgrade', $upgrade);

        return \view();
    }

    /*开始在线更新数据*/
    public function start()
    {
        $this->setTitle('在线更新');
        View::assign([
            'type' => input('app_type', 'system'),
            'localVersion' => $this->version(),
            'upgradeVersion' => input('version')
        ]);
        return \view();
    }

    public function upgrade()
    {
        $params = request()->param();
        $path = $params['file'];//文件路径
        $md5 = $params['md5'];
        $appid = $params['appid'];//应用
        $app_type = $params['app_type'];//应用类型
        $version = $params['version'];//应用类型
        $local_path = root_path() . $path;

        //对比文件
        if (file_exists($local_path)) {
            $upgrade = !boolval($md5 == @md5_file($local_path));
        } else {
            $upgrade = true;
        }

        //md5不同，请求远端文件
        if ($upgrade) {
            $source = $this->api . "/upgrade/download?md5={$md5}&appid={$appid}&app_type={$app_type}&version={$version}";
            try {
                $this->downFile($source, $local_path);
            } catch (Exception $e) {
                return $this->error($e->getMessage());
            }
        }
        return $this->success('success', 0);
    }

    private function downFile($source, $save_path = '')
    {
        $ch = curl_init();//初始化一个cURL会话
        curl_setopt($ch, CURLOPT_URL, $source);//抓取url
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//是否显示头信息
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);//传递一个包含SSL版本的长参数
//        curl_setopt($ch, CURLOPT_HEADER, 1); //返回response头部信息
        curl_setopt($ch, CURLINFO_HEADER_OUT, true); //TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header

        $data = curl_exec($ch);// 执行一个cURL会话
        $response = curl_getinfo($ch);
        $error = curl_error($ch);//返回一条最近一次cURL操作明确的文本的错误信息。
        curl_close($ch);//关闭一个cURL会话并且释放所有资源
        //处理返回的错误信息
        if ($response['content_type'] != 'application/octet-stream') {
            $error = json_decode($data, true);
            throw new Exception($error['msg']);
        }
        if ($error) {
            throw new Exception($error);
        }
        //文件名
        if (!file_exists($save_path)) {
            @mkdir($save_path, 0777, true);
            @chmod($save_path, 0777);
        }
        if (file_put_contents($save_path, $data)) {
            return $save_path;
        }
        return false;
    }

    /**
     * 获取云端最新系统版本
     * @return [type] [description]
     */
    private function cloudVersion()
    {
        $api = $this->api . 'app/version';
        $output = curl_request($api, []);
        $result = json_decode($output, true);//转换为数组格式
        return $result;
    }

    /*
    *更新数据库
    */
    private function updateTable($updatesql, $prefix = 'muucmf_')
    {
        $sql = File::read_file($updatesql);
        $sql = str_replace("\r\n", "\n", $sql);
        $sql = str_replace("\r", "\n", $sql);
        $sql = explode(";\n", trim($sql));
        //替换表前缀
        $orginal = config('database.prefix');
        $sql = str_replace(" `{$orginal}", " `{$prefix}", $sql);
        foreach ($sql as $value) {
            $value = trim($value);
            if (empty($value)) continue;
            if (substr($value, 0, 3) == 'SET') continue;
            if (substr($value, 0, 12) == 'CREATE TABLE') {
                $name = preg_replace("/^CREATE TABLE IF NOT EXISTS `(\w+)` .*/s", "\\1", $value);
                $msg = '创建数据表' . $name;
            }
            if (substr($value, 0, 10) == 'DROP TABLE') {
                $name = preg_replace("/^DROP TABLE IF EXISTS `(\w+)` .*/s", "\\1", $value);
                $msg = '删除数据表' . $name;
            }
            if (substr($value, 0, 11) == 'ALTER TABLE') {
                $name = preg_replace("/^ALTER TABLE IF EXISTS `(\w+)` .*/s", "\\1", $value);
                $msg = '更新数据表' . $name;
            }
            if (substr($value, 0, 11) == 'INSERT INTO') {
                $name = preg_replace("/^INSERT INTO `(\w+)` .*/s", "\\1", $value);
                $msg = '数据表' . $name . '写入数据';
            }

            if (Db::query(trim($value))) {
                $this->showMsg($msg . '...成功');
            } else {
                $this->showMsg($msg . '...失败', 'error');
            }
        }
        unset($value);
    }

}