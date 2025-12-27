<?php
// +----------------------------------------------------------------------
// | API路由配置
// +----------------------------------------------------------------------
// | 用于配置应用接口的路由规则
// +----------------------------------------------------------------------

use think\facade\Route;

// =====================================================
// 用户登录注册相关路由
// =====================================================

// 用户登录注册
Route::group('user', function () {
    Route::post('login', 'ucenter/Common/login');               // 登录
    Route::post('register', 'ucenter/Common/register');        // 注册
    Route::post('logout', 'ucenter/Common/logout');            // 登出
    Route::post('forgot', 'ucenter/Common/forgot');            // 忘记密码
    Route::post('reset', 'ucenter/Common/reset');              // 重置密码
    Route::get('info', 'ucenter/Common/info');                  // 获取用户信息
    Route::post('profile', 'ucenter/Common/profile');           // 更新用户资料
});

// =====================================================
// 实名认证相关路由
// =====================================================

// 实名认证
Route::group('authentication', function () {
    Route::post('edit', 'api/Authentication/edit');            // 提交/编辑认证
    Route::get('detail', 'api/Authentication/detail');        // 获取认证信息
});

// =====================================================
// 作者/服务人员相关路由
// =====================================================

Route::group('author', function () {
    Route::get('list', 'api/Author/lists');                    // 作者列表
    Route::get('detail/:id', 'api/Author/detail');             // 作者详情
    Route::post('follow', 'api/Author/follow');                // 关注作者
    Route::get('isfollow/:id', 'api/Author/isfollow');         // 检查是否关注
    Route::get('follow/list', 'api/Author/followList');        // 关注列表
});

// =====================================================
// 订单相关路由
// =====================================================

Route::group('order', function () {
    Route::post('create', 'api/Orders/create');                // 创建订单
    Route::get('list', 'api/Orders/list');                     // 订单列表
    Route::get('detail/:id', 'api/Orders/detail');             // 订单详情
    Route::post('cancel/:id', 'api/Orders/cancel');            // 取消订单
    Route::post('confirm/:id', 'api/Orders/confirm');          // 确认订单
    Route::post('pay/:id', 'api/Orders/pay');                  // 支付订单
});

// =====================================================
// 支付相关路由
// =====================================================

Route::group('pay', function () {
    Route::post('pay', 'api/Pay/pay');                         // 发起支付
    Route::get('query/:order_id', 'api/Pay/query');            // 查询支付状态
    Route::post('close/:order_id', 'api/Pay/close');            // 关闭支付
    Route::post('refund', 'api/Pay/refund');                   // 退款
    Route::post('callback', 'api/Pay/callback');               // 支付回调
    Route::get('v3cert', 'api/Pay/getV3cert');                 // 获取V3证书
});

// =====================================================
// 文件上传相关路由
// =====================================================

Route::group('file', function () {
    Route::post('upload', 'api/File/upload');                  // 文件上传
    Route::post('avatar', 'api/File/avatar');                  // 头像上传
    Route::post('ueditor', 'api/File/ueditor');                // UEditor上传
    Route::get('list', 'api/File/lists');                      // 文件列表
    Route::get('attachment', 'api/File/attachment');           // 获取附件
    Route::delete('delete', 'api/File/delete');                // 删除文件
});

// =====================================================
// 消息相关路由
// =====================================================

Route::group('message', function () {
    Route::get('type', 'api/Message/type');                    // 消息类型
    Route::get('list', 'api/Message/lists');                   // 消息列表
    Route::get('detail/:id', 'api/Message/detail');            // 消息详情
    Route::get('unread', 'api/Message/unread');                // 未读消息数
    Route::post('isread/:id', 'api/Message/isread');           // 标记已读
});

// =====================================================
// 评价相关路由
// =====================================================

Route::group('evaluate', function () {
    Route::get('statistical', 'api/Evaluate/statistical');     // 评价统计
    Route::get('list', 'api/Evaluate/lists');                  // 评价列表
    Route::post('edit', 'api/Evaluate/edit');                  // 添加/编辑评价
    Route::get('detail/:id', 'api/Evaluate/detail');           // 评价详情
});

// =====================================================
// 收藏相关路由
// =====================================================

Route::group('favorites', function () {
    Route::get('list', 'api/Favorites/lists');                // 收藏列表
    Route::get('count', 'api/Favorites/count');                // 收藏数量
    Route::post('add', 'api/Favorites/add');                   // 添加收藏
    Route::delete('delete/:id', 'api/Favorites/delete');      // 取消收藏
});

// =====================================================
// 历史记录相关路由
// =====================================================

Route::group('history', function () {
    Route::get('list', 'api/History/lists');                   // 历史记录列表
    Route::get('count', 'api/History/count');                  // 历史记录数量
    Route::post('add', 'api/History/add');                     // 添加历史记录
    Route::delete('clear', 'api/History/clear');               // 清空历史记录
});

// =====================================================
// 关键词相关路由
// =====================================================

Route::group('keywords', function () {
    Route::get('history', 'api/Keywords/history');             // 搜索历史
    Route::get('hot', 'api/Keywords/hot');                     // 热门搜索
    Route::post('add', 'api/Keywords/add');                   // 添加搜索记录
});

// =====================================================
// 系统配置相关路由
// =====================================================

Route::group('config', function () {
    Route::get('system', 'api/Config/system');                 // 系统配置
    Route::get('app', 'api/Config/app');                       // 应用配置
});

// =====================================================
// 地址管理相关路由
// =====================================================

Route::group('address', function () {
    Route::get('list', 'api/Address/lists');                  // 地址列表
    Route::get('detail/:id', 'api/Address/detail');           // 地址详情
    Route::post('edit', 'api/Address/edit');                  // 添加/编辑地址
    Route::delete('delete/:id', 'api/Address/delete');        // 删除地址
    Route::post('default/:id', 'api/Address/setDefault');     // 设置默认地址
});



// =====================================================
// 公告相关路由
// =====================================================

Route::group('announce', function () {
    Route::get('list', 'api/Announce/lists');                  // 公告列表
    Route::get('detail/:id', 'api/Announce/detail');          // 公告详情
});

// =====================================================
// 资金流水相关路由
// =====================================================

Route::group('capital', function () {
    Route::get('flow', 'api/Capital/flow');                    // 资金流水
    Route::get('balance', 'api/Capital/balance');              // 账户余额
});

// =====================================================
// VIP相关路由
// =====================================================

Route::group('vip', function () {
    Route::get('info', 'api/Vip/info');                       // VIP信息
    Route::get('card/list', 'api/VipCard/lists');              // VIP卡列表
    Route::get('card/detail/:id', 'api/VipCard/detail');       // VIP卡详情
    Route::get('card/product', 'api/VipCard/productAble');     // 可用VIP产品
    Route::get('card/able', 'api/VipCard/userAble');           // 用户可用VIP卡
});

// =====================================================
// 提现相关路由
// =====================================================

Route::group('withdraw', function () {
    Route::post('withdraw', 'api/Withdraw/withdraw');          // 申请提现
    Route::post('notify', 'api/Withdraw/notify');              // 提现回调通知
    Route::get('list', 'api/Withdraw/lists');                  // 提现记录
    Route::get('detail/:id', 'api/Withdraw/detail');           // 提现详情
});

// =====================================================
// 模块相关路由
// =====================================================

Route::group('module', function () {
    Route::get('list', 'api/Module/lists');                    // 模块列表
    Route::post('enable/:name', 'api/Module/enable');          // 启用模块
    Route::post('disable/:name', 'api/Module/disable');        // 禁用模块
});

// =====================================================
// 二维码相关路由
// =====================================================

Route::group('qrcode', function () {
    Route::get('create', 'api/Qrcode/create');                 // 生成二维码
});

// =====================================================
// 反馈相关路由
// =====================================================

Route::group('feedback', function () {
    Route::post('add', 'api/Feedback/add');                    // 提交反馈
    Route::get('list', 'api/Feedback/lists');                  // 反馈列表
});

// =====================================================
// 定时任务相关路由
// =====================================================

Route::group('crontab', function () {
    Route::get('orders/cancel', 'api/Crontab/ordersCancel');    // 订单超时取消
    Route::get('orders/evaluate', 'api/Crontab/ordersEvaluate'); // 订单评价提醒
});

// =====================================================
// 视频相关路由
// =====================================================

Route::group('vod', function () {
    Route::get('sign', 'api/Vod/sign');                        // 视频签名
});

// =====================================================
// 验证码相关路由
// =====================================================

Route::group('verify', function () {
    Route::post('send', 'api/Verify/send');                    // 发送验证码
    Route::post('check', 'api/Verify/check');                  // 验证验证码
});

// =====================================================
// 分享相关路由
// =====================================================

Route::group('share', function () {
    Route::get('list', 'api/Share/lists');                     // 分享列表
    Route::get('count', 'api/Share/count');                    // 分享统计
});

// =====================================================
// 积分相关路由
// =====================================================

Route::group('score', function () {
    Route::get('log', 'api/Score/log');                        // 积分记录
    Route::get('balance', 'api/Score/balance');                // 积分余额
});

