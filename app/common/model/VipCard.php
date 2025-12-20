<?php

namespace app\common\model;

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

        $list = $this->getList($map, 10, 'create_time desc', '*');
        $list = $list->toArray();

        return $list;
    }

    /**
     * 获取商品可用会员卡列表
     */
    public function getProductAbleCardsList(int $shopid, string $app, int $product_id, string $product_type)
    {
        // 获取应用所有启用中会员卡
        $map[] = ['shopid', '=', $shopid];
        $map[] = ['status', '=', 1];
        $map[] = ['app', 'like', '%' . $app . '%'];

        $card_list = $this->getList($map, 10, 'create_time desc', '*');
        $card_list = $card_list->toArray();
        $cardLogic = new VipCardLogic();
        foreach ($card_list as &$item) {
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
        foreach ($card_list as $key => &$val) {
            
            if(is_json($val['category_ids'])){
                // 多应用数据处理
                if (!$this->getVipCardSupportAppCategoryId($val, $app, $product_data['category_id'])) {
                    unset($card_list[$key]);
                } else {
                    //根据折扣计算该会员价格
                    $val['member_price'] = intval($product_data['price'] * ($val['discount'] / 10));
                    $val['member_price'] = sprintf("%.2f", $val['member_price'] / 100);
                }
            }else{
                // 单应用数据处理
                if (!in_array($product_data['category_id'], $val['category_ids_arr'])) {
                    unset($card_list[$key]);
                } else {
                    //根据折扣计算该会员价格
                    $val['member_price'] = intval($product_data['price'] * ($val['discount'] / 10));
                    $val['member_price'] = sprintf("%.2f", $val['member_price'] / 100);
                }
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
        $where = "v.`shopid`={$shopid} and vc.`app` like '%{$app}%' and v.`uid`={$uid} and (v.`end_time` > " . time() . " or v.`end_time`=0) and v.`status`=1 and vc.`status`=1";
        $vip_card_list = $vipModel->alias('v')
        ->join('vip_card vc', 'vc.id = v.card_id')
        ->whereRaw($where)
        ->field('v.uid vip_uid,v.card_id vip_card_id,v.end_time vip_end_time,v.status vip_status, vc.*')
        ->select();

        if (empty($vip_card_list)) {
            return null;
        }else{
            $vip_card_list = $vip_card_list->toArray();
        }

        $cardLogic = new VipCardLogic();
        foreach ($vip_card_list as &$v) {
            unset($v['vip_uid']);
            unset($v['vip_card_id']);
            unset($v['vip_end_time']);
            unset($v['vip_status']);
            $v = $cardLogic->formatData($v);
        }
        unset($v);

        //获取商品数据
        $file_name = ucfirst($app) . ucfirst($product_type);
        $namespace = "app\\{$app}\\model\\{$file_name}";
        $productModel = new $namespace;
        $product_data = $productModel->where('id', $product_id)->find();
        $product_data = $product_data->toArray();
        
        if (!empty($product_data)) {
            //循环查询会员卡是否支持该产品，删除不支持的会员卡元素
            foreach ($vip_card_list as $key => &$val) {

                if(is_json($val['category_ids'])){
                    // 多应用数据处理
                    if (!$this->getVipCardSupportAppCategoryId($val, $app, $product_data['category_id'])) {
                        unset($vip_card_list[$key]);
                    } else {
                        //根据折扣计算该会员价格
                        $member_price = intval($product_data['price'] * ($val['discount'] / 10));
                        $val['member_price'] = sprintf("%.2f", $member_price / 100);
                    }
                }else{
                    // 单应用数据处理
                    if (!in_array($product_data['category_id'], $val['category_ids_arr'])) {
                        unset($card_list[$key]);
                    } else {
                        //根据折扣计算该会员价格
                        $member_price = intval($product_data['price'] * ($val['discount'] / 10));
                        $val['member_price'] = sprintf("%.2f", $member_price / 100);
                    }
                }
            }
            unset($val);
            
            //查询优惠力度最大的会员卡
            $resule = [];
            if (!empty($vip_card_list) && count($vip_card_list) >= 1) {
                $discount_arr = [];
                foreach ($vip_card_list as $key => $val) {
                    $discount_arr[$key] = $val['discount'];
                }
                asort($discount_arr);
                $key = key($discount_arr);
                $resule = $vip_card_list[$key];
            }
            
            return $resule;
        }
        return null;
    }

    /**
     * 查询多应用卡项是否支持某应用的分类
     * 
     * @param array $card VIP卡信息数组，包含multi_app_category_ids字段
     * @param string $app 应用名称
     * @param mixed $category_ids 要检查的分类ID或ID数组
     * @return bool 如果支持返回true，否则返回false
     */
    private function getVipCardSupportAppCategoryId($card, $app, $category_ids)
    {
        if(!empty($card['category_ids']) && empty($card['multi_app_category_ids'])){
            $card['multi_app_category_ids'] = json_decode($card['category_ids'], true);
        }

        foreach($card['multi_app_category_ids'] as $val){
            if($val['app_name'] == $app){
                if(in_array($category_ids, $val['category_ids'])){
                    return true;
                }else{
                    // 跳过本次循环
                    continue;
                }
            }
        }
        return false;
    }
}
