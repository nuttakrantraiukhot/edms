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
        header('Cache-Control: public, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Type: '.$file['mime']);
        header('Content-Length: '.$file['size']);
        if ($file['name'] != '') {
            header('Content-Disposition: attachment; filename='.$file['name']);
        }
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        readfile($file['file']);
        exit;
    }
}
header('HTTP/1.0 404 Not Found');
