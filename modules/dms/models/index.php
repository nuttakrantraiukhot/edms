<?php
/**
 * @filesource modules/dms/models/index.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Dms\Index;

use Gcms\Login;
use Kotchasan\Database\Sql;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * โมเดลสำหรับแสดงรายการเอกสาร (index.php)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * Query ข้อมูลสำหรับส่งให้กับ DataTable
     *
     * @param array $params
     * @param array $login
     *
     * @return \Kotchasan\Database\QueryBuilder
     */
    public static function toDataTable($params, $login)
    {
        $where = array();
        foreach (Language::get('DMS_CATEGORIES') as $k => $label) {
            if (!empty($params[$k])) {
                $where[] = array('A.'.$k, $params[$k]);
            }
        }
        if (!empty($params['from'])) {
            $where[] = array('A.create_date', '>=', $params['from']);
        }
        if (!empty($params['to'])) {
            $where[] = array('A.create_date', '<=', $params['to']);
        }
        if (!empty($params['search'])) {
            $where[] = Sql::create("(A.`document_no` LIKE '%$params[search]%' OR A.`topic` LIKE '%$params[search]%' OR F.`topic` LIKE '%$params[search]%')");
        }

        return static::createQuery()
            ->select('F.id', 'F.dms_id', 'A.create_date', 'A.document_no', 'A.topic', 'F.topic file_name', 'F.ext', 'A.department', 'A.cabinet', 'D.downloads')
            ->from('dms_files F')
            ->join('dms A', 'INNER', array('A.id', 'F.dms_id'))
            ->join('dms_download D', 'LEFT', array(array('D.file_id', 'F.id'), array('D.member_id', $login['id'])))
            ->where($where);
    }

    /**
     * รับค่าจาก action
     *
     * @param Request $request
     */
    public function action(Request $request)
    {
        $ret = array();
        // session, referer, member, สามารถดูหรือดาวน์โหลดเอกสารได้
        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::checkPermission($login, 'can_download_dms')) {
                // ค่าที่ส่งมา
                $action = $request->post('action')->toString();
                if ($action == 'detail') {
                    // แสดงรายละเอียดของเอกสาร
                    $document = \Dms\View\Model::get($request->post('id')->toInt(), $login);
                    if ($document) {
                        $ret['modal'] = Language::trans(createClass('Dms\View\View')->render($document, $login));
                    }
                } elseif ($action == 'download') {
                    // อ่านรายการที่เลือก
                    $result = $this->db()->createQuery()
                        ->from('dms_files F')
                        ->join('dms_download D', 'LEFT', array(array('D.file_id', 'F.id'), array('D.member_id', (int) $login['id'])))
                        ->where(array('F.id', $request->post('id')->toInt()))
                        ->groupBy('F.id')
                        ->first('D.id', 'F.id file_id', 'F.dms_id', 'D.downloads', 'F.size', 'F.name', 'F.file', 'F.ext');
                    if ($result) {
                        // ไฟล์
                        $file = ROOT_PATH.DATA_FOLDER.$result->file;
                        if (is_file($file)) {
                            // สามารถดาวน์โหลดได้
                            $save = array(
                                'downloads' => $result->downloads + 1,
                                'dms_id' => $result->dms_id,
                                'file_id' => $result->file_id,
                                'member_id' => $login['id'],
                                'last_update' => date('Y-m-d H:i:s'),
                            );
                            if (empty($result->id)) {
                                $this->db()->insert($this->getTableName('dms_download'), $save);
                            } else {
                                $this->db()->update($this->getTableName('dms_download'), $result->id, $save);
                            }
                            // id สำหรบไฟล์ดาวน์โหลด
                            $id = uniqid();
                            // บันทึกรายละเอียดการดาวน์โหลดลง SESSION
                            $_SESSION[$id] = array(
                                'file' => $file,
                                'name' => $result->name.'.'.$result->ext,
                                'size' => $result->size,
                                'mime' => self::$cfg->dms_download_action == 1 ? \Kotchasan\Mime::get($result->ext) : 'application/octet-stream',
                            );
                            // คืนค่า
                            $ret['location'] = WEB_URL.'modules/dms/filedownload.php?id='.$id;
                        } else {
                            // ไม่พบไฟล์
                            $ret['alert'] = Language::get('File not found');
                        }
                    }
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่าเป็น JSON
        echo json_encode($ret);
    }
}
