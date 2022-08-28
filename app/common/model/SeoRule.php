<?php
namespace app\common\model;

class SeoRule extends Base
{
    /**
     * 获取seo规则
     */
    public function getRule($app, $controller, $action)
    {
        $where = "(`app`='".$app."' or `app`='') and (`controller`='".$controller."' or `controller`='') and (`action`='".$action."' or `action`='') and `status`=1";
        $rule = (new SeoRule())->whereRaw($where)->find();
        if($rule){
            $rule = $rule->toArray();
        }else{
            $rule = NULL;
        }

        return $rule;
    }

}