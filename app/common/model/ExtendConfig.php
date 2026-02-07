<?php

namespace app\common\model;

/**
 * 系统扩展配置模型 ，第三方功能整合的专用配置数据模型
 */
class ExtendConfig extends Base
{
    /**
     * 获取配置列表
     * @return array 配置数组
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function lists()
    {
        $map    = array('status' => 1);
        $list   = $this->where($map)->field('type,name,value')->select()->toArray();

        $config = array();
        if ($list && is_array($list)) {
            foreach ($list as $value) {
                $config[$value['name']] = $this->parse($value['type'], $value['value']);
            }
        }
        return $config;
    }

    /**
     * 根据配置名称获取配置extra值
     * @param  string $name  配置名称
     * @return array 配置extra值
     */
    public function getExtraByName($name)
    {
        $data = $this->where(['name' => $name])->find();
        return $this->parse($data['type'], $data['extra']);
    }

    /**
     * 根据配置类型解析配置
     * @param  integer $type  配置类型
     * @param  string  $value 配置值
     */
    private function parse($type, $value)
    {
        if($type == 'select' || $type == 'entity' || $type == 'checkbox' || $type == 'radio'){
            // 解析数组
            $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
            if (strpos($value, ':')) {
                $value  = array();
                foreach ($array as $val) {
                    list($k, $v) = explode(':', $val);
                    $value[$k]   = $v;
                }
            }
        }

        return $value;
    }
}
