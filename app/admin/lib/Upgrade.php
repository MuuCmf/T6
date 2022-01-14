<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Upgrade.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/1/13
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\admin\lib;
use app\common\model\Module;
use think\Exception;
use think\facade\Db;
use think\File;

class Upgrade{
    public $api;
    private $app;

    function __construct($app = 'system')
    {
        $this->api = config('muucmf.cloud_api');
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
        $ignore_paths = ['./.git','./runtime','./.idea','.gitignore','data/version.ini','info/info.php','_src/css','_src/js','runtime/'];
        foreach ($ignore_paths as $item){
            if (strpos($path,$item) !== false){
                return true;
            }
        }
        return false;
    }

    /**
     * @title下载远端文件
     * @param string $source  网络地址
     * @param string $save_path 保存路径
     * @return string
     */
    public function downFile($source, $save_path = '')
    {
        //地址追加授权域名
        $source .= "&auth_code={$this->authCode()}";
        $ch = curl_init();//初始化一个cURL会话
        curl_setopt($ch, CURLOPT_URL, $source);//抓取url
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//是否显示头信息
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);//传递一个包含SSL版本的长参数
        curl_setopt($ch, CURLINFO_HEADER_OUT, true); //TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
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
        $backup_path .= date('Y') . '-' . date('m') . DIRECTORY_SEPARATOR . date('d') . DIRECTORY_SEPARATOR . time() . DIRECTORY_SEPARATOR;
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
        $result = json_decode($output, true);//转换为数组格式
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

        $sql = (new SqlFile())->getSqlFromFile($sql_path);
        if ($sql){
            foreach ($sql as $s){
                @Db::query($s);
            }
        }
        return true;
    }

    /**
     * 加密
     * @param string $string     要加密或解密的字符串
     * @param string $key        密钥，加密解密时保持一致
     * @param int    $expiry 有效时长，单位：秒
     * @return string
     */
    protected function encrypt_code($string, $expiry = 0, $key = '1234567890') {
        $ckey_length = 1;
        $key = md5($key ? $key : UC_KEY); //加密解密时这个是不变的
        $keya = md5(substr($key, 0, 16)); //加密解密时这个是不变的
        $keyb = md5(substr($key, 16, 16)); //加密解密时这个是不变的
        $keyc = $ckey_length ?  substr(md5(microtime()), -$ckey_length) : '';
        $cryptkey = $keya . md5($keya . $keyc); //64
        $key_length = strlen($cryptkey); //64

        $string =sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) { //字母表 64位后重复 数列 范围为48~122
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) { //这里是一个打乱算法
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $result .= chr(ord($string[$i]) ^ ($box[$i]));

        }
        $str =  $keyc . str_replace('=', '', base64_encode($result));
        //  $str =htmlentities($str, ENT_QUOTES, "UTF-8"); // curl 访问出错
        return $str ;
    }

    /**
     * @title 生成授权码
     * @return string
     */
    public function authCode(){
        $web_domain = request()->host();
        $web_host   = request()->ip();
        $lock_str   = $web_domain . '|' . $web_host;
        return $this->encrypt_code($lock_str ,6000,'muucmf_tp6');
    }
}