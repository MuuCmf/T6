<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: MakeZip.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/1/13
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\admin\lib;
use think\Exception;

class MakeZip{
    /**
     * PHP ZipArchive压缩文件夹，实现将目录及子目录中的所有文件压缩为zip文件
     * @author 吴先成 wuxiancheng.cn 高阶代码 原创发布
     * @param string $folderPath 要压缩的目录路径 绝对路径和相对路径都可以
     * @param string $zipAs 压缩包文件的文件名，可以带路径，不能为空
     * @return bool 成功时返回true，否则返回false
     */
    function zipFolder($folderPath, $zipAs){
        if(!is_scalar($folderPath) || !is_scalar($zipAs)){
            return false;
        }
        $folderPath = (string)$folderPath;
        $folderPath = str_replace('\\', '/', $folderPath);
        $zipAs = (string)$zipAs;
        if($zipAs === ''){
            return false;
        }
        try{
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folderPath, \RecursiveDirectoryIterator::UNIX_PATHS|\RecursiveDirectoryIterator::CURRENT_AS_SELF|\RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST, \RecursiveIteratorIterator::CATCH_GET_CHILD);
            $zipObject   = new \ZipArchive();
            $errorCode = $zipObject->open($zipAs, \ZipArchive::CREATE|\ZipArchive::OVERWRITE);
            if($errorCode !== true){
                return false;
            }
            foreach($files as $file){
                $subPath = $file->getSubPathname();
                if($file->isDir()){
                    if ($subPath == 'runtime') continue;
                    $subPath = rtrim($subPath, '/') . '/';
                    $zipObject->addEmptyDir($subPath);
                }else{
                    $zipObject->addFile($file->getPathname());
                }
            }
            if($zipObject->close()){
                return true;
            }
        }catch(Exception $e){
        }
        return false;
    }
}