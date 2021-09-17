<?php
declare (strict_types = 1);

namespace app\install\controller;

use think\exception\HttpResponseException;
use think\facade\Env;
use think\Response;

/**
 * 控制器基础类
 */
class Base
{
    private $debug = [];
    
    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 系统设置
     * @var array
     */
    protected $system = [];

    protected $uid = 0;

    protected $token = '';
    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
        
    }

    /** 
    * 操作成功跳转的快捷方法
    * @access protected
    * @param  mixed $msg 提示信息
    * @param  string $url 跳转的URL地址
    * @param  mixed $data 返回的数据
    * @param  integer $wait 跳转等待时间
    * @param  array $header 发送的Header信息
    * @return void
    */
    // protected function success($msg = '', $data = '', string $url = '', array $header = [])
    // {
    //     if (empty($url)) {
    //         $url = 'refresh';
    //     }

    //    return $this->result(200, $msg, $data, $url);
    // }
    protected function success($msg = '',  $data = '', string $url = null, int $wait = 3, array $header = []): Response
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app('route')->buildUrl($url);
        }

        $result = [
            'code' => 200,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = (request()->isJson() || request()->isAjax()) ? 'json' : 'html';
        if ($type == 'html') {
            $response = view(app('config')->get('app.dispatch_success_tmpl'), $result);
        } else if ($type == 'json') {
            $response = json($result);
        }
        throw new HttpResponseException($response);
    }

    /**
    * 操作错误跳转的快捷方法
    * @access protected
    * @param  mixed $msg 提示信息
    * @param  string $url 跳转的URL地址
    * @param  mixed $data 返回的数据
    * @param  integer $wait 跳转等待时间
    * @param  array $header 发送的Header信息
    * @return void
    */
    // protected function error($msg = '', $data = '', string $url = '', array $header = [])
    // {
    //     return $this->result(0, $msg, $data, $url);
    // }
    protected function error($msg = '', $data = '', string $url = null, int $wait = 3, array $header = []): Response
    {
        if (is_null($url)) {
            $url = request()->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app('route')->buildUrl($url);
        }

        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = (request()->isJson() || request()->isAjax()) ? 'json' : 'html';
       
        if ($type == 'html') {
            $response = view(app('config')->get('app.dispatch_error_tmpl'), $result);
        } else if ($type == 'json') {
            $response = json($result);
        }
        throw new HttpResponseException($response);
    }


   /**
    * 返回封装后的API数据到客户端
    * @access protected
    * @param  integer $code 返回的code
    * @param  mixed $data 要返回的数据
    * @param  mixed $msg 提示信息
    * @param  string $type 返回数据格式
    * @param  array $header 发送的Header信息
    * @return void
    */
   protected function result(int $code = 0, string $msg = '', $data = '', $url = '',$type = 'json', array $header = []): Response
   {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'time' => time(),
        ];

        if (Env::get('APP_DEBUG') && $this->debug) {
            $result['debug'] = $this->debug;
        }

        return json($result);
   }

   /**
    * URL重定向
    * @access protected
    * @param  string $url 跳转的URL表达式
    * @param  integer $code http code
    * @param  array $with 隐式传参
    * @return void
    */
   protected function redirect($url, $code = 302, $with = [])
   {
       $response = Response::create($url, 'redirect');

       $response->code($code)->with($with);

       throw new HttpResponseException($response);
   }

   /**
    * 获取当前的response 输出类型
    * @access protected
    * @return string
    */
   protected function getResponseType()
   {
       return request()->isJson() || request()->isAjax() ? 'json' : 'html';
   }
}