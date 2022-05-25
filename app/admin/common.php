<?php
/**
 * 后台公共文件
 * 主要定义后台公共函数库
 */

function rmdirr($dirname) {
    if (!file_exists($dirname)) {
        return false;
    }
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }
    $dir = dir($dirname);
    if($dir){
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
        }
    }
    $dir->close();
    return rmdir($dirname);
}