<?php
/**
 * @filesource modules/dms/controllers/init.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Dms\Init;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * Init Module.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\KBase
{
    /**
     * ฟังก์ชั่นเริ่มต้นการทำงานของโมดูลที่ติดตั้ง
     * และจัดการเมนูของโมดูล.
     *
     * @param Request                $request
     * @param \Index\Menu\Controller $menu
     * @param array                  $login
     */
    public static function execute(Request $request, $menu, $login)
    {
        // รายการเมนูย่อย
        $submenus = array();
        if (Login::checkPermission($login, 'can_download_dms')) {
            $submenus[] = array(
                'text' => '{LNG_List of} {LNG_Document}',
                'url' => 'index.php?module=dms',
            );
        }
        if (Login::checkPermission($login, 'can_upload_dms')) {
            $submenus[] = array(
                'text' => '{LNG_Upload} {LNG_Document}',
                'url' => 'index.php?module=dms-setup',
            );
        }
        $menu->addTopLvlMenu('dms', '{LNG_Document management system}', null, $submenus, 'module');
        // เมนูตั้งค่า
        $submenus = array();
        if (Login::checkPermission($login, 'can_manage_dms')) {
            foreach (Language::get('DMS_CATEGORIES') as $type => $text) {
                $submenus[] = array(
                    'text' => $text,
                    'url' => 'index.php?module=dms-categories&amp;type='.$type,
                );
            }
        }
        if (Login::checkPermission($login, 'can_config')) {
            $submenus[] = array(
                'text' => '{LNG_Settings}',
                'url' => 'index.php?module=dms-settings',
            );

        }
        if (!empty($submenus)) {
            $menu->add('settings', '{LNG_Document management system}', null, $submenus);
        }
    }

    /**
     * รายการ permission ของโมดูล.
     *
     * @param array $permissions
     *
     * @return array
     */
    public static function updatePermissions($permissions)
    {
        $permissions['can_manage_dms'] = '{LNG_Can set the module} ({LNG_Document management system})';
        $permissions['can_download_dms'] = '{LNG_Can view or download file}';
        $permissions['can_upload_dms'] = '{LNG_Can upload file} ({LNG_Document management system})';

        return $permissions;
    }
}
