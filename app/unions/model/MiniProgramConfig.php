<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: MiniProgramConfig.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/10/14
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */

namespace app\unions\model;
use app\common\model\BaseModel;

class MiniProgramConfig extends BaseModel{
    public $group_list = ['wechat' => '微信小程序' , 'baidu' => '百度小程序', 'alipay' => '支付宝小程序', 'bytedance' => '字节跳动小程序'];

    /**
     * 处理builder数据
     * @param $params
     * @return array
     */
    public function formatParams($params){
        $data = [];
        foreach ($params as $k => $v){
            $k = explode('_',$k);
            $data[$k[0]][$k[1]] = $v;
        }
        return $data;
    }

    public function handleBuilder($list){
        $data = [];
        foreach ($list as $item){
            foreach ($item as $key => $value){
                $data[$item['platform'] . '_' . $key] = $value;
            }
        }
        return $data;
    }
}