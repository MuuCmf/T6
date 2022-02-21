<?php
// 这是系统自动生成的公共文件
function array_to_xml($arr) {
    $xml = "<xml>";
    foreach ($arr as $key => $val){
        if (is_numeric($val)){
            $xml.="<$key>$val</$key>";
        }
        else
            $xml.="<$key><![CDATA[$val]]></$key>";
    }
    $xml.="</xml>";
    return $xml;
}