<?php

namespace app\common\logic;

use app\common\model\Module;

class Share extends Base
{
    /**
     * 格式化分享数据
     * 该函数用于格式化和补充分享数据中的必要信息。
     * 如果数据中的产品信息为空，则尝试从元数据中解析出产品信息，并设置图片属性。
     * 同时，将产品价格格式化为两位小数。
     * 最后，补充用户信息和时间属性。
     *
     * @param array $data 分享数据数组
     * @return array 格式化后的分享数据数组
     */
    public function formatData($data)
    {
        if (!empty($data)) {
            if (empty($data['products'])) {
                $data['metadata'] = $data['products'] = json_decode($data['metadata'], true);
                $data['products'] = $this->setImgAttr($data['products'], '1:1');
                if (isset($data['products']['price'])) {
                    $data['products']['price'] = sprintf("%.2f", $data['products']['price'] / 100);
                }
            }
            // info_id转字符串
            $data['info_id'] = (string)$data['info_id'];

            // 获取应用信息
            $data['app_info'] = (new Module())->getModule($data['app']);
            // 获取用户信息
            $data['user_info'] = query_user($data['uid'], ['nickname', 'avatar']);
            // 设置时间属性
            if (!empty($data['create_time'])) {
                $data['create_time_str'] = time_format($data['create_time']);
                $data['create_time_friendly_str'] = friendly_date($data['create_time']);
            }
            if (!empty($data['update_time'])) {
                $data['update_time_str'] = time_format($data['update_time']);
                $data['update_time_friendly_str'] = friendly_date($data['update_time']);
            }
        }

        return $data;
    }
}
