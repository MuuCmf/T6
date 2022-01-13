<?php

namespace app\admin\controller;

use app\admin\lib\MakeZip;
use app\admin\lib\Upgrade as UpgradeServer;
use think\Exception;
use think\facade\View;
use think\Response;

/**
 * 升级包制作规则
 * 压缩方式：zip
 * 升级文件命名规则：将升级文件压缩至update.zip
 * 数据库文件路径：./sql/update.sql
 */
class Update extends Admin
{
    private $UpgradeServer;
    private $app_name;//应用标识

    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->UpgradeServer = new UpgradeServer();
        $this->app_name = input('app_name', 'system');
    }

    /**
     * 系统升级首页
     * @return [type] [description]
     */
    public function index()
    {
        //读取本地版本号
        $localVersion = $this->UpgradeServer->version();
        //读取云端最新版本号
        $cloudVersion = $this->UpgradeServer->cloudVersion()['data'];
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
        //备份地址
        $filename =  date('Y') . '-' . date('m') . '/' . date('d') . '/' . time() . '.zip';
        $backup_path = $this->app_name == 'system' ? '网站根目录/data/upgrade/' : '网站根目录/app/' . $this->app_name . '/';
        $backup_path = $backup_path . $filename;
        View::assign([
            'appName' => $this->app_name,
            'localVersion' => $this->UpgradeServer->version(),
            'upgradeVersion' => input('version'),
            'backupPath' => $backup_path
        ]);
        return \view();
    }

    /**
     * @title 升级
     * @return Response|void
     */
    public function upgrade()
    {
        if (request()->isAjax()) {
            $params = request()->param();
            $path = $params['file'];//文件路径
            $md5 = $params['md5'];
            $appid = $params['appid'];//应用
            $app_name = $this->app_name;//应用类型
            $version = $params['version'];//应用类型
            $local_path = root_path() . $path;
            if ($version == $this->UpgradeServer->version($app_name)) $this->success('无需升级', 'same_version');
            try {
                //检查忽略文件
                $ignore = $this->UpgradeServer->checkIgnoreFile($path);
                if ($ignore) {
                    return $this->success('success');
                }
                //对比文件
                if (file_exists($local_path)) {
                    $upgrade = !boolval($md5 == @md5_file($local_path));
                } else {
                    $upgrade = true;
                }
                //md5不同，请求远端文件
                if ($upgrade) {
                    $source = $this->UpgradeServer->api . "/upgrade/download?md5={$md5}&appid={$appid}&app_name={$app_name}&version={$version}";
                    $this->UpgradeServer->downFile($source, $local_path);
                }
                return $this->success('success');
            } catch (Exception $e) {
                return $this->error($e->getMessage());
            }
        }
    }

    /**
     * @title 更新完成
     * @return Response|void
     */
    public function finish()
    {
        if (request()->isAjax()) {
            $params = request()->post();
            try {
                $local_path = root_path() . $params['path'];
                if ($params['skip'] == 0) {
                    //替换版本文件
                    $source = $this->UpgradeServer->api . "/upgrade/download?md5={$params['md5']}&appid={$params['appid']}&app_name={$this->app_name}&version={$params['version']}";
                    $this->UpgradeServer->downFile($source, $local_path);
                    if ($this->app_name != 'system') {
                        //更新应用版本号
                        \app\common\model\Module::where('name', $this->app_name)->update(['version' => $params['version']]);
                    }
                }

                //执行sql
                $this->UpgradeServer->executeUpgradeSql($this->app_name);
                return $this->success('升级完成');
            } catch (Exception $e) {
                return $this->error($e->getMessage());
            }
        }
    }

    public function backup()
    {
        if (request()->isAjax()) {
            $zip = new MakeZip();
            try {
                $root_path = $this->UpgradeServer->getAppRootPath($this->app_name);
                if ($this->app_name == 'system'){
                    $backup_path = $root_path . 'data' . DIRECTORY_SEPARATOR . 'upgrade' . DIRECTORY_SEPARATOR;
                }else{
                    $backup_path = $root_path . 'info' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR;
                }
                $backup_path .= date('Y') . '-' . date('m') . DIRECTORY_SEPARATOR . date('d') . DIRECTORY_SEPARATOR;
                //检查目录
                if (!file_exists($backup_path)) {
                    mkdir($backup_path, 0777, true);
                    chmod($backup_path, 0777);
                }
                $backup_path .= time() . '.zip';
                $res = $zip->zipFolder($root_path, $backup_path);
                if (!$res) {
                    throw new Exception('压缩失败');
                }
                $this->success('备份成功');
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }
}