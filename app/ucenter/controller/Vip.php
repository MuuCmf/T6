<?php
declare (strict_types = 1);

namespace app\ucenter\controller;

use think\facade\View;
use app\common\model\Vip as VipModel;
use app\common\logic\Vip as VipLogic;
use app\common\model\VipCard as VipCardModel;
use app\common\logic\VipCard as VipCardLogic;

class Vip extends Base
{
    protected $VipModel;
    protected $VipLogic;
    protected $VipCardModel;
    protected $VipCardLogic;

    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->VipModel = new VipModel();
        $this->VipLogic = new VipLogic();
        $this->VipCardModel = new VipCardModel();
        $this->VipCardLogic = new VipCardLogic();
        $user = query_user(get_uid());
        View::assign('user', $user);
        
    }

    /**
     * VIP卡项列表
     * @return [type] [description]
     */
    public function lists()
    {
        $uid = get_uid();
        $app = input('app', '', 'text');
        $rows = input('rows',20, 'intval');
        $order_field = input('order_field', 'id', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order = 'sort DESC,' . $order_field . ' ' . $order_type;
        $fields = '*';

        // 初始化查询条件
        $map = [];
        if(!empty($app)){
            $map[] = ['app', '=', $app];
        }
        
        $map[] = ['status', '=', 1];

        $lists = $this->VipCardModel->getListByPage($map,$order,$fields, $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();
        
        foreach($lists['data'] as &$val){
            $val = $this->VipCardLogic->formatData($val);
            //查询用户是否已拥有会员类型数据
			$have_card = $this->VipModel->getVipByCardId($this->shopid, $uid, $val['id']);
			if(!empty($have_card)){
                $have_card = $this->VipLogic->formatData($have_card);
				$val['have_card'] = $have_card;
			}
        }
        unset($val);
        
        View::assign('pager', $pager);
        View::assign('lists', $lists);

        // 设置页面TITLE
        $this->setTitle('VIP卡项');
        View::assign('tab', 'vip');
        // 输出模板
        return View::fetch();
    }

    /**
     * 卡项详情
     */
    public function detail()
    {

    }



}