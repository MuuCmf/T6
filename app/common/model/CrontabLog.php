<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: CrontabLog.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/2/24
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\common\model;
class CrontabLog extends Base{
    protected $autoWriteTimestamp = true;

    public static function addLog($params){
        $data = [
            'shopid'    =>  $params['shopid'],
            'cid'       =>  $params['cid'],
            'description'   =>  $params['description'],
            'status'    =>  isset($params['status']) ? $params['status'] : 1,
        ];
        return (new self())->edit($data);
    }
}