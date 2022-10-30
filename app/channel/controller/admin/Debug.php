<?php
namespace app\channel\controller\admin;

use app\admin\controller\Admin as MuuAdmin;
use app\channel\facade\bytedance\MiniProgram as MiniProgramServer;
use app\channel\facade\wechat\MiniProgram;
use muucmf\Rsa;
use app\channel\facade\baidu\MiniProgram as BaiduMiniProgramServer;

class Debug extends MuuAdmin
{
    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        // $a = MiniProgramServer::getAccessToken();
        // dump($a);exit;
        $rsaPriKeyStr = 'MIICXwIBAAKBgQCznDLbXalZwwXmgI/G5PPWLbT8NGO4cJQPDOO08wQifA2FKrh7U7hhsqztZnI0EhWqkXUhjYCyDofQXmtr0wXdUbcvTIDS3lnxSKuAss2/aX3G82fH9jdD4kXP3XptsaO/mYqPmX7tFe1MgfCElPBNVjjae8rkC85TN/30bHwYkQIDAQABAoGBAKf2b1zTVvaZOWBYY5wlKZ3mOnUL7SFjLiJw9FSYWgqBpgcdb03teeTrOWn8vtnQ+6/5vOa2tF5O1lVWpvA7dCP14q2dfrBn8R8NYlPDeYlA7Muet2T1gYhaeuDpPtE5NidW8ViAxj34aRD/N7GO4QBkIQ7n0ypkcUarOc4hbYS9AkEA3jWOWnxssjcAGS1slB4K4JE0X4sj1TiZDDDlJF0ZXu2kOIV6k2ICHNzUgNrnTHukq399Y464XMMxj3IWeIkLpwJBAM7sSs8wAQJwVKkJBiPaJTaqNqApzSGj4z9dHrxGCHArr1jYvZ0xxVbG1n8nYWGoZRgKToaJ4UImEJOFbKpp4QcCQQCrOLNXMBcFf/H4dJL80uVowxqLIIjc7H6p8ScvzPkWt6DZ2Khp1pRwLw0juQmPWpq5d0RkKX4QJGwU70E3YcpzAkEAn0jz+XO5gzgXY5vHtzeI6AC1Vit3dgrjtvYm38WFX1uxelI1/FjA0SD1IyKcawGm+I+OjTB8T2Bf6D+QO8qPZwJBAIDdnxfo6fonf3lmSuVJh92fZoN2lqKRBgUFcTkjj4TqirdcqBA8lpLxKNwAj2+57usT0iTNrrFTnVjGcyU7oj4=';

        $assocArr = [
            'appKey' => 'MMNhiT',
            'dealId' => '3993162964',
            'tpOrderId' => '202210297654920221',
            'totalAmount' => '11300'
        ];

        // 参数按字典顺序排序
        ksort($assocArr); 

        $parts = [];
        foreach ($assocArr as $k => $v) {
            $parts[] = $k . '=' . $v;
        }
        $str = implode('&', $parts);
        //$str = 'appKey=MMNhiT&dealId=3993162964&totalAmount=11300&tpOrderId=202210297654920221';

        $rsaSign = (new Rsa(null, $rsaPriKeyStr))->sign($str);

        dump($rsaSign);


        // 
        $rsaPublicKeyStr = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCznDLbXalZwwXmgI/G5PPWLbT8NGO4cJQPDOO08wQifA2FKrh7U7hhsqztZnI0EhWqkXUhjYCyDofQXmtr0wXdUbcvTIDS3lnxSKuAss2/aX3G82fH9jdD4kXP3XptsaO/mYqPmX7tFe1MgfCElPBNVjjae8rkC85TN/30bHwYkQIDAQAB';

        $assocArr = [
            'appKey' => 'MMNhiT',
            'dealId' => '3993162964',
            'tpOrderId' => '202210297654920221',
            'totalAmount' => '11300',
            'rsaSign' => $rsaSign
        ];

        $sign = $assocArr['rsaSign'];
        unset($assocArr['rsaSign']);

        ksort($assocArr); 

        $parts = array();
        foreach ($assocArr as $k => $v) {
            $parts[] = $k . '=' . $v;
        }
        $str = implode('&', $parts);

        $res = (new Rsa($rsaPublicKeyStr, null))->verify($str, $sign);

        dump($res);

    }

    public function callback()
    {
        $notify_data = 'unitPrice=10&orderId=93969718697904&payTime=1667056401&dealId=3993162964&tpOrderId=202210299506610976&count=1&totalMoney=10&hbBalanceMoney=0&userId=1510248121&promoMoney=0&promoDetail=&hbMoney=0&giftCardMoney=0&payMoney=10&payType=1087&returnData=&partnerId=6000001&rsaSign=oUUM%2BPSb73EosdtaRKiTyrGC1hGmcUBfETIWE61bDvupi8C6oDxeCvKXWIP8J3U6hdqqtNIDEvUK67CHOx2lmL4AU5s8hUtEFAki0szBCSMBXY3ad9d9KrGmHo3SlzwgNS1fG4%2FojfLmcNBF26NS%2FIminFsSHDoQdUoqSjr3vEQ%3D&status=2';

        parse_str($notify_data, $content);
        dump($content);
        $sign = BaiduMiniProgramServer::checkSign($content);

        dump($sign);


    }

}