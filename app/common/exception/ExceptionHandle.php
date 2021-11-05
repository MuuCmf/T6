<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: ExceptionHandle.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/11/3
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */

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
                return json($e->getError(), 0);
            }
            // ajax请求404异常 , 不返回错误页面
            if (($e instanceof ClassNotFoundException || $e instanceof RouteNotFoundException) && request()->isAjax()) {
                return json(['code' => 0,'msg' => env('app_debug') ? $e->getMessage() : '当前请求资源不存在，请稍后再试', 'data' => []]);
            }
            // ajax请求500异常, 不返回错误页面
            if (($e instanceof Exception || $e instanceof PDOException || $e instanceof HttpException || $e instanceof InvalidArgumentException || $e instanceof ErrorException || $e instanceof ParseError || $e instanceof TypeError) && request()->isAjax()) {
                return json(['code' => 0,'msg' => env('app_debug') ? $e->getMessage() : '系统异常，请稍后再试', 'data' => env('app_debug') ? 'line:'. $e->getFile() . ' on ' . $e->getLine() . ' row' : []]);
            }
        }
        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}