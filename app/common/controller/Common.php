<?php

namespace app\common\controller;

use think\facade\Config;
use think\facade\Db;
use think\facade\View;
use app\common\model\Channel;
use app\common\model\SeoRule;
use app\common\model\Member;
use app\common\model\Module;
use app\common\logic\Config as ConfigLogic;

/**
 * 前台控制器基类
 */
class Common extends Base
{
    public $shopid = 0; //店铺ID
    public $module; //请求的应用
    public $app_name;
    public $muu_config_data;
    public $title = '';
    public $keywords = '';
    public $description = '';
    protected $params;
    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        $this->initSiteStatus();
        $this->params = request()->param();
        $this->shopid = $this->params['shopid'] ?? 0;

        // 控制器初始化
        $this->initialize();
    }

    /**
     * 初始化
     */
    public function initialize()
    {
        //获取shopid
        $this->initShopid();
        //记住登录
        $this->initRemberLogin();
        //获取应用名
        $this->initModuleName();
        //获取系统配置
        $this->initMuuConfig();
        //获取micro模块配置
        $this->initMicroConfig();
        //获取公众号配置
        $this->initWechatConfig();
        //获取前端导航菜单
        $this->initNavbar();
        //获取底部导航菜单
        $this->initFooterNav();
        //获取用户菜单
        $this->initUserNav();
        //用户登录、注册
        $this->initRegAndLogin();
        //获取用户基本资料
        $this->initUserBaseInfo();
        //seo规则
        $this->initSeo();
    }

    /**
     * 初始化站点状态
     */
    protected function initSiteStatus()
    {
        // 判断站点是否关闭
        if (strtolower(App('http')->getName()) != 'ucenter' && strtolower(App('http')->getName()) != 'admin') {
            if (!Config::get('system.SITE_CLOSE')) {
                $type = (request()->isJson() || request()->isAjax()) ? 'json' : 'html';
                $result = [
                    'code' => 0,
                    'msg' => '站点临时关闭，请稍后访问',
                ];
                if ($type == 'html') {
                    $response = view(config('app.dispatch_error_tmpl'), $result);
                } else if ($type == 'json') {
                    $response = json($result);
                }
                throw new \think\exception\HttpResponseException($response);
            }
        }
    }

    protected function initShopid()
    {
        View::assign('shopid', $this->shopid);
    }

    /**
     * 实例化应用名称
     */
    protected function initModuleName()
    {
        $this->module = $this->app_name = $this->params['app'] ?? App('http')->getName();
    }

    protected function initMuuConfig()
    {
        $this->muu_config_data = $muu_config_data = (new ConfigLogic())->frontend($this->shopid);
        View::assign('muu_config_data', $muu_config_data);
    }

    protected function initMicroConfig()
    {
        $micro_config_data = [];
        $is_install = (new Module())->checkInstalled('micro');
        if($is_install){
            $MicroConfigModel = new \app\micro\model\MicroConfig();
            $micro_config_data = $MicroConfigModel->getConfig($this->shopid);
        }
        View::assign('micro_config_data', $micro_config_data);
    }

    /**
     * 获取公众号配置
     */
    public function initWechatConfig()
    {
        $weixin_config_data = (new \app\channel\model\WechatConfig())->where('shopid', $this->shopid)->field('title,desc,cover,qrcode,appid,auth_login')->find();
        if ($weixin_config_data) {
            $weixin_config_data = $weixin_config_data->toArray();
            $weixin_config_data = (new \app\channel\logic\OfficialAccount())->formatData($weixin_config_data);
        }
        View::assign('weixin_config_data', $weixin_config_data);
    }

    /**
     * 初始化前端导航
     */
    private function initNavbar()
    {
        $channelModel = new Channel();
        $nav = $channelModel->lists('navbar');
        $nav_count = count($nav);
        View::assign('navbar_count', $nav_count);
        View::assign('navbar', $nav);
    }

    private function initFooterNav()
    {
        $channelModel = new Channel();
        $nav = $channelModel->lists('footer');
        View::assign('footer_nav', $nav);
    }

    /**
     * 初始化用户导航
     */
    private function initUserNav()
    {
        $user_nav = Db::name('UserNav')->order('sort asc')->where('status', '=', 1)->select();
        View::assign('user_nav', $user_nav);
    }

    /**
     * 初始化用户基本信息
     */
    private function initUserBaseInfo()
    {
        $common_header_user = query_user(is_login(), ['nickname', 'avatar']);
        View::assign('common_header_user', $common_header_user);
    }

    /**
     * 初始化用户登陆注册
     */
    private function initRegAndLogin()
    {
        // 用户注册登陆
        $open_quick_login = config('system.OPEN_QUICK_LOGIN');
        View::assign('open_quick_login', $open_quick_login);
        $register_switch = config('system.USER_REG_SWITCH');
        View::assign('register_switch', $register_switch);
        $login_url = url('ucenter/Common/login');
        View::assign('login_url', $login_url);
    }

    private function initSeo()
    {
        $app = strtolower(app('http')->getName());
        $controller = strtolower(request()->controller());
        $action = strtolower(request()->action());

        // 查询是否有Seo规则
        $rule = (new SeoRule())->getRule($app, $controller, $action);
        if ($rule) {
            $this->setTitle($rule['seo_title']);
            $this->setKeywords($rule['seo_keywords']);
            $this->setDescription($rule['seo_description']);
        }
    }

    protected function initRemberLogin()
    {
        //记住登录
        (new Member())->rembemberLogin($this->shopid);
    }

    public function setTitle($title)
    {
        $this->title = $title;
        View::assign('title', $this->title);
    }

    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
        View::assign('keywords', $this->keywords);
    }

    public function setDescription($description)
    {
        $this->description = $description;
        View::assign('description', $this->description);
    }
}
