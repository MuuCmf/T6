<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Vip as VipModel;
use app\common\logic\Vip as VipLogic;
use app\common\model\VipCard as VipCardModel;
use app\common\logic\VipCard as VipCardLogic;

class VipCard extends Api
{
    protected $VipModel;
    protected $VipLogic;
    protected $VipCardModel;
    protected $VipCardLogic;
    function __construct()
    {
        parent::__construct();
        $this->VipModel = new VipModel();
        $this->VipLogic = new VipLogic();
        $this->VipCardModel = new VipCardModel();
        $this->VipCardLogic = new VipCardLogic();
    }

    /**
     * 卡项管理
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
        
        return $this->success('SUCCESS', $lists);
    }

    /**
     * 卡项详情
     */
    public function detail()
    {
        $id = input('id',0,'intval');
        $uid = get_uid();
        if(!empty($id)){
            $data = $this->VipCardModel->getDataById($id);
            $data = $this->VipCardLogic->formatData($data);

            if(!empty($data)){
                //查询用户是否已拥有会员类型数据
                $have_card = $this->VipModel->getVipByCardId($this->shopid, $uid, $id);
                if(!empty($have_card)){
                    $have_card = $this->VipLogic->formatData($have_card);
                    $data['have_card'] = $have_card;
                }
                // ajax请求返回数据
                return $this->success('success', $data);
            }else{
                return $this->error('error');
            }
        }else{
            return $this->error('缺少参数');
        }
    }

    /**
     * 根据商品ID查询可使用的会员卡(返回支持该商品所有会员卡数组)
     * @param  [type] $id      [description] 课程ID
     * @param  [type] $type    [description] 课程类型
     * @param  [type] $uniacid [description] 平台ID
     * @return [type] array    [description] 会员卡列表
     */
    public function productAble()
    {
        $app = input('app', '', 'text');
        $product_id = input('product_id', 0, 'intval');
        $product_type = input('product_type', '', 'text');
        
        $lists = $this->VipCardModel->getProductAbleCardsList($this->shopid, $app, $product_id, $product_type);

        return $this->success('success', $lists);
    }

    /**
     * 获取用户可使用最优惠的会员卡
     */
    public function userAble()
    {
        $app = input('app', '', 'text');
        $product_id = input('product_id', 0, 'intval');
        $product_type = input('product_type', '', 'text');
        $uid = get_uid();
        
        $result = $this->VipCardModel->getUserAbleCard($this->shopid, $app, $uid, $product_id, $product_type);
        if($result){
            return $this->success('success', $result);
        }else{
            return $this->error('无可以VIP卡数据');
        }
        
    }
}