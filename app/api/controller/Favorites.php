<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Favorites.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/11/10
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\api\controller;
use app\common\controller\Api;
use app\common\model\Favorites as FavoritesModel;
use app\common\logic\Favorites as FavoritesLogic;
use think\Request;

/**
 * 收藏接口
 * Class Favorites
 * @package app\minishop\controller
 */
class Favorites extends Api{
    protected $model;
    protected $logic;
    //添加token验证中间件
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];
    function __construct(Request $request)
    {
        parent::__construct();
        $this->model = new FavoritesModel();
        $this->logic = new FavoritesLogic();
    }

    public function lists(){
        $uid = request()->uid;
        $map = [
            ['shopid' ,'=' ,$this->shopid],
            ['uid' ,'=' ,$uid],
            ['status' ,'=' ,1]
        ];
        $rows = $params['rows'] ?? 15;
        $list = $this->model->where($map)->page($this->params['page'],$rows)->order('id','DESC')->select()->toArray();
        foreach ($list as &$item){
            $item = $this->logic->formatData($item);
        }
        $this->success('success',$list);
    }
}