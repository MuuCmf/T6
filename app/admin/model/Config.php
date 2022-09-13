<?php
namespace app\admin\model;

use think\Model;

/**
 * 系统配置模型
 */
class Config extends Model {

    /**
     * 新增或编辑数据
     *
     * @param      <type>  $data   The data
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function edit($data)
    {
        if(!empty($data['id'])){
            $res = $this->update($data);
        }else{
            $res = $this->insert($data);
        }

        if($res){
            return $res;
        }else{
            return false;
        }
        
    }

    /**
     * 根据ID获取配置数据
     *
     * @param      integer  $id     The identifier
     *
     * @return     <type>   The data by identifier.
     */
    public function getDataById(int $id)
    {
        if($id > 0){
            $data = $this->find($id)->toArray();
            return $data;
        }
        return null;
    }

    /**
     * 获取配置列表
     * @return array 配置数组
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function lists(){
        $map    = array('status' => 1);
        $data   = $this->where($map)->field('type,name,value')->select();
        
        $config = array();
        if($data && is_array($data)){
            foreach ($data as $value) {
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
