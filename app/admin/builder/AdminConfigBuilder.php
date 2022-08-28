<?php

namespace app\admin\builder;

use think\App;
use think\facade\Db;
use think\facade\View;

class AdminConfigBuilder extends AdminBuilder
{
    private $_title;
    private $_suggest;
    private $_keyList     = [];
    private $_data        = [];
    private $_buttonList  = [];
    private $_savePostUrl = [];
    private $_group       = [];
    private $_callback    = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function title($title)
    {
        $title = empty($title)? '': $title;
        $this->_title = $title;

        return $this;
    }

    /**
     * suggest  页面标题边上的提示信息
     * @param $suggest
     * @return $this
     */
    public function suggest($suggest)
    {
        $this->_suggest = $suggest;
        return $this;
    }

    public function callback($callback)
    {
        $this->_callback = $callback;
        return $this;
    }

    /**键，一般用于内部调用
     * @param      $name
     * @param      $title
     * @param null $subtitle
     * @param      $type
     * @param null $opt
     * @return $this
     */
    public function key($name, $title, $subtitle = null, $type = '', $opt = null)
    {
        $key = ['name' => $name, 'title' => $title, 'subtitle' => $subtitle, 'type' => $type, 'opt' => $opt];
        $this->_keyList[] = $key;
        return $this;
    }

    /**只读文本
     * @param      $name
     * @param      $title
     * @param null $subtitle
     * @return AdminConfigBuilder
     */
    public function keyHidden($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'hidden');
    }

    /**只读文本
     * @param      $name
     * @param      $title
     * @param null $subtitle
     * @return AdminConfigBuilder
     */
    public function keyReadOnly($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'readonly');
    }

    /**只读文本框
    *
    */
    public function keyReadOnlyText($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'readonlytext');
    }

    /**
     * 只读纯HTML
     * @param  [type] $name     [description]
     * @param  [type] $title    [description]
     * @param  [type] $subtitle [description]
     * @return [type]           [description]
     * @auth 大蒙<59262424@qq.com>
     */
    public function keyReadOnlyHtml($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'readonlyhtml');
    }

    /**
     * 文本输入框
     * @param      $name
     * @param      $title
     * @param null $subtitle
     * @return AdminConfigBuilder
     */
    public function keyText($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'text');
    }

    /**
     * 颜色选择器
     * @param $name
     * @param $title
     * @param null $subtitle
     * @param $opt 字符串，如：#fff,#000,#ff6600,#999999
     * @return $this
     */
    public function keyColor($name, $title, $subtitle = null ,$opt = null)
    {
        return $this->key($name, $title, $subtitle, 'colorPicker', $opt);
    }

    /**
     * 字体图标选择器
     */
    public function keyIcon($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'icon');
    }

    public function keyLabel($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'label');
    }

    public function keyTextArea($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'textarea');
    }

    /**
     * 整数输入框
     */
    public function keyInteger($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'integer');
    }

    public function keyId($name = 'id', $title = '编号', $subtitle = null)
    {
        return $this->keyReadOnly($name, $title, $subtitle);
    }

    public function keyUid($name = 'uid', $title = 'UID', $subtitle = null)
    {
        return $this->keyReadOnly($name, $title, $subtitle);
    }

    public function keyStatus($name = 'status', $title = '状态', $subtitle = null)
    {
        $map = array(-1 => '删除', 0 => '禁用', 1 => '启用');
        return $this->keySelect($name, $title, $subtitle, $map);
    }

    public function keySelect($name, $title, $subtitle = null, $options = null)
    {
        return $this->key($name, $title, $subtitle, 'select', $options);
    }

    public function keyRadio($name, $title, $subtitle = null, $options  = null)
    {
        return $this->key($name, $title, $subtitle, 'radio', $options);
    }

    public function keyCheckBox($name, $title, $subtitle = null, $options  = null)
    {
        return $this->key($name, $title, $subtitle, 'checkbox', $options);
    }
    
    /**
     * 调用不同的富文本编辑器
     * @param  [type] $name     字段
     * @param  [type] $title    标题
     * @param  [type] $subtitle 标题描述
     * @param  string $type     目前支持ueditor\wangeditou，默认editor
     * @param  string $config   配置项，需参考相应编辑器文档设置
     * @param  string $style    样式 如：height:200px;width:200px
     * @param  string $param    预留参数
     * @param  string $width    [description]
     * @return [type]           [description]
     */
    public function keyEditor($name, $title, $subtitle = null, $type = 'ueditor', $config = '', $style = '',$param='', $width='100%')
    {
        if(empty($type) || $type==''){
            $type='ueditor';
        }
        //兼容老版
        if(is_array($config)){
            $config = '';
        }
        $key = ['name' => $name, 'title' => $title, 'subtitle' => $subtitle, 'config' => $config, 'style' => $style, 'param'=>$param , 'width'=>$width, 'type' => $type];
        $this->_keyList[] = $key;
        return $this;
    }

    /**
     * 日期选择器：支持三种类型
     * @param $name
     * @param $title
     * @param null $subtitle
     * @param string $type 类型：支持（time）（datetime，默认）(date)
     * @return $this
     */
    public function keyTime($name, $title, $subtitle = null,$type='datetime')
    {
        return $this->key($name, $title, $subtitle, $type);
    }

    public function keyCreateTime($name = 'create_time', $title = '创建时间', $subtitle = null)
    {
        return $this->keyTime($name, $title, $subtitle);
    }

    public function keyUpdateTime($name = 'update_time', $title = '修改时间', $subtitle = null)
    {
        return $this->keyTime($name, $title, $subtitle);
    }

    public function keyBool($name, $title, $subtitle = null)
    {
        $map = [1 => '是', 0 => '否'];
        return $this->keyRadio($name, $title, $subtitle, $map);
    }

    public function keySwitch($name, $title, $subtitle = null)
    {
        $map = [1 => '开', 0 => '关'];
        return $this->keyRadio($name, $title, $subtitle, $map);
    }

    public function keyKanban($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'kanban');
    }

    public function keyTitle($name = 'title', $title = '标题', $subtitle = null)
    {
        return $this->keyText($name, $title, $subtitle);
    }

    /**单文件上传
     * @param $name
     * @param $title
     * @param null $subtitle
     */
    public function keySingleFile($name, $title, $subtitle = null, $opt = ['enforce'=>'auto']){
        return  $this->key($name,$title,$subtitle,'singleFile', $opt);
    }

    /**多文件上传
     * @param $name
     * @param $title
     * @param null $subtitle
     */
    public function keyMultiFile($name, $title, $subtitle = null){
        return   $this->key($name,$title,$subtitle,'multiFile');
    }

    public function keySingleImage($name, $title, $subtitle = null)
    {
        return $this->key($name, $title, $subtitle, 'singleImage');
    }

    public function keyMultiImage($name, $title, $subtitle = null, $limit = '')
    {
        return $this->key($name, $title, $subtitle, 'multiImage', $limit);
    }

    /**
     * 依赖OSS插件单音频web直传
     *
     * @param      <type>  $name      The name
     * @param      <type>  $title     The title
     * @param      <type>  $subtitle  The subtitle
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function keySingleAudio($name, $title, $subtitle = null)
    {   
        return $this->key($name, $title, $subtitle, 'singleAudio');
    }

    /**
     * 单视频web直传
     *
     * @param      <type>  $name      The name
     * @param      <type>  $title     The title
     * @param      <type>  $subtitle  The subtitle
     * @author     大蒙 <59262424@qq.com>
     * @return     <type>  ( description_of_the_return_value )
     */
    public function keySingleVideo($name, $title, $subtitle = null)
    {   
        return $this->key($name, $title, $subtitle, 'singleVideo');
    }

    public function keySingleUserGroup($name, $title, $subtitle = null)
    {
        $options = $this->readUserGroups();
        return $this->keySelect($name, $title, $subtitle, $options);
    }

    public function keyMultiUserGroup($name, $title, $subtitle = null)
    {
        $options = $this->readUserGroups();
        return $this->keyCheckBox($name, $title, $subtitle, $options);
    }

    /** 
     * 添加城市选择
     * @param array $name
     * @param $title
     * @param $subtitle
     * @return AdminConfigBuilder
     */
    public function keyCity($name = ['province', 'city', 'district'],$title = '', $subtitle = '')
    {
        //修正在编辑信息时无法正常显示已经保存的地区信息
        return $this->key($name, $title, $subtitle, 'city');
    }

    /**
     * 增加数据时通过列表页选择相应的关联数据ID  -_-。sorry！表述不清楚..
     * @param  unknown $name 字段名
     * @param  unknown $title 标题
     * @param  string $subtitle 副标题（说明）
     * @param  unknown $url 选择数据的列表页地址，Url方法地址'index/index'
     * @return $this
     */
    public function keyDataSelect($name, $title, $subtitle = null, $url = '')
    {
        $urls = url($url, array('inputid' => $name));
        return $this->key($name, $title, $subtitle, 'dataselect', $urls);
    }

    /**
     * 按钮
     */
    public function button($title, $attr = array())
    {
        $this->_buttonList[] = array('title' => $title, 'attr' => $attr);
        return $this;
    }

    /**
     * 确认按钮
     */
    public function buttonSubmit($url = '', $title = '确定')
    {
        if ($url == '') {
            $url = url(request()->action(),$_GET);
        }
        $this->savePostUrl($url);

        $attr = array();
        $attr['class'] = "btn submit-btn btn-success";
        $attr['id'] = 'submit';
        $attr['type'] = 'submit';
        $attr['target-form'] = 'form-horizontal';
        return $this->button($title, $attr);
    }

    /**
     * 返回按钮
     */
    public function buttonBack($title = '返回')
    {
        $attr = array();
        $attr['onclick'] = 'javascript:history.back(-1);return false;';
        $attr['class'] = 'btn btn-return';
        return $this->button($title, $attr);
    }

    public function buttonLink($title='按钮',$attr = []){
        $attr['onclick'] = 'javascript:location.href=\''.$attr['href'].'\';return false;';
        return $this->button($title, $attr);
    }

    public function data($list)
    {
        $this->_data = $list;
        return $this;
    }

    public function savePostUrl($url)
    {
        if ($url) {
            $this->_savePostUrl = $url;
        }
    }

    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '')
    {
        //将数据融入到key中
        foreach ($this->_keyList as &$e) {
            
            //修正在编辑信息时无法正常显示已经保存的地区信息/***修改的代码****/
            if (is_array($e['name'])) {
                $i = 0;
                $n = count($e['name']);
                while ($n > 0) {
                    empty($this->_data[$e['name'][$i]]) && $this->_data[$e['name'][$i]] = null;
                    $e['value'][$i] = $this->_data[$e['name'][$i]];

                    $e['value'][$i] = $this->_data[$e['name'][$i]];
                    $i++;
                    $n--;
                }
            } else {
                //修复未定义数组下标提示
                empty($this->_data[$e['name']]) && $this->_data[$e['name']] = null;
                $e['value'] = $this->_data[$e['name']];
            }
        }
        //编译按钮的html属性
        foreach ($this->_buttonList as &$button) {
            $button['attr'] = $this->compileHtmlAttr($button['attr']);
        }
        //设置meta标题
        $this->setTitle($this->_title);

        //显示页面
        View::assign('group', $this->_group);
        View::assign('title', $this->_title);
        View::assign('suggest', $this->_suggest);
        View::assign('keyList', $this->_keyList);
        View::assign('buttonList', $this->_buttonList);
        View::assign('savePostUrl', $this->_savePostUrl);

        parent::display('config');
    }

    /**
     * keyChosen  多选菜单
     * @param $name
     * @param $title
     * @param null $subtitle
     * @param $options
     * @return $this
     */
    public function keyChosen($name, $title, $subtitle = null, $options = null)
    {
        // 解析option数组
        if (key($options) === 0) {
            if (!is_array(reset($options))) {
                foreach ($options as $key => &$val) {
                    $val = array($val, $val);
                }
                unset($key, $val);
            }
        } else {
            foreach ($options as $key => &$val) {
                foreach ($val as $k => &$v) {
                    if (!is_array($v)) {
                        $v = array($v, $v);
                    }
                }
                unset($k, $v);
            }
            unset($key, $val);
        }
        return $this->key($name, $title, $subtitle, 'chosen', $options);
    }


    /**
     * keyMultiInput  输入组组件
     * @param $name
     * @param $title
     * @param $subtitle
     * @param $config
     * @param null $style
     * @return $this
     */
    public function keyMultiInput($name, $title, $subtitle, $config, $style = null)
    {
        empty($style) && $style = 'width:400px;';

        if(strpos($name,'|')){
            $name = explode('|', $name);
        }
        
        $key = array('name' => $name, 'title' => $title, 'subtitle' => $subtitle, 'type' => 'multiInput', 'config' => $config, 'style' => $style);
        $this->_keyList[] = $key;
        return $this;
    }

    /**插入配置分组
     * @param       $name 组名
     * @param array $list 组内字段列表
     * @return $this
     */
    public function group($name, $list = array())
    {   
        !is_array($list) && $list = explode(',', $list);
        $this->_group[$name] = $list;
        return $this;
    }

    public function groups($list = array())
    {
        foreach ($list as $key => $v) {
            $this->_group[$key] = is_array($v) ? $v : explode(',', $v);
        }
        return $this;
    }

    private function readUserGroups()
    {
        $list = Db::name('AuthGroup')->where('status', '=', 1)->order('id asc')->select();

        $result = [];
        $result[0] = '未登陆';
        foreach ($list as $group) {
            $result[$group['id']] = $group['title'];
        }
        return $result;
    }

    /**
     * parseKanbanArray  解析看板数组
     * @param $data
     * @param array $item
     * @param array $default
     * @return array|mixed
     */
    public function parseKanbanArray($data, $item = [], $default = [])
    {
        if (empty($data)) {
            $head = reset($default);
            if (!array_key_exists("items", $head)) {
                $temp = array();
                foreach ($default as $k => $v) {
                    $temp[] = array('id' => $k, 'title' => $k, 'items' => $v);
                }
                $default = $temp;
            }
            $result = $default;
        } else {
            $data = json_decode($data, true);

            $item_d = getSubByKey($item, 'id');
            $all = array();
            foreach ($data as $key => $v) {
                $data_id = getSubByKey($v['items'], 'id');
                $data_d[$key] = $v;
                unset($data_d[$key]['items']);
                $data_d[$key]['items'] = $data_id ? $data_id : [];
                $all = array_merge($all, $data_id);
            }
            unset($v);
            foreach ($item_d as $val) {
                if (!in_array($val, $all)) {
                    $data_d[0]['items'][] = $val;
                }
            }
            unset($val);
            foreach ($all as $v) {
                if (!in_array($v, $item_d)) {
                    foreach ($data_d as $key => $val) {
                        $key_search = array_search($v, $val['items']);
                        if (!is_bool($key_search)) {
                            unset($data_d[$key]['items'][$key_search]);
                        }
                    }
                    unset($val);
                }
            }
            unset($v);
            $item_t = [];
            foreach ($item as $val) {

                $item_t[$val['id']] = $val['title'];
            }
            unset($v);

            foreach ($data_d as &$v) {
                foreach ($v['items'] as &$val) {
                    $t = $val;
                    $val = [];
                    $val['id'] = $t;
                    $val['title'] = $item_t[$t];
                }
                unset($val);
            }
            unset($v);

            $result = $data_d;
        }
        return $result;

    }

    public function setDefault($data, $key, $value)
    {
        $data[$key] = $data[$key]!=null ? $data[$key] : $value;
        return $data;
    }

    public function keyDefault($key, $value)
    {
        $data = $this->_data;
        empty($data[$key]) && $data[$key]=null;
        $data[$key] = $data[$key]!==null ? $data[$key] : $value;
        $this->_data = $data;
        return $this;
    }

    public function keyUserDefined($name,$title,$subtitle,$html='',$param=''){
        View::assign('param',$param);
        View::assign('name',$name);

        $html = $this->parseTemplate($html);
        
        $key = array('name'=>$name, 'title' => $title, 'subtitle' => $subtitle, 'type' => 'userDefined', 'definedHtml' => $html);
        $this->_keyList[] = $key;
        return $this;
    }


    /**
     * 自定义JS
     * @param [type] $script [description]
     */
    public function customJs($script){
        View::assign('myJs',$script);
    }

    /**
     * 解析html是文件还是html字符串
     * @access private
     * @return string
     */
    private function parseTemplate($html)
    {
        $file = '';
        // 获取视图根目录
        if (strpos($html, '@')) {
            // 跨模块调用
            list($module, $template) = explode('@', $html);
        }
        if(isset($module)){
            $path = APP_PATH . $module . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
            $file =  $path . ltrim($template, '/') . '.' . ltrim('html', '.');
        }
        
        if(is_file($file)){
            $html = View::fetch($html);
        }

        return $html;
    }
}