<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: UniAccount.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/9/27
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\unions\model;

use app\common\model\BaseModel;
use think\Model;

class UniAccount extends BaseModel {
    public function getGroupStrAttr($value)
    {
        $type = ['offcial_account' => '微信公众号','wechat_mini_program' => '微信小程序'];
        return $type[$value];
    }

    /**
     * 根据指定条件查询
     * @param $where
     */
    public function findDataByWhere($where)
    {
        $where['status'] = 1;
        $data = $this->where($where)->field("id,name,type,title,group,extra,remark,value")->order('sort','ASC')->select()->toArray();
        if (!empty($data)){
            $handle_data = [];
            foreach ($data as &$item){
                $handle_data[$item['name']] = $item['value'];
            }
            $data = $handle_data;
        }
        return $data;
    }
    public function getbuilder($where = []){
        $where['status'] = 1;
        $list = $this->where($where)->field('type,name,value')->select()->toArray();
        $config = [];
        if($list && is_array($list)){
            foreach ($list as $value) {
                $config[$value['name']] = $this->parse($value['type'], $value['value']);
            }
        }
        return $config;
    }
    /**
     * 根据配置类型解析配置
     * @param  integer $type  配置类型
     * @param  string  $value 配置值
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    private function parse($type, $value){
        switch ($type) {
            case 3: //解析数组
                $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
                if(strpos($value,':')){
                    $value  = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value[$k]   = $v;
                    }
                }else{
                    $value =    $array;
                }
                break;
        }
        return $value;
    }
}