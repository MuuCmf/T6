<?php
namespace app\common\logic;

use app\common\model\Module;
use think\helper\Str;

class Favorites extends Base
{
    public function formatData($data){
        //获取应用名
        $data['module_name'] =  $data['app'] == 'system' ? '系统' :Module::where('name',$data['app'])->value('alias');
        $data['user_info'] = query_user($data['uid'],['nickname','avatar']);//用户信息
        $data['products'] = json_decode($data['metadata'],true);
        $data['products'] = $this->setImgAttr($data['products'], '1:1');
        if (isset($data['products']['price'])){
            $data['products']['price'] = sprintf("%.2f",$data['products']['price']/100);
        }
        $data = $this->setTimeAttr($data);
        return $data;
    }
}