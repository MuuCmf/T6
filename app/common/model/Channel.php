<?php
namespace app\common\model;

use think\Model;

/**
 * 导航模型
 */
class Channel extends Model
{
    /**
     * 获取导航列表，支持多级导航
     * @param  string $block 导航类型
     * @param  boolean $field 要列出的字段
     * @return array          导航树
     */
    public function lists($block = 'navbar', $field = true)
    {
        $map[] = ['block', '=', $block];
        $list = $this->field($field)->where($map)->order('sort')->select();
        if ($list) {
            $list = $list->toArray();

            foreach ($list as $key => $value) {
                if($value['type'] == 'app'){
                    $list[$key]['url'] = $value['app'];
                }
            }
        }
        
        return $list;
    }
}