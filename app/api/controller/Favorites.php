<?php
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
class Favorites extends Api
{
    protected $FavoritesModel;
    protected $FavoritesLogic;
    //添加token验证中间件
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];
    function __construct(Request $request)
    {
        parent::__construct();
        $this->FavoritesModel = new FavoritesModel();
        $this->FavoritesLogic = new FavoritesLogic();
    }

    /**
     * 收藏列表
     */
    public function lists()
    {
        $uid = get_uid();
        $map = [
            ['shopid' ,'=' ,$this->shopid],
            ['uid' ,'=' ,$uid],
            ['status' ,'=' ,1]
        ];

        $rows = 15;
        $order_field = input('order_field', 'id', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order =  $order_field . ' ' . $order_type;
        $fields = '*';
        $lists = $this->FavoritesModel->getListByPage($map, $order, $fields, $rows);
        $lists = $lists->toArray();
        foreach($lists['data'] as &$val){
            $val = $this->FavoritesLogic->formatData($val);
        }
        unset($val);

        return $this->success('success',$lists);
    }

    /**
     * 收藏数量
     */
    public function count()
    {
        $uid = get_uid();
        $map = [
            ['shopid' ,'=' ,$this->shopid],
            ['uid' ,'=' ,$uid],
            ['status' ,'=' ,1]
        ];

        $count = $this->FavoritesModel->where($map)->count();

        return $this->success('success', $count);
    }
}