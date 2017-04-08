<?php
/**
 * AdminController.php
 *
 * The AdminController class file.
 *
 * PHP versions 5
 *
 * @author    Alexander Schneider <alexanderschneider85@gmail.com>
 * @copyright 2008-2017 Alexander Schneider
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 * @version   SVN: $Id$
 * @link      http://wordpress.org/extend/plugins/user-access-manager/
 */
namespace UserAccessManager\Controller;

use UserAccessManager\AccessHandler\AccessHandler;
use UserAccessManager\Config\Config;
use UserAccessManager\FileHandler\FileHandler;
use UserAccessManager\UserAccessManager;
use UserAccessManager\Wrapper\Php;
use UserAccessManager\Wrapper\Wordpress;

/**
 * Class AdminController
 *
 * @package UserAccessManager\Controller
 */
class AdminController extends Controller
{
    const HANDLE_STYLE_ADMIN = 'UserAccessManagerAdmin';
    const HANDLE_SCRIPT_ADMIN = 'UserAccessManagerFunctions';

    /**
     * @var AccessHandler
     */
    protected $AccessHandler;

    /**
     * @var FileHandler
     */
    protected $FileHandler;

    /**
     * @var string
     */
    protected $sNotice = '';

    /**
     * AdminController constructor.
     *
     *
     * @param Php           $Php
     * @param Wordpress     $Wordpress
     * @param Config        $Config
     * @param AccessHandler $AccessHandler
     * @param FileHandler   $FileHandler
     */
    public function __construct(
        Php $Php,
        Wordpress $Wordpress,
        Config $Config,
        AccessHandler $AccessHandler,
        FileHandler $FileHandler
    ) {
        parent::__construct($Php, $Wordpress, $Config);
        $this->Config = $Config;
        $this->AccessHandler = $AccessHandler;
        $this->FileHandler = $FileHandler;
    }

    /**
     * Shows the fopen notice.
     */
    public function showFOpenNotice()
    {
        $this->sNotice = TXT_UAM_FOPEN_WITHOUT_SAVE_MODE_OFF;
        echo $this->getIncludeContents('AdminNotice.php');
    }

    /**
     * Shows the database notice.
     */
    public function showDatabaseNotice()
    {
        $this->sNotice = sprintf(TXT_UAM_NEED_DATABASE_UPDATE, 'admin.php?page=uam_setup');
        echo $this->getIncludeContents('AdminNotice.php');
    }

    /**
     * Returns the set notice.
     *
     * @return string
     */
    public function getNotice()
    {
        return $this->sNotice;
    }

    /**
     * Register styles and scripts with handle for admin panel.
     */
    protected function registerStylesAndScripts()
    {
        $sUrlPath = $this->Config->getUrlPath();

        $this->Wordpress->registerStyle(
            self::HANDLE_STYLE_ADMIN,
            $sUrlPath.'assets/css/uamAdmin.css',
            [],
            UserAccessManager::VERSION,
            'screen'
        );

        $this->Wordpress->registerScript(
            self::HANDLE_SCRIPT_ADMIN,
            $sUrlPath.'assets/js/functions.js',
            ['jquery'],
            UserAccessManager::VERSION
        );
    }

    /**
     * The function for the admin_enqueue_scripts action for styles and scripts.
     *
     * @param string $sHook
     */
    public function enqueueStylesAndScripts($sHook)
    {
        $this->registerStylesAndScripts();
        $this->Wordpress->enqueueStyle(self::HANDLE_STYLE_ADMIN);

        if ($sHook === 'uam_page_uam_settings' || $sHook === 'uam_page_uam_setup') {
            $this->Wordpress->enqueueScript(self::HANDLE_SCRIPT_ADMIN);
        }
    }


    /**
     * The function for the wp_dashboard_setup action.
     * Removes widgets to which a user should not have access.
     */
    public function setupAdminDashboard()
    {
        if ($this->AccessHandler->checkUserAccess('manage_user_groups') === false) {
            $aMetaBoxes = $this->Wordpress->getMetaBoxes();
            unset($aMetaBoxes['dashboard']['normal']['core']['dashboard_recent_comments']);
            $this->Wordpress->setMetaBoxes($aMetaBoxes);
        }
    }

    /**
     * The function for the update_option_permalink_structure action.
     */
    public function updatePermalink()
    {
        $this->FileHandler->createFileProtection();
    }
}
