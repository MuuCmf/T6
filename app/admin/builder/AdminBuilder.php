<?php
namespace app\admin\builder;

use think\App;
use think\facade\View;
use app\admin\Controller\Admin;

/**
 * AdminBuilder：快速建立管理页面。
 * Class AdminBuilder
 * @package Admin\Builder
 */
abstract class AdminBuilder extends Admin
{
    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        //获取模版的名称
        //$template = APP_PATH . 'admin' .DIRECTORY_SEPARATOR. 'view' .DIRECTORY_SEPARATOR. 'builder'. DIRECTORY_SEPARATOR . $templateFile . '.html';
        
        //显示页面
        $html = htmlspecialchars_decode(View::fetch('../../admin/view/builder' . DIRECTORY_SEPARATOR . $templateFile));
        echo $html;
    }

    protected function compileHtmlAttr($attr) {
        $result = array();
        
        if(is_array($attr)){
            foreach($attr as $key=>$value) {
                $value = htmlspecialchars($value);
                $result[] = "$key=\"$value\"";
            }
            $result = implode(' ', $result);
        }
        
        return $result;
    }
}

