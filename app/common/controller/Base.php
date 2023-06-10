<?php
namespace app\common\controller;

use think\exception\HttpResponseException;
use think\facade\Env;
use think\Response;
use think\Validate;
use app\common\model\Member;

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
    public $params;
    public $shopid = 0;
    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        $this->params = request()->param();
        $this->shopid = $this->params['shopid'] ?? 0;
        //记住登录
        $this->initRemberLogin();
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        //是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        $result =  $v->failException(false)->check($data);
        if (true !== $result) {
            return $v->getError();
        } else {
            return $result;
        }
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
    protected function success($msg = '',  $data = '', $url = '', int $wait = 3, array $header = []): Response
    {
        if (empty($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = 'refresh';
        } elseif ($url) {
            if(is_object($url)){
                $url = $url->build();
            }else{
                $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : 'refresh';
            }
        }
        
        $result = [
            'code' => 200,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'time' => time(),
            'wait' => $wait,
        ];

        if (Env::get('APP_DEBUG') && $this->debug) {
            $result['debug'] = $this->debug;
        }

        $type = (request()->isJson() || request()->isAjax()) ? 'json' : 'html';
        if ($type == 'html') {
            $response = view(config('app.dispatch_success_tmpl'), $result);
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
    protected function error($msg = '', $data = '', $url = '', int $wait = 3, array $header = []): Response
    {
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'time' => time(),
            'wait' => $wait,
        ];

        if (Env::get('APP_DEBUG') && $this->debug) {
            $result['debug'] = $this->debug;
        }

        $type = (request()->isJson() || request()->isAjax()) ? 'json' : 'html';
        if ($type == 'html') {
            $response = view(config('app.dispatch_error_tmpl'), $result);
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
   protected function result(int $code = 0, string $msg = '', $data = '', $url = '',int $wait = 3, array $header = []): Response
   {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => $wait,
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

   protected function initRemberLogin()
    {
        if(empty(get_uid())){
            (new Member())->rembemberLogin($this->shopid);
        }
    }

}