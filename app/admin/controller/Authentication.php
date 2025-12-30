<?php

namespace app\admin\controller;

use think\facade\Db;
use think\Exception;
use think\facade\View;
use app\common\model\Member as MemberModel;
use app\common\model\MemberAuthentication as AuthenticationModel;

/**
 * 实名用户控制器
 */
class Authentication extends Admin
{
    protected $MemberModel;
    protected $AuthenticationModel;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(
        ?MemberModel $MemberModel = null,
        ?AuthenticationModel $AuthenticationModel = null
    ) {
        parent::__construct();

        $this->MemberModel = $MemberModel ?? new MemberModel();
        $this->AuthenticationModel = $AuthenticationModel ?? new AuthenticationModel();
    }

    public function list()
    {
        $map = [];
        $keyword = input('keyword', '', 'text');
        View::assign('keyword', $keyword);
        $status = input('status', 'all');
        if($status === 'all'){
            $map[] = ['a.status', 'in', [-1, 0, 1, 2]];
        }
        if(intval($status) == 2){
            $map[] = ['a.status', '=', 2];
        }
        if(intval($status) == 1){
            $map[] = ['a.status', '=', 1];
        }
        if(intval($status) == -1){
            $map[] = ['a.status', '=', -1];
        }
        View::assign('status', $status);
        
        if (!empty($keyword)) {
            $uids = $this->MemberModel
                ->where('uid', '=', $keyword)
                ->whereOr('username', 'like', '%' . $keyword . '%')
                ->whereOr('nickname', 'like', '%' . $keyword . '%')
                ->whereOr('mobile', 'like', '%' . $keyword . '%')
                ->whereOr('email', 'like', '%' . $keyword . '%')
                ->column('uid');
            if (!empty($uids)) {
                $map[] = ['a.uid', 'in', $uids];
            } else {
                $map[] = ['m.nickname', 'like', '%' . $keyword . '%'];
            }
        }

        // 每页显示数量
        $rows = input('rows', 15, 'intval');
        $list = $this->AuthenticationModel->alias('a')
        ->join('member m', 'a.uid = m.uid')
        ->where($map)
        ->field('a.*, m.username, m.nickname, m.email, m.mobile, m.avatar, m.authentication')
        ->order('a.uid', 'desc')
        ->paginate($rows);

        $pager = $list->render();
        $list = $list->toArray();
        
        foreach ($list['data'] as &$v) {
            // 头像
            if (empty($v['avatar'])) {
                $v['avatar'] = $v['avatar64'] = $v['avatar128'] = $v['avatar256'] = $v['avatar512'] = request()->domain() . '/static/common/images/default_avatar.jpg';
            } else {
                $v['avatar64'] = get_thumb_image($v['avatar'], 64, 64);
                $v['avatar128'] = get_thumb_image($v['avatar'], 128, 128);
                $v['avatar256'] = get_thumb_image($v['avatar'], 256, 256);
                $v['avatar512'] = get_thumb_image($v['avatar'], 512, 512);
            }

            $v = $this->AuthenticationModel->handle($v);
        }
        unset($v);
        View::assign('pager', $pager);
        View::assign('list', $list);

        // 获取未审核的认证数量
        $unverify = $this->AuthenticationModel->where('status', '=', 1)->count();
        View::assign('unverify', $unverify);
        // 获取未通过审核的认证数量
        $false_verify = $this->AuthenticationModel->where('status', '=', -1)->count();
        View::assign('false_verify', $false_verify);

        $this->setTitle('实名认证列表');
        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        return View::fetch();
    }

    /**
     * 审核认证
     */
    public function verify()
    {
        $uid = input('uid', 0, 'intval');
        if (request()->isPost()) {
            $id = input('id', 0, 'intval');
            $status = input('status', 0, 'intval');
            $uid = input('uid', '=', 'intval');
            $reason = input('reason', '', 'text');

            Db::startTrans();
            try {
                //写入数据
                $data = [
                    'id' => $id,
                    'shopid' => $this->shopid,
                    'uid' => $uid,
                    'status' => $status
                ];
                if ($status == -1) {
                    $data['reason'] = $reason;
                }

                $res = $this->AuthenticationModel->edit($data);
                if (!$res) {
                    throw new Exception('数据写入失败');
                }

                // 更改用户表认证状态值
                $res = $this->MemberModel->edit([
                    'shopid' => $this->shopid,
                    'uid' => $uid,
                    'authentication' => $status
                ]);
                if (!$res) {
                    throw new Exception('数据写入失败');
                }
            } catch (Exception $e) {
                Db::rollback();
                return $this->error('发生错误：' . $e->getMessage());
            }
            Db::commit();
            //返回提示
            return $this->success('提交成功！', $res);
        } else {
            // 查询用户认证数据
            $map = [
                ['shopid', '=', $this->shopid],
                ['uid', '=', $uid]
            ];

            $data = $this->AuthenticationModel->where($map)->find();
            if (!empty($data)) {
                $data = $this->AuthenticationModel->handle($data);
                View::assign('data', $data);
            }

            return View::fetch();
        }
    }

}