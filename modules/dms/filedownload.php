<?php
/**
 * @filesource modules/dms/filedownload.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */
// session
@session_cache_limiter('none');
@session_start();
// datas
if (isset($_GET['id']) && isset($_SESSION[$_GET['id']])) {
    $file = $_SESSION[$_GET['id']];
    if (is_file($file['file'])) {
        // ดาวน์โหลดไฟล์
        header('Pragma: public');
        header('Expires: -1');
        header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
        header('Content-Disposition: attachment; filename='.$file['name']);
        header('Content-Type: '.$file['mime']);
        header('Content-Length: '.$file['size']);
        header('Accept-Ranges: bytes');
        readfile($file['file']);
        exit;
    }
}
header('HTTP/1.0 404 Not Found');
