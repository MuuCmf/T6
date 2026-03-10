<?php

namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use app\common\model\Attachment;
use app\common\model\Member as MemberModel;
use app\common\model\MemberSync as MemberSyncModel;
use app\common\model\AuthGroup;
use app\common\model\ScoreType as ScoreTypeModel;
use app\common\model\ScoreLog as ScoreLogModel;

/**
 * 后台用户控制器
 */
class Member extends Admin
{
    protected $MemberModel;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        parent::__construct();

        $this->MemberModel = new MemberModel();
    }

    /**
     * 用户管理首页
     */
    public function index()
    {
        $search = input('search', '', 'text');
        if (!empty($search)) {
            $uids = $this->MemberModel
                ->where('uid', '=', $search)
                ->whereOr('username', 'like', '%' . $search . '%')
                ->whereOr('nickname', 'like', '%' . $search . '%')
                ->whereOr('mobile', 'like', '%' . $search . '%')
                ->whereOr('email', 'like', '%' . $search . '%')
                ->column('uid');
            if (!empty($uids)) {
                $map[] = ['uid', 'in', $uids];
            } else {
                $map[] = ['nickname', 'like', '%' . $search . '%'];
            }
        }

        //排序
        $order = input('order', 'create_time', 'text');
        $order_type = '';
        if ($order == 'uid') {
            $order_type = 'uid desc';
        }
        if ($order == 'create_time') {
            $order_type = 'create_time desc';
        }
        if ($order == 'last_login_time') {
            $order_type = 'last_login_time desc';
        }
        if ($order == 'login') {
            $order_type = 'login desc';
        }
        $map[] = ['status', '>=', 0];
        // 每页显示数量
        $rows = input('rows', 20, 'intval');
        View::assign('rows', $rows);
        $list = $this->MemberModel->where($map)->order($order_type)->paginate(['list_rows' => $rows, 'query' => request()->param()], false);
        $pager = $list->render();
        $list = $list->toArray();
        $list_arr = $list['data'];

        foreach ($list_arr as &$v) {
            $v = $this->MemberModel->info($v['uid'], '*');
        }
        unset($v);

        if (request()->isAjax()) {
            $list['data'] = $list_arr;
            return $this->success('success', $list);
        }
        $this->setTitle('用户列表');
        View::assign('title', '用户列表');
        View::assign('pager', $pager);
        View::assign('_list', $list_arr);
        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        return View::fetch();
    }

    /**
     * 重置用户密码
     */
    public function initPass()
    {
        $ids = input('uid');
        !is_array($ids) && $ids = explode(',', $ids);

        foreach ($ids as $key => $val) {
            if (!query_user($val, ['uid'])) {
                unset($ids[$key]);
            }
        }
        if (!count($ids)) {
            return $this->error('重置失败');
        }
        $data['password'] = user_md5('123456', config('auth.auth_key'));
        $res = $this->MemberModel->where('uid', 'in', $ids)->update(['password' => $data['password']]);
        if ($res) {
            return $this->success('重置密码成功');
        } else {
            return $this->error('重置用户密码失败');
        }
    }

    /**
     * 用户资料详情修改
     * @param string $uid
     * @author 大蒙<59262424@qq.com>
     */
    public function edit()
    {
        $uid = input('uid', 0, 'intval');
        if (request()->isPost()) {
            $data = input();

            // 初始化写入数据
            if (!empty($uid)) {
                $member_data['uid'] = $uid;
            }

            // 头像部分处理
            $crop = input('post.crop', '', 'text');
            $member_data['avatar'] = $data['avatar'];
            if (!empty($crop) && !empty($data['avatar'])) {
                // 裁切图片
                $Attachment = new Attachment();
                $path = $Attachment->cropImage($data['avatar'], $crop);
                $member_data['avatar'] = $path;
            } else {
                // 用户资料
                $member_data['nickname'] = $data['nickname'];
                $member_data['username'] = $data['username'];
                $member_data['email'] = $data['email'];
                $member_data['mobile'] = $data['mobile'];
                $member_data['sex'] = intval($data['sex']);
                $member_data['status'] = intval($data['status']);

                if ($member_data['username'] == '' && $member_data['email'] == '' && $member_data['mobile'] == '') {
                    return $this->error('用户名、邮箱、手机号，至少填写一项！');
                }
                $check_nickname = $this->MemberModel->checkNickname($member_data['nickname'], $uid);
                if ($check_nickname !== true) {
                    return $this->error($check_nickname);
                }

                $check_username = $this->MemberModel->checkUsername($member_data['username'], $uid);
                if ($check_username !== true) {
                    return $this->error($check_username);
                }

                $check_email = $this->MemberModel->checkEmail($member_data['email'], $uid);
                if ($check_email !== true) {
                    return $this->error($check_email);
                }

                $check_mobile = $this->MemberModel->checkMobile($member_data['mobile'], $uid);
                if ($check_mobile !== true) {
                    return $this->error($check_mobile);
                }
            }

            // 写入数据并返回UID
            $uid = $this->MemberModel->edit($member_data);

            /* 积分 start*/
            $data_score = [];
            foreach ($data as $key => $val) {
                if (substr($key, 0, 5) == 'score') {
                    $data_score[$key] = intval($val);
                }
            }

            $member = query_user($uid);
            foreach ($data_score as $key => $val) {
                // 值相同跳过
                if (intval($val) == intval($member[$key])) {
                    continue;
                } else {
                    //写入积分
                    $this->MemberModel->where('uid', $uid)->update($data_score);
                    //写积分变化日志
                    if (intval($val) > intval($member[$key])) {
                        $action = 'inc';
                        $value = intval($val) - intval($member[$key]);
                    } else {
                        $action = 'dec';
                        $value = intval($member[$key]) - intval($val);
                    }
                    $scoreLogModel = new ScoreLogModel();
                    $scoreLogModel->addScoreLog($uid, cut_str('score', $key, 'l'), $action, $value, '', 0, get_nickname(is_login()) . '后台调整');
                }
            }
            /* 积分 end*/

            /*用户组 start*/
            if(isset($data['auth_group']) && !empty($data['auth_group'])){
                $authGroup = new AuthGroup();
                $authGroup->addToGroup($uid, $data['auth_group']);
            }
            /*用户组END*/

            return $this->success('保存成功', $uid, cookie('__forward__'));
        } else {

            // 获取启用的积分类型
            $score_types = (new ScoreTypeModel())->getTypeList(['status' => 1]);

            // 获取用户数据
            if(empty($uid)){
                $member['uid'] = 0;
                $member['nickname'] = '';
                $member['username'] = '';
                $member['email'] = '';
                $member['mobile'] = '';
                $member['sex'] = 0;
                $member['avatar'] = 'static/images/default_avatar.jpg';
                $member['avatar64'] = request()->domain() . '/static/common/images/default_avatar_64_64.jpg';
                $member['avatar128'] = request()->domain() . '/static/common/images/default_avatar_128_128.jpg';
                $member['avatar256'] = request()->domain() . '/static/common/images/default_avatar_256_256.jpg';
                $member['avatar512'] = request()->domain() . '/static/common/images/default_avatar_512_512.jpg';
                $member['score'] = $score_types;
                $member['status'] = 1;
            }else{
                $member = query_user($uid);
            }
            // 用户拥有的权限组
            $auth = Db::name('auth_group_access')->where(['uid' => $uid])->select();
            $temp_auth_group_arr = [];
            foreach ($auth as $key => $val) {
                $temp_auth_group_arr[] = $val['group_id'];
            }

            // 系统设置启用的权限组
            $auth_group = Db::name('auth_group')->where('status', '=', 1)->select()->toArray();
            foreach ($auth_group as &$val) {
                $val['checked'] = in_array($val['id'], $temp_auth_group_arr) ? true : false;
            }
            unset($val);

            View::assign('member', $member);
            View::assign('auth_group', $auth_group);
            // 设置页面TITLE
            $this->setTitle('用户资料管理');

            return View::fetch();
        }
    }

    /**
     * 用户详情
     */
    public function detail()
    {
        $uid = input('uid', 0, 'intval');
        if (empty($uid)) {
            return $this->error('缺少参数');
        }
        $map[] = ['uid', '=', $uid];
        $member = query_user($uid);

        // 判断用户是否存在
        if (!is_array($member) || empty($member)) {
            return $this->error('用户数据不存在');
        }
        View::assign('member', $member);
        // 设置页面TITLE
        $this->setTitle('用户详情');
        // 输出模板
        return View::fetch();
    }

    /**
     * 会员状态修改
     */
    public function status()
    {
        $ids = input('uid');
        !is_array($ids) && $ids = explode(',', (string)$ids);
        if (count(array_intersect(explode(',', config('auth.auth_administrator')), $ids)) > 0) {
            return $this->error('不允许对超管进行该操作');
        }
        if (empty($ids)) {
            return $this->error('请选择要操作的数据');
        }
        $status = input('status', 0, 'intval');

        $title = '更新用户';
        switch ($status) {
            case 0:
                $title = '禁用用户';
                break;
            case 1:
                $title = '启用用户';
                break;
            case -1:
                $title = '删除用户';
                break;
            default:
        }
        $data['status'] = $status;
        $res = $this->MemberModel->where('uid', 'in', $ids)->update($data);
        if ($res) {
            return $this->success($title . '成功');
        } else {
            return $this->error($title . '失败');
        }
    }

    /**
     * Modal 选择用户信息
     * @return \think\response\View
     * @throws \think\db\exception\DbException
     */
    public function chooseUser()
    {
        $search = input('search', '', 'text');
        $oauth_type = input('oauth_type', '', 'text'); //授权条件

        //用户名或昵称查询
        $uids = $this->MemberModel
            ->where('uid', '=', $search)
            ->where('username', 'like', '%' . $search . '%')
            ->whereOr('nickname', 'like', '%' . $search . '%')
            ->whereOr('mobile', 'like', '%' . $search . '%')
            ->whereOr('email', 'like', '%' . $search . '%')
            ->column('uid');
        if (!empty($uids)) {
            $map[] = ['m.uid', 'in', $uids];
        } else {
            $map[] = ['m.nickname', 'like', '%' . (string)$search . '%'];
        }

        $map[] = ['m.status', '>=', 0];

        // 每页显示数量
        $rows = input('rows', 15, 'intval');
        if (empty($oauth_type)) {
            $list = $this->MemberModel->alias('m')->where($map)->order('uid', 'desc')->paginate($rows);
        } else {
            $map[] = ['ms.type', '=', $oauth_type];
            $list = $this->MemberModel->alias('m')->join('member_sync ms', 'm.uid = ms.uid')->where($map)->order('m.uid', 'desc')->paginate($rows);
        }

        $pager = $list->render();
        $list = $list->toArray();
        $list_arr = $list['data'];

        foreach ($list_arr as $key => $v) {
            //处理用户头像
            if (empty($list_arr[$key]['avatar'])) {
                $list_arr[$key]['avatar'] = $list_arr[$key]['avatar64'] = $list_arr[$key]['avatar128'] = $list_arr[$key]['avatar256'] = $list_arr[$key]['avatar512'] = request()->domain() . '/static/common/images/default_avatar.jpg';
            } else {
                $list_arr[$key]['avatar64'] = get_thumb_image($list_arr[$key]['avatar'], 64, 64);
                $list_arr[$key]['avatar128'] = get_thumb_image($list_arr[$key]['avatar'], 128, 128);
                $list_arr[$key]['avatar256'] = get_thumb_image($list_arr[$key]['avatar'], 256, 256);
                $list_arr[$key]['avatar512'] = get_thumb_image($list_arr[$key]['avatar'], 512, 512);
            }
        }
        View::assign([
            'pager' => $pager,
            '_list' => $list_arr,
            'oauth_type' => $oauth_type,
            'search' => $search
        ]);

        return View::fetch('_choose_user');
    }
}
