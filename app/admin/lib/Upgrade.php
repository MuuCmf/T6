<?php
namespace app\admin\lib;

use app\common\model\Module;
use think\Exception;
use think\facade\Db;
use think\File;

class Upgrade
{   
    public $api;
    private $app;

    function __construct($app = 'system')
    {
        $this->api = config('cloud.api');
        $this->app = $app;
    }

    /**
     * @title 获取应用根目录
     * @param string $app
     * @return string
     */
    public function getAppRootPath($app = 'system'){
        if ($app == 'system'){
            $path = root_path();
        }else{
            $path = root_path("app" . DIRECTORY_SEPARATOR . $app);
        }
        return $path;
    }

    /**
     * @title 获取版本号
     * @param string $app
     * @return false|string
     */
    public function version($app = 'system')
    {
        if ($app == 'system'){
            $path = $this->getAppRootPath($app) . 'data' . DIRECTORY_SEPARATOR . 'version.ini';
            $version = file_get_contents($path);
        }else{
            $version = Module::where('name',$app)->value('version');
        }
        return $version;
    }

    /**
     * @title 检查忽略文件
     * @param $path
     * @return bool
     */
    public function checkIgnoreFile($path){
        $ignore_paths = ['.env','runtime','.idea','.gitignore','data/version.ini','info/info.php','_src'];
        foreach ($ignore_paths as $item){
            if (strpos($path,$item) !== false){
                return true;
            }
        }
        return false;
        
    }

    /**
     * @title下载远端文件
     * @param string $params  参数
     * @param string $save_path 保存路径
     * @return string
     */
    public function downFile($params = [], $save_path = '')
    {
        $source = $this->api . "upgrade/download?" . http_build_query($params);
        //地址追加授权域名
        $source .= "&auth_code=" . Cloud::authCode();
        $ch = curl_init();//初始化一个cURL会话
        curl_setopt($ch, CURLOPT_URL, $source);//抓取url
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//是否显示头信息
        //curl_setopt($ch, CURLOPT_SSLVERSION, 3);//传递一个包含SSL版本的长参数
        curl_setopt($ch, CURLINFO_HEADER_OUT, true); //TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);// 执行一个cURL会话
        $response = curl_getinfo($ch);
        $error = curl_error($ch);//返回一条最近一次cURL操作明确的文本的错误信息。
        curl_close($ch);//关闭一个cURL会话并且释放所有资源
        
        //处理返回的错误信息
        if ($response['content_type'] != 'application/octet-stream') {
            $error = json_decode($data, true);
            throw new Exception($error['msg'],$error['code']);
        }
        if ($error) {
            throw new Exception($error);
        }
        //备份文件
        if (is_file($save_path)){
            $this->backup($save_path);
        }
        //创建目录
        $filename = basename($save_path);
        $dirname = str_replace($filename ,'' ,$save_path);
        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
            chmod($dirname, 0777);
        }
        if (file_put_contents($save_path, $data)) {
            return $save_path;
        }
        throw new Exception('创建文件失败');
    }

    /**
     * @title 备份
     * @param $file
     */
    public function backup($file){
        $root_path = $this->getAppRootPath($this->app);
        if ($this->app == 'system'){
            $backup_path = $root_path . 'data' . DIRECTORY_SEPARATOR . 'upgrade' . DIRECTORY_SEPARATOR;
        }else{
            $backup_path = $root_path . 'info' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR;
        }
        $backup_path .= date('Y') . '-' . date('m') . DIRECTORY_SEPARATOR . date('d') . DIRECTORY_SEPARATOR . $this->version($this->app) . DIRECTORY_SEPARATOR;
        $backup_path .= str_replace(root_path(),'',$file);
        //创建目录
        $filename = basename($backup_path);
        $dirname = str_replace($filename ,'' ,$backup_path);
        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
            chmod($dirname, 0777);
        }
        copy($file,$backup_path);
    }

    /**
     * @title 获取云端最新系统版本
     * @return [type] [description]
     */
    public function cloudVersion($params = [])
    {
        $api = $this->api . 'app/version';
        $output = curl_request($api, $params);
        // 初始化返回数据
        $result = [
            'code' => 0,
            'data' => [
                'version' => '服务器错误',
                'remark' => '服务器错误'
            ]
        ];
        if(is_json($output)){
            $result = json_decode($output, true);//转换为数组格式
        }
        return $result;
    }

    /**
     * @title 执行升级sql
     * @param $sql_path
     * @return bool
     */
    public function executeUpgradeSql($app = 'system')
    {
        if ($app == 'system'){
            $sql_path = $this->getAppRootPath($app) . 'data' . DIRECTORY_SEPARATOR . 'upgrade.sql';
        }else{
            $sql_path = $this->getAppRootPath($app) . 'info' . DIRECTORY_SEPARATOR . 'upgrade.sql';
        }

        $sql = (new SqlFile())->getSqlFromFile($sql_path,false,['muucmf_' => config('database.connections.mysql.prefix')]);
        if ($sql){
            foreach ($sql as $s){
                try {
                    @Db::query($s);
                }catch (\Exception $e){
                    //忽略错误 继续执行
                }
            }
        }
        return true;
    }

    /**
     * @title 生成请求升级json
     * @param $path
     * @return array
     */
    public function packageJson($app = 'system', $path = '')
    {
        global $json_upgrade;
        $path = !empty($path) ? $path  : root_path();
        
        if($path == root_path()){
            $files = $this->getAppLibrary($app);
        }else{
            $files = scandir($path);
        }
        
        foreach ($files as $file) {
            
            if ($file != '.' && $file != '..') {
                if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                    $this->packageJson($app, $path . DIRECTORY_SEPARATOR . $file);
                } else {
                    $file_path = $path . DIRECTORY_SEPARATOR . $file;
                    $name = str_replace(root_path() . DIRECTORY_SEPARATOR,'',$file_path);
                    $data = [
                        'name' => $name,
                        'md5'  => @md5_file($file_path),
                    ];
                    $json_upgrade[] = $data;
                }
            }
        }
        return $json_upgrade;
    }

    protected function getAppLibrary($app = 'system'){

        if($app == 'system'){
            $lib = [
                'app/admin',
                'app/api',
                'app/channel',
                'app/common',
                'app/index',
                'app/install',
                'app/ucenter',
                'app/common.php',
                'app/middleware.php',
                'app/provider.php',
                'app/service.php',
                'config',
                'extend',
                'public/static/admin',
                'public/static/channel',
                'public/static/common',
                'public/static/install',
                'public/static/ucenter',
                'public/index.php',
                'public/router.php',
                'route/app.php',
                'vendor',
                '.travis.yml',
                'composer.json',
                'composer.lock',
                'LICENSE.txt',
                'package.json',
                'README.md',
                'think'
            ];
        }else{
            $lib = [
                'app/' . $app,
                'public/static/' . $app
            ];
        }
        
        return $lib;
    
    }


}