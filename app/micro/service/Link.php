<?php
// +----------------------------------------------------------------------
// | 微页应用 MuuCmf_micro V1.0.0
// | 多应用链接至页面数据处理
// +----------------------------------------------------------------------
namespace app\micro\service;

use think\facade\View;
use think\helper\Str;
use app\common\model\Module;

class Link
{
    /**
     * 获取所有应用支持的连接至配置参数
     */
    public function getAllLinks()
    {
        $data = [];
        $module_list = (new Module)->getAll([['is_setup', '=', 1]]);
        foreach($module_list as $v){
            
            $file = APP_PATH . $v['name'] . '/service/' . 'Link.php';
            //打开
            if(file_exists($file)){
                $data[$v['name']]['name'] = $v['name'];
                $data[$v['name']]['alias'] = $v['alias'];
                // 去除文件名后缀
                $name = $v['name'] . '\\' . 'Link';
                $class = 'app\\' . $v['name'] . '\\service\\Link';
                // 绑定到容器
                bind($name, $class);
                // 获取组件唯一标识
                $links = app($name)->links();
                $data[$v['name']]['links'] = $links;
            }
        }

        return $data;
    }
    /**
     * 连接至参数配置
     */
    public function links()
    {
        return [
            'micro_page' => [
                'icon' => 'desktop',
                'sys_type' => 'detail',
                'link_type' => 'micro_page',
                'link_type_title' => '自定义页面',
                'api' => url('micro/admin.page/api')
            ],
            'category' => [
                'icon' => 'indent',
                'sys_type' => 'direct',
                'link_type' => 'category',
                'link_type_title' => '分类页',
                'api' => url('exam/admin.category/lists')
            ],
            'member' => [
                'icon' => 'user',
                'sys_type' => 'direct',
                'link_type' => 'member',
                'link_type_title' => '会员服务',
                'api' => url('exam/admin.vip/lists')
            ],
            'out_url' => [
                'icon' => 'link',
                'sys_type' => 'direct',
                'link_type' => 'out_url',
                'link_type_title' => '自定义链接',
            ]
        ];
    }

    public function linkToUrl($linkParam = [], $channel = 'mobile'){
        //初始化返回值
        $result = '';

        return $result;
    }

    /**
     * 获取链接至组件静态资源模板内容
     */
    public function getStaticTmpl()
    {
        $links_params_config = $this->getAllLinks();
        $style = '';
        $script = '';
        foreach($links_params_config as $k=>$v){
            if(!empty($v['links'])){
                foreach($v['links'] as $c_k=>$c_v){
                    
                    $style .= file_get_contents($c_v['static']['link']['css']);
                    
                    if(!empty($c_v['static']['link']['js'])){
                        $script .= file_get_contents($c_v['static']['link']['js']);
                    }
                }
            }
        }
        $style='<style>' .$style. '</style>';
        $script = '<script>' .$script. '</script>';
        $link_static_tmpl = $style . $script;
        //dump($style);
        return $link_static_tmpl;
    }

}