<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: MiniProgram.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/10/14
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\unions\controller\admin;
use app\admin\builder\AdminConfigBuilder;
use app\admin\controller\Admin as MuuAdmin;
use app\common\model\Module;
use app\unions\model\MiniProgramConfig;

class MiniProgram extends MuuAdmin{
    private $miniProgramModel;
    private $shopid;
    function __construct()
    {
        parent::__construct();
        $this->miniProgramModel = new MiniProgramConfig();
        $this->shopid = 0;
    }

    /**
     * 商户小程序配置
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function shopIndex(){
        $module_name = input('param.module_name');
        $module = Module::where('name',$module_name)->find();
        if (!$module){
            return $this->error('没有找到此应用');
        }
        if (request()->isAjax()){
            $params = input('post.');
            $params = $this->miniProgramModel->formatParams($params);
            foreach ($params as $key => $value){
                $data = [
                    'id' => 0,
                    'shopid' => $this->shopid,
                    'name' => $module_name,
                    'title' => $value['title'],
                    'description' => $value['description'],
                    'platform' => $key,
                    'appid' => $value['appid'],
                    'secret' => $value['secret'],
                    'originalid' => $value['originalid'],
                ];
                $map = [
                    ['shopid' ,'=' ,$this->shopid],
                    ['platform' ,'=' , $key],
                    ['name' ,'=' ,$module_name]
                ];
                $id = $this->miniProgramModel->where($map)->value('id');
                if ($id){
                    $data['id'] = $id;
                }
                $this->miniProgramModel->edit($data);
            }
            $this->success('保存成功');
        }else{
            $group = [];
            $platform = [];
            //查询分组数据
            if ($module['weixin_mp']){
                $group['wechat'] = '微信小程序';
                $platform[] = 'wechat';
            }
            if ($module['baidu_mp']){
                $group['baidu'] = '百度小程序';
                $platform[] = 'baidu';
            }
            if ($module['alipay_mp']){
                $group['alipay'] = '支付宝小程序';
                $platform[] = 'alipay';
            }
            if ($module['bytedance_mp']){
                $group['bytedance'] = '字节跳动小程序';
                $platform[] = 'bytedance';
            }
            //查询数据
            $list = $this->miniProgramModel->where([
                ['shopid' ,'=' ,$this->shopid],
                ['platform' ,'in' ,$platform],
                ['name' ,'=' ,$module_name],

            ])->select()->toArray();
            $list = $this->miniProgramModel->handleBuilder($list);
            $builder = new AdminConfigBuilder();
            $builder->title('小程序配置')->suggest('基于第三方授权各项参数配置');

            foreach ($group as $key => $value){
                $builder
                    ->keyText($key . '_title', '小程序名称', '小程序名称.')
                    ->keyText($key . '_appid', 'APPID', 'APPID是小程序的ID，请您妥善保管.')
                    ->keyText($key . '_secret', 'AppSecret', 'AppSecret是小程序的密钥，具有该账户完全的权限，请您妥善保管.')
                    ->keyText($key . '_originalid', '原始ID', '小程序原始ID')
                    ->keyTextArea($key . '_description', '小程序描述', '小程序描述')
                    ->group($value, [
                        $key . '_title',
                        $key . '_appid',
                        $key . '_secret',
                        $key . '_originalid',
                        $key . '_description',
                    ]);
            }
            $builder->data($list);
            $builder->buttonSubmit();
            $builder->display();
        }
    }
//    public parse
}