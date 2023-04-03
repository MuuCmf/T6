<?php
namespace app\common\model;

use app\common\model\Vip as VipModel;
use app\common\logic\Vip as VipLogic;
use app\common\logic\VipCard as VipCardLogic;

/**
 * 付费会员卡项模型
 */
class VipCard extends Base
{
    protected $autoWriteTimestamp = true;

    /**
     * 获取各应用卡片列表
     */
    public function getCardData(int $shopid, string $app)
    {
        $map[] = ['shopid', '=', $shopid];
        $map[] = ['app', '=', $app];
        $map[] = ['status', '=', 1];

        $list = $this->getList($map, 10, 'create_time desc' ,'*');
        $list = $list->toArray();

        return $list;
    }

    /**
     * 获取商品可以会员卡列表
     */
    public function getProductAbleCardsList(int $shopid, string $app, int $product_id, string $product_type)
    {
        // 获取应用所有启用中会员卡
        $map[] = ['shopid', '=', $shopid];
        $map[] = ['app', '=', $app];
        $map[] = ['status', '=', 1];

        $card_list = $this->getList($map, 10, 'create_time desc' ,'*');
        $card_list = $card_list->toArray();
        $cardLogic = new VipCardLogic();
        foreach ($card_list as &$item){
            $item = $cardLogic->formatData($item);
        }
        unset($item);
        
        //获取商品数据
        $file_name = ucfirst($app) . ucfirst($product_type);
        $namespace = "app\\{$app}\\model\\{$file_name}";
        $productModel = new $namespace;
        $product_data = $productModel->where('id', $product_id)->find();
        $product_data = $product_data->toArray();

        //循环查询会员卡是否支持该产品，删除不支持的会员卡元素
        foreach($card_list as $key => &$val){
            if(!in_array($product_data['category_id'], $val['category_ids_arr'])){
                unset($card_list[$key]);
            }else{
                //根据折扣计算该会员价格
                $val['member_price'] = intval($product_data['price'] * ($val['discount']/10));
                $val['member_price'] = sprintf("%.2f",$val['member_price']/100);
            }
        }
        unset($val);
        //使用array_values函数，让数组只返回值，不返回键名
        $card_list = array_values($card_list);
        
        return $card_list;
    }

    /**
     * 获取用户可用并最优惠的VIP卡
     */
    public function getUserAbleCard(int $shopid, string $app, int $uid, int $product_id, string $product_type)
    {
        //获取用户未到期的所有会员卡
        $vipModel = new Vip();

        $where = "`shopid`={$shopid} and `app`='{$app}' and `uid`={$uid} and (`end_time` > ".time()." or `end_time`=0) and `status`=1";
        $vip_card_list = $vipModel->whereRaw($where)->select()->toArray();
        if(!empty($vip_card_list)){
            $logic = new VipLogic();
            foreach($vip_card_list as &$val){
                $val = $logic->formatData($val);
            }
            unset($val);
        }else{
            return null;
        }
        
        //获取商品数据
        $file_name = ucfirst($app) . ucfirst($product_type);
        $namespace = "app\\{$app}\\model\\{$file_name}";
        $productModel = new $namespace;
        $product_data = $productModel->where('id', $product_id)->find();
        $product_data = $product_data->toArray();
        
        if(!empty($product_data)){
            //循环查询会员卡是否支持该产品，删除不支持的会员卡元素
            foreach($vip_card_list as $key => &$val){
                if(empty($val['vip_card_info'])){
                    unset($vip_card_list[$key]);
                    continue;
                }
                
                if(!in_array($product_data['category_id'], $val['vip_card_info']['category_ids_arr'])){
                    unset($vip_card_list[$key]);
                }else{
                    //根据折扣计算该产品会员价格
                    $member_price = intval($product_data['price'] * ($val['vip_card_info']['discount']/10));
                    $member_price = sprintf("%.2f",$member_price/100);
                    $val['member_price'] = $member_price;
                }
            }
            unset($val);

            //查询优惠力度最大的会员卡
            $resule = [];
            if(!empty($vip_card_list) && count($vip_card_list)>=1){
                $discount_arr = [];
                foreach($vip_card_list as $key => $val){
                    if(!empty($val['vip_card_info'])){
                        $discount_arr[$key] = $val['vip_card_info']['discount']; 
                    }
                }
                asort($discount_arr);
                $key = key($discount_arr);
                $resule = $vip_card_list[$key];
            }

            return $resule;
        }
        return null;
        
    }

}