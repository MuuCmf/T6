<?php
/**
 * 应用模块数据处理
 */
namespace app\common\logic;

class Module
{
    /**
     * 获取模块图标
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function getIcon($name, $icon)
    {
        if(empty($icon)){
            //图标所在位置为模块静态目录下（推荐）
            if(file_exists(PUBLIC_PATH . '/static/' . $name . '/images/icon.jpg')){
                $icon = '/static/'. $name .'/images/icon.jpg';
            }else{
                $icon = '/static/admin/images/module_default_icon.png';
            }
        }else{
            $icon = get_attachment_src($icon);
        }
        
        return $icon;
    }


}