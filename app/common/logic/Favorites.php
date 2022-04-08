<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: History.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/2/21
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\common\logic;
use app\common\model\Module;
use think\helper\Str;

class Favorites extends Base{
    public function formatData($data){
        //获取应用名
        $data['module_name'] =  $data['app'] == 'system' ? '系统' :Module::where('name',$data['app'])->value('alias');
        $data['user_info'] = query_user($data['uid'],['nickname','avatar']);//用户信息
        $data['products'] = json_decode($data['metadata'],true);
        $data['products'] = $this->setImgAttr($data['products']);
        if (isset($data['products']['price'])){
            $data['products']['price'] = sprintf("%.2f",$data['products']['price']/100);
        }
        $data = $this->setTimeAttr($data);
        return $data;
    }
}