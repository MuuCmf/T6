<?php
namespace app\common\exception;

use think\db\exception\PDOException;
use think\exception\Handle;
use ErrorException;
use Exception;
use InvalidArgumentException;
use ParseError;
use think\exception\ClassNotFoundException;
use think\exception\HttpException;
use think\exception\RouteNotFoundException;
use think\exception\ValidateException;
use think\Response;
use Throwable;
use TypeError;
class ExceptionHandle extends Handle{
    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        if (!$this->isIgnoreReport($e)) {
            // 参数验证错误
            if ($e instanceof ValidateException) {
                return $this->errorMsg($e->getError(),$e);
            }
            // ajax请求404异常 , 不返回错误页面
            if (($e instanceof ClassNotFoundException || $e instanceof RouteNotFoundException) && request()->isAjax()) {
                return $this->errorMsg('当前请求资源不存在，请稍后再试',$e);
            }
            // ajax请求500异常, 不返回错误页面
            if (($e instanceof Exception || $e instanceof PDOException || $e instanceof HttpException || $e instanceof InvalidArgumentException || $e instanceof ErrorException || $e instanceof ParseError || $e instanceof TypeError) && request()->isAjax()) {
                return $this->errorMsg('系统错误',$e);
            }
        }
        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
    protected function errorMsg($msg ,$e){
        return json(['code' => 0,'msg' => env('app_debug') ? $e->getMessage() : $msg, 'data' => env('app_debug') ? 'line:'. $e->getFile() . ' on ' . $e->getLine() . ' row' : []]);
    }
}