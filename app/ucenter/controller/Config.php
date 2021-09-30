<?php
declare (strict_types = 1);

namespace app\ucenter\controller;

use think\facade\Db;
use think\facade\View;
use app\common\controller\Common;

class Config extends Common
{
    /**
     * 用户中心首页
     */
    public function index()
    {
        // 验证登录
        if (!is_login()) {
            return redirect('/ucenter/common/login');
        }

        $aUid = input('get.uid', is_login(), 'intval');
        $aNickname = input('post.nickname', '', 'text');
        $aSex = input('post.sex', 0, 'intval');
        $aSignature = input('post.signature', '', 'text');

        if (Request()->isPost()) {
            $this->checkNickname($aNickname);
            $user['nickname'] = $aNickname;
            $user['sex'] = $aSex;
            $user['signature'] = $aSignature;

            $res = Db::name('Member')->where(['uid'=>get_uid()])->update($user);
            if ($res) {
                return $this->success('设置成功');

            } else {
                return $this->error('设置失败');
            }

        } else {
            //调用API获取基本信息
            $user = query_user($aUid,['nickname', 'signature', 'email', 'mobile', 'avatar', 'sex']);
            //显示页面
            View::assign('user', $user);
            $this->_getExpandInfo();
            // 当前方法赋值变量
            View::assign('tab', 'index');

            return View::fetch();
        }
    }

    /**获取用户扩展信息
     * @param null $uid
     */
    public function _getExpandInfo($uid = null)
    {
        $profile_group_list = $this->_profile_group_list($uid);
        if ($profile_group_list) {
            $info_list = $this->_info_list($profile_group_list[0]['id'], $uid);
            View::assign('info_list', $info_list);
            View::assign('profile_group_id', $profile_group_list[0]['id']);
            //dump($info_list);exit;
        }
        foreach ($profile_group_list as &$v) {
            $v['fields'] = $this->_getExpandInfoByGid($v['id']);
        }

        View::assign('profile_group_list', $profile_group_list);
    }

    /**显示某一扩展分组信息
     * @param null $profile_group_id
     * @param null $uid
     */
    public function _getExpandInfoByGid($profile_group_id = null)
    {
        $res = Db::name('field_group')->where(array('id' => $profile_group_id, 'status' => '1'))->find();
        if (!$res) {
            return array();
        }
        $info_list = $this->_info_list($profile_group_id);

        return $info_list;
        View::assign('info_list', $info_list);
        View::assign('profile_group_id', $profile_group_id);
        View::assign('profile_group_list', $profile_group_list);
    }


    /**扩展信息分组列表获取
     * @return mixed
     */
    private function _profile_group_list($uid = null)
    {
        $profile_group_list = array();
        
        return $profile_group_list;
    }

    /**分组下的字段信息及相应内容
     * @param null $id 扩展分组id
     * @param null $uid
     * @author 大蒙 59262424@qq.com
     */
    public function _info_list($id = null, $uid = null)
    {
        $info_list = null;

        if (isset($uid) && $uid != is_login()) {
            //查看别人的扩展信息
            $field_setting_list = Db::name('field_setting')->where(array('profile_group_id' => $id, 'status' => '1', 'visiable' => '1', 'id' => array('in', $fields_list)))->order('sort asc')->select();

            if (!$field_setting_list) {
                return null;
            }
            $map['uid'] = $uid;
        } else if (is_login()) {
            $field_setting_list = Db::name('field_setting')->where(array('profile_group_id' => $id, 'status' => '1', 'id' => array('in', $fields_list)))->order('sort asc')->select();

            if (!$field_setting_list) {
                return null;
            }
            $map['uid'] = is_login();

        } else {
            $this->error(lang('_ERROR_PLEASE_LOGIN_').lang('_EXCLAMATION_'));
        }
        foreach ($field_setting_list as $val) {
            $map['field_id'] = $val['id'];
            $field = Db::name('field')->where($map)->find();
            $val['field_content'] = $field;
            $info_list[$val['id']] = $val;
            unset($map['field_id']);
        }

        return $info_list;
    }

    /**
     * saveAvatar  保存头像
     */
    public function avatar()
    {
        if (Request()->isPost()) {
            $aCrop = input('post.crop', '', 'text');
            $aUid = session('temp_login_uid') ? session('temp_login_uid') : is_login();
            $aPath = input('post.path', '', 'text');
            
            if (empty($aCrop)) {
                return $this->error('参数错误');
            }

            //更新数据库数据
            $data = [
                'uid' => $aUid,
                'status' => 1, 
            ];
            $res = Db::name('Member')->where(['uid' => $aUid])->update($data);
            if (!$res) {
                Db::name('Member')->insert($data);
            }

            return $this->success('SUCCESS');

        }else{

            // 基本信息
            $user = query_user(is_login(),['nickname', 'avatar']);
            
            //显示页面
            View::assign('user', $user);

            return View::fetch();
        }
        
    }

}