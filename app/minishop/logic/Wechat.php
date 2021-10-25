<?php
namespace app\Knowledge\logic;

use think\Model;
use think\Db;

class Wechat extends Model
{

	/**
	 * 获取微信配置
	 *
	 * @return     array  The configuration.
	 */
	public function getConfig()
	{
		$config = [];
        $tmp = Db::name('Config')->where(['name' => ['like', '_WEIXIN_' . '%']])->limit(999)->select();

        foreach ($tmp as $k => $v) {
            $key = str_replace('_WEIXIN_', '', strtoupper($v['name']));
            $config[$key] = $v['value'];
        }

        //$config['CERT_PATH'] = get_file_by_id($config['CERT_PATH']);
        //$config['KEY_PATH'] = get_file_by_id($config['KEY_PATH']);

        // 一些配置
        $options = [
        	/**
		     * Debug 模式，bool 值：true/false
		     *
		     * 当值为 false 时，所有的日志都不会记录
		     */
            'debug'  => true,
            /**
		     * 账号基本信息，请从微信公众平台/开放平台获取
		     */
            'app_id' => $config['APP_ID'],
            'secret' => $config['APP_SECRET'],
            'token' => $config['TOKEN'],
            'aes_key' => $config['AES_ENCODING_KEY'],
            // EncodingAESKey，兼容与安全模式下请一定要填写！！！

            /**
             * 日志配置
             *
             * level: 日志级别, 可选为：
             *         debug/info/notice/warning/error/critical/alert/emergency
             * path：日志文件位置(绝对路径!!!)，要求可写权限
             */
            /*
            'log' => [
                'level' => 'debug',
                'file'  => '/tmp/easywechat.log', // XXX: 绝对路径！！！！
            ],
            */
            /**
             * OAuth 配置
             *
             * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
             * callback：OAuth授权完成后的回调页地址
             */
            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' => url('oauth_callback'),
            ],

            /**
             * Guzzle 全局设置
             *
             * 更多请参考： http://docs.guzzlephp.org/en/latest/request-options.html
             */
            'guzzle' => [
                'timeout' => 3.0, // 超时时间（秒）
                //'verify' => false, // 关掉 SSL 认证（强烈不建议！！！）
            ],
        ];

        return $options;;
	}

    /**
     * 获取openid
     * 可能会出现两种情况，
     * 1.已在系统内授权过的用户，在库中读取
     * 2.未授权过的用户，调用网页授权获取
     */
    public function getOpenid($uid = 0)
    {   
        if($uid == 0){
            $uid = get_uid();
        }
        
        $sync_user = Db::name('sync_login')->where(['uid'=>$uid,'type'=>'weixin'])->find();
        
        if($sync_user){
            $openid = $sync_user['type_uid'];

            return $openid;

        }

        return false;
    }
}