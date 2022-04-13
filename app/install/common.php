<?php
define('INSTALL_APP_PATH', dirname(dirname(__DIR__)));
//配置目录
define('CONFIG_PATH', dirname(dirname(__DIR__)) . '/config/');

/**
 * T6 系统环境检测
 * @return array 系统环境数据
 */
function check_env()
{
    $items = [
        ['name'=>'操作系统', 'type'=>'不限制', 'need'=>'类Unix', 'version'=>PHP_OS, 'icon'=>'ok'],
        ['name'=>'PHP版本', 'type'=>'7.2', 'need'=>'7.2+', 'version'=>PHP_VERSION, 'icon'=>'ok'],
        ['name'=>'附件上传', 'type'=>'不限制', 'need'=>'2M+', 'version'=>'未知', 'icon'=>'ok'],
        ['name'=>'GD库', 'type'=>'2.0', 'need'=>'2.0+', 'version'=>'未知', 'icon'=>'ok'],
        ['name'=>'Curl扩展', 'type'=>'开启', 'need'=>'不限制', 'version'=>'未知', 'icon'=>'ok'],
        ['name'=>'磁盘空间', 'type'=>'50M', 'need'=>'50M', 'version'=>'未知', 'icon'=>'ok'],
    ];

    //PHP环境检测
    if ($items[1]['version'] < $items[1]['type']) {
        $items[1]['icon'] = 'remove';
        session('error', true);
    }

    //附件上传检测
    if (@ini_get('file_uploads'))
        $items[2]['version'] = ini_get('upload_max_filesize');

    //GD库检测
    $tmp = function_exists('gd_info') ? gd_info() : array();
    if (empty($tmp['GD Version'])) {
        $items[3]['version'] = '未安装';
        $items[3]['icon'] = 'remove';
        session('error', true);
    } else {
        $items[3]['version'] = $tmp['GD Version'];
    }
    unset($tmp);
    //CURL
    $tmp = function_exists('curl_init') ? curl_version() : array();
    if (empty($tmp['version'])) {
        $items[4]['version'] = '未安装';
        $items[4]['icon'] = 'remove';
        session('curl', true);
    } else {
        $items[4]['version'] = $tmp['version'];
    }
    unset($tmp);
    //磁盘空间检测
    if (function_exists('disk_free_space')) {
        $items[5]['version'] = floor(disk_free_space(INSTALL_APP_PATH) / (1024 * 1024)) . 'M';
    }

    return $items;
}

/**
 * 目录，文件读写检测
 * @return array 检测数据
 */
function check_dirfile()
{
    $items = array(
        array('dir', '可写', 'ok', '../data'),
        array('dir', '可写', 'ok', '../public/attachment'),
        array('dir', '可写', 'ok', '../runtime'),
        //array('dir', '可写', 'ok', '../app'),
        array('file', '可写', 'ok','../.env'),
    );

    foreach ($items as &$val) {
        if ('dir' == $val[0]) {
            if (!is_writable($val[3])) {
                if (is_dir($val[3])) {
                    $val[1] = '可读';
                    $val[2] = 'remove';
                    session('error', true);
                } else {
                    $val[1] = '不存在或者不可写';
                    $val[2] = 'remove';
                    session('error', true);
                }
            }
        } else {
            if (file_exists($val[3])) {
                if (!is_writable($val[3])) {
                    $val[1] = '文件存在但不可写';
                    $val[2] = 'remove';
                    session('error', true);
                }
            } else {
                if (!is_writable(dirname($val[3]))) {
                    $val[1] = '不存在或者不可写';
                    $val[2] = 'remove';
                    session('error', true);
                }
            }
        }
    }
    return $items;
}

/**
 * 函数检测
 * @return array 检测数据
 */
function check_func()
{
    $items = [
        ['file_get_contents', '支持', 'ok'],
        ['mb_strlen', '支持', 'ok'],
        ['curl_init', '支持', 'ok'],
        ['finfo_open','支持','ok'],
        ['pathinfo','支持','ok']
    ];

    foreach ($items as &$val) {
        if (!function_exists($val[0])) {
            $val[1] = '不支持';
            $val[2] = 'remove';
            $val[3] = '开启';
            session('error', true);
        }
    }
    return $items;
}

/**
 * 设置数据库配置
 */
function set_database_config($dbconfig)
{
    // 初始化配置项
    $databaseConfig = [
        'default'         => 'mysql',
        'time_query_rule' => [],
        'auto_timestamp'  => true,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections'     => [
            'mysql' => [
                'type' => 'mysql',
            ],
        ],
    ];

    $databaseConfig['connections']['mysql'] = $dbconfig;
    config($databaseConfig, 'database');

    return true;
}

/**
 * 写入配置文件
 * @param  array $config 配置信息
 */
function write_config($config, $auth)
{
    if (is_array($config)) {
        //数据库配置文件
        $dbConfigFile = root_path() . '.env';
        //读取配置内容
        $db_conf = @file_get_contents($dbConfigFile);
        //把auth字串写入
        $callback = function($matches) use($config) {
            $field = $matches[1];
            $replace = $config[strtolower($field)];
            return "{$matches[1]} = {$replace}";
        };
        
        //修改数据库相关配置
        $db_conf = preg_replace_callback("/(HOSTNAME|DATABASE|USERNAME|PASSWORD|HOSTPORT|PREFIX)(\s+)=(\s+)(.*)/", $callback, $db_conf);
        // //修改用户加密字串配置
        // $db_conf = preg_replace_callback("/(AUTH_KEY)(\s*)=(\s*)(.*)/", function($matches) use($auth){
        //     $replace = $auth;
        //     return "{$matches[1]} = {$replace}";
        // }, $db_conf);

        //检测能否成功写入数据库配置
        $result = @file_put_contents($dbConfigFile, $db_conf);

        //写入数据库配置提示消息
        if ($result) {
            show_msg('数据库配置写入成功');
            return '';
        } else {
            show_msg('数据库配置写入失败！', 'error');
            return '由于您的环境不可写，请复制下面的配置文件内容覆盖到相关的配置文件，然后再登录后台。<p>' . root_path() . '.env</p>
            <textarea class="form-control" rows="15" name="" >' . $db_conf . '</textarea>';
        }
    }
}

/**
 * 创建数据表
 * @param  resource $db 数据库连接资源
 */
function create_tables($db_instance, $prefix = '')
{
    //读取SQL文件

    $sql = @file_get_contents(root_path() . 'app/install/data/install.sql');
    $sql = str_replace("\r", "\n", $sql);
    $sql = explode(";\n", $sql);
    //替换表前缀
    $orginal = 'muucmf_';
    $sql = str_replace(" `{$orginal}", " `{$prefix}", $sql);

    //开始安装
    show_msg('开始创建数据表...');
    foreach ($sql as $value) {
        $value = trim($value);
        if (empty($value)) continue;
        if (substr($value, 0, 12) == 'CREATE TABLE') {
            $name = preg_replace("/^CREATE TABLE IF NOT EXISTS `(\w+)` .*/s", "\\1", $value);
            $msg = "创建数据表{$name}";
            if (false !== $db_instance->execute($value)) {
                show_msg($msg . '...成功');
            } else {
                show_msg($msg . '...失败！', 'error');
                session('error', true);
                session('error_info','部分数据表写入失败，请检测后重新安装');
            }
        } else {
            $db_instance->execute($value);
        }
    }
}
/**
 * 注册创始人数据
 * @param  [type] $db     [description]
 * @param  [type] $prefix [description]
 * @param  [type] $admin  [description]
 * @param  [type] $auth   [description]
 * @return [type]         [description]
 */
function register_administrator($db, $prefix, $admin, $auth)
{
    show_msg('开始注册创始人帐号...');
    $uid = 1;
    /*插入用户*/
    $sql = <<<sql
REPLACE INTO `[PREFIX]member` (`uid`, `shopid`, `username`, `email`, `mobile`, `realname`, `nickname`, `password`, `sex`, `avatar`, `birthday`, `qq`, `login`, `signature`, `score1`, `score2`, `score3`, `score4`, `status`, `reg_ip`, `last_login_time`, `last_login_ip`, `create_time`, `update_time`) VALUES
('[UID]', 0, '[NAME]', '[EMAIL]','','','[NAME]', '[PASS]',0, '', '[TIME]', '', '', '', 0, 0, 0, 0, 1, '[IP]', '[TIME]', '[IP]','[TIME]','[TIME]');
sql;

    /*  "REPLACE INTO `[PREFIX]ucenter_member` VALUES " .
         "('1', '[NAME]', '[PASS]', '[EMAIL]', '', '[TIME]', '[IP]', 0, 0, '[TIME]', '1',1,'finish')";*/

    $password = user_md5($admin['password'], $auth);
    $sql = str_replace(
        array('[PREFIX]', '[NAME]', '[PASS]', '[EMAIL]', '[TIME]', '[IP]', '[UID]'),
        array($prefix, $admin['username'], $password, $admin['email'], time(), request()->ip(1), $uid),
        $sql);
    //执行sql
    $db->execute($sql);

    show_msg('创始人帐号注册完成！');
}

/**
 * 及时显示提示信息
 * @param  string $msg 提示信息
 */
function show_msg($msg, $class = '')
{
    echo "<script type=\"text/javascript\">showmsg(\"{$msg}\", \"{$class}\")</script>";
    ob_flush();
    flush();
}

/**
 * 按钮上及时显示提示信息
 * @param  string $msg 提示信息
 */
function error_btn($msg, $class = '')
{
    echo "<script type=\"text/javascript\">error_btn(\"{$msg}\", \"{$class}\")</script>";
    ob_flush();
    flush();
}
