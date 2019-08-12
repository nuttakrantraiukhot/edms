<?php
/**
 * @filesource Gcms/Category.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Gcms;

/**
 * คลาสสำหรับอ่านข้อมูลหมวดหมู่
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Category
{
    /**
     * @var array
     */
    private $datas = array();
    /**
     * @var array
     */
    protected $categories = array();

    /**
     * อ่านรายชื่อประเภทหมวดหมู่ที่สามารถใช้งานได้
     *
     * @return array
     */
    public function typies()
    {
        return $this->categories;
    }

    /**
     * อ่านรายชื่อหมวดหมู่จากฐานข้อมูลตามภาษาปัจจุบัน
     * สำหรับการแสดงผล
     *
     * @return static
     */
    public static function init()
    {
        $obj = new static();
        // Query
        $query = \Kotchasan\Model::createQuery()
            ->select('category_id', 'topic', 'type')
            ->from('category')
            ->where(array('type', $obj->typies()))
            ->order('category_id')
            ->cacheOn();
        foreach ($query->execute() as $item) {
            $obj->datas[$item->type][$item->category_id] = $item->topic;
        }

        return $obj;
    }

    /**
     * ลิสต์รายการหมวดหมู่
     * สำหรับใส่ลงใน select
     *
     * @param string $type
     *
     * @return array
     */
    public function toSelect($type)
    {
        return empty($this->datas[$type]) ? array() : $this->datas[$type];
    }

    /**
     * อ่านหมวดหมู่จาก $category_id
     * ไม่พบ คืนค่าว่าง
     *
     * @param string $type
     * @param int $category_id
     *
     * @return string
     */
    public function get($type, $category_id)
    {
        return empty($this->datas[$type][$category_id]) ? '' : $this->datas[$type][$category_id];
    }
}
