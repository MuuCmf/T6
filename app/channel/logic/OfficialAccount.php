<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: OfficialAccount.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/9/24
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\channel\logic;
use app\common\logic\Base as MuuBase;


/**
 * 微信公众号逻辑类
 * Class OfficialAccount
 * @package app\common\service\wechat
 */
class OfficialAccount extends MuuBase {
    function formatData($data){
        $data = $this->setImgAttr($data,'1:1');
        $data = $this->setImgAttr($data,'1:1','qrcode');
        return $data;
    }
}