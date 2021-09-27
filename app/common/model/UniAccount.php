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
namespace app\common\model;

use think\Model;

class UniAccount extends Model{
    public function getStatusStrAttr($value)
    {
        $status = [-1=>'删除',0=>'禁用',1=>'正常',2=>'待审核'];
        return $status[$value];
    }
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
        //处理相同平台数据
        $handle_data = [];
//        foreach ($data as &$item){
//            $platform = $item['platform'];//平台名称
//            $group = $item['group'];//相同应用
//            $handle_data[$platform]['title'] = $this->getPlatformStrAttr($platform);
//            $handle_data[$platform]['data'][$group]['title'] = $this->getGroupStrAttr($group);
//            $handle_data[$platform]['data'][$group]['data'][] = $item;
//        }
        return $handle_data;
    }
    public function getbuilder($where){
        $where['status'] = 1;
        $data = $this->where($where)->field("id,name,type,title,group,extra,remark,value")->order('sort','ASC')->select()->toArray();
        //处理相同平台数据
//        $handle_data = [];
//        foreach ($data as &$item){
//            $group = $item['group'];//相同应用
//            $handle_data[$platform]['data'][$group]['title'] = $this->getGroupStrAttr($group);
//            $handle_data[$platform]['data'][$group]['data'][] = $item;
//        }
        return $data;
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