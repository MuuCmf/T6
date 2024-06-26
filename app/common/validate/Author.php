<?php

namespace app\common\validate;

use think\Validate;
use app\common\model\Author as AuthorModel;

/**
 * 创作者验证器
 */
class Author extends Validate
{
    protected $rule = [
        'name'  =>  'require',
        'description'  =>  'require',
        'cover' => 'require',
        'professional'  =>  'require',
        'group_id' => 'require|gt:0',
        'uid' => 'require|checkUidUnique',
        'content'  =>  'require',
    ];

    protected $message  =   [
        'name.require' =>  '真实姓名不能为空',
        'description.require' => '简短描述不能为空',
        'cover.require' => '封面图还未上传',
        'professional.require' => '职称不能为空',
        'group_id.require' => '创作者类型未选择',
        'group_id.gt' => '创作者类型未选择',
        'uid.checkUidUnique' => '该用户已经绑定了其他创作者',
        'content.require' => '详情描述不能为空',
    ];

    protected $scene = [
        'edit'   =>  ['name', 'description', 'cover', 'professional', 'group_id', 'uid', 'content'],
    ];

    protected function checkUidUnique($value, $rule, $data)
    {
        if ($value > 0) {
            $AuthorModel = new AuthorModel();
            $map = [];
            $map[] = ['uid', '=', $value];
            $map[] = ['status', 'in', [0, 1, -1, -2]];
            if (!empty($data['id'])) {
                $map[] = ['id', '<>', $data['id']];
            }

            $count = $AuthorModel->where($map)->count();

            if ($count > 0) {
                return false;
            }
        }

        return true;
    }
}
