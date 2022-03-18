<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Config.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/3/16
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\api\controller;
use app\common\controller\Base;
use app\common\logic\Config as ConfigLogic;

class Config extends Base
{
    protected $ConfigLogic;
    protected $params;
    function __construct()
    {
        parent::__construct();
        $this->ConfigLogic = new ConfigLogic();
        $this->params = request()->param();
    }

    function getConfig(){
        $frontend = [
            'SITE_CLOSE',
            'SITE_CLOSE_HINT',
            'WEB_SITE_STYLE',
            'WEB_SITE_NAME',
            'WEB_SITE_DESCRIPTION',
            'WEB_SITE_ICP',
            'WEB_SITE_LOGO',
            'WEB_SITE_GICP',
            'WEB_SITE_COPY_RIGHT',
            'SERVICE_TEL',
            'SERVICE_CONSULT',
            'SERVICE_BUSINESS',
            'SERVICE_QRCODE',
            'SERVICE_WEIXINKF'
        ];
        $config = config('system');
        $withdraw = config('extend');
        $config = $this->ConfigLogic->frontend($this->params['shopid']);
        $this->success('success',$config);
    }
}