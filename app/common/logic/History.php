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

class History extends Base{
    public function formatData($data){
        //获取应用名
        $data['module_name'] =  $data['app'] == 'system' ? '系统' :Module::where('name',$data['app'])->value('alias');
        $data['user_info'] = query_user($data['uid'],['nickname','avatar']);//用户信息
        $products = $this->getProductsModel($data['app'] ,$data['info_type'])->where('id',$data['info_id'])->find()->toArray();
        $products = $this->getProductsLogic($data['app'] ,$data['info_type'])->formatData($products);
        $data['products'] = $products;
        $data = $this->setTimeAttr($data);
        return $data;
    }

    protected function getProductsModel($app ,$info_type){
        $namespace = "app\\{$app}\\model\\" . ucfirst($app) . ucfirst($info_type);
        $model = new $namespace;
        return $model;
    }

    protected function getProductsLogic($app ,$info_type){
        $namespace = "app\\{$app}\\logic\\" . ucfirst($info_type);
        $logic = new $namespace;
        return $logic;
    }
}