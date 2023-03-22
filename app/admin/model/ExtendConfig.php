<?php
namespace app\admin\model;

use think\Model;

/**
 * 系统扩展配置模型 ，第三方功能整合的专用配置数据模型
 */
class ExtendConfig extends Model {

    /**
     * 新增或编辑数据
     *
     * @param      <type>  $data   The data
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function edit($data)
    {
        if(empty($data['id'])){
            $res = $this->insertGetId($data);
        }else{
            $res = $this->update($data);
        }

        if(!empty($this->id)){
            return $this->id;
        }else{
            if (is_object($res)) return  $res->id;
            return $res;
        }
        
    }

    /**
     * 根据ID获取配置数据
     *
     * @param      integer  $id     The identifier
     *
     * @return     <type>   The data by identifier.
     */
    public function getDataById($id)
    {
        if($id>0){
            $data = $this->find($id);
            if(!empty($data)){
                return $data;
            }
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
        $list   = $this->where($map)->field('type,name,value')->select()->toArray();
        
        $config = array();
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
