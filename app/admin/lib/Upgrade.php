<?php
namespace app\admin\lib;

use app\common\model\Module;
use think\Exception;
use think\facade\Db;
use think\exception\ValidateException;

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
        $ignore_paths = ['.env','config','runtime','.idea','.gitignore','vendor','data/version.ini','_src'];
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
        $source .= "&auth_code=" . urlencode(Cloud::authCode());
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
     * 生成本地升级json包
     */
    public function buildJson($app_name, $version, $auth_code)
    {
        $url = $this->api;
        if ($app_name == 'system') {
            $url = $url . "upgrade/system/version";
        } else {
            $url = $url . "upgrade/app/version";
        }
        $result = curl_request($url,[
            'app_name'  =>  $app_name,
            'version' =>  $version,
            'auth_code' => $auth_code
        ]);
        try {
            $result = json_decode($result,true);
            if (is_array($result) && $result['code'] == 200){
                $files = $result['data']['data'];
                $files_allowed = [];
                if (!empty($files)) {
                    foreach ($files as $file) {
                        $entry = root_path() . $file['name'];
                        if (!is_file($entry) || md5_file($entry) != $file['md5']) {
                            $files_allowed[] = $file;
                        }
                    }
                    $result['data']['data'] = $files_allowed;
                    $result['data']['total'] = count($files_allowed);
                }else{
                    $result['data']['data'] = $files;
                    $result['data']['total'] = count($files);
                }
                
                return $result;
            }else{
                return $result;
            }
        } catch (ValidateException $e) {
            return false;
        }

        return false;
    }


}