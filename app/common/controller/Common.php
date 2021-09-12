<?php
namespace app\common\controller;

use think\facade\Db;
use think\facade\View;
use app\common\model\Channel;
use app\common\controller\Base;


/**
 * 前台控制器基类
 */
class Common extends Base
{
    public $title;
    public $keywords;
    public $description;
    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        // 控制器初始化
        $this->initialize();
    }

    /**
     * 初始化
     */
	public function initialize()
    {
 		//获取站点LOGO
 		$this->initLogo();
        //获取前端导航菜单
        $this->initNav();
		//获取用户菜单
		$this->initUserNav();
        //用户登录、注册
        $this->initRegAndLogin();
        //获取用户基本资料
        $this->initUserBaseInfo();
    }

    /**
     * 初始化站点LOGO
     */
    private function initLogo()
    {
        $logo = config('system.WEB_SITE_LOGO');
        $logo = $logo ? get_attachment_src($logo) : STATIC_URL . '/common/images/logo.png';

        View::assign('logo', $logo);
    }

    /**
     * 初始化前端导航
     */
    private function initNav()
    {
        $channelModel = new Channel();
        $nav = $channelModel->lists();
        View::assign('nav',$nav);
    }

    /**
     * 初始化用户导航
     */
    private function initUserNav()
    {
        $user_nav=Db::name('UserNav')->order('sort asc')->where('status','=', 1)->select();
        View::assign('user_nav',$user_nav);
    }

    /**
     * 初始化用户基本信息
     */
    private function initUserBaseInfo()
    {
        $common_header_user = query_user(is_login(), ['nickname','avatar']);
        View::assign('common_header_user',$common_header_user);
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