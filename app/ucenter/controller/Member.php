<?php
namespace app\ucenter\controller;

use think\App;
use think\facade\Request;
use think\facade\Session;
use think\facade\Db;
use think\facade\Cache;
use think\Response;
use think\Validate;
use think\exception\ValidateException;
use app\common\model\Member as CommonMember;
use app\common\controller\Base;

/**
 * 用户控制器
 */
class Member extends Base
{
    protected $middleware = [\app\common\middleware\CheckAuth::class];

    public function user()
    {
        dump($this->request->uid);
    }

    /**
     * saveAvatar  保存头像
     */
    public function saveAvatar()
    {
        //跳回的地址
        $redirect_url = session('temp_login_uid') ? url('ucenter/member/step', ['step' => get_next_step('change_avatar')]) : url('ucenter/config/avatar');

        $aCrop = input('post.crop', '', 'text');
        $aUid = session('temp_login_uid') ? session('temp_login_uid') : is_login();
        $aPath = input('post.path', '', 'text');
		
        if (empty($aCrop)) {
            $this->success(lang('_SUCCESS_SAVE_').lang('_EXCLAMATION_'),$redirect_url );
        }
        $returnPath = controller('ucenter/UploadAvatar', 'widget')->cropPicture($aCrop,$aPath);

        $driver = modC('PICTURE_UPLOAD_DRIVER','local','config');

        //更新数据库数据
        $data = [
            'uid' => $aUid,
            'status' => 1, 
            'is_temp' => 0,
            'path' => $returnPath,
            'driver'=> $driver, 
            'create_time' => time()
        ];
        $res = Db::name('avatar')->where(['uid' => $aUid])->update($data);
        if (!$res) {
            Db::name('avatar')->insert($data);
        }
        clean_query_user_cache($aUid, array('avatars','avatars_html'));

        $this->success(lang('_SUCCESS_AVATAR_CHANGE_').lang('_EXCLAMATION_'), $redirect_url);
    }

}