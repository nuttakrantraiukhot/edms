<?php
/**
 * @filesource modules/dms/models/view.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Dms\View;

/**
 * โมเดลสำหรับอ่านเอกสาร.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * อ่านเอกสารที่ $id
     * ไม่พบ คืนค่า null.
     *
     * @param int   $id
     * @param array $login
     *
     * @return object
     */
    public static function get($id, $login)
    {
        return static::createQuery()
            ->from('dms A')
            ->where(array('A.id', $id))
            ->first('A.id', 'A.document_no', 'A.topic', 'A.member_id', 'A.create_date', 'A.detail');
    }

    /**
     * อ่านรายการไฟล์
     * และ ประวัติการดาวน์โหลดของคนที่ login
     *
     * @param int $id
     * @param array $login
     *
     * @return array
     */
    public static function files($id, $login)
    {
        return static::createQuery()
            ->select('F.topic', 'F.ext', 'D.downloads')
            ->from('dms_files F')
            ->join('dms_download D', 'LEFT', array(array('D.file_id', 'F.id'), array('D.member_id', $login['id'])))
            ->where(array('F.dms_id', $id))
            ->groupBy('F.id')
            ->cacheOn()
            ->execute();
    }
}