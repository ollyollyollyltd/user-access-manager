<?php
/**
 * AdminSettingsController.php
 *
 * The AdminSettingsController class file.
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

use UserAccessManager\Config\Config;
use UserAccessManager\Config\ConfigParameter;
use UserAccessManager\FileHandler\FileHandler;
use UserAccessManager\ObjectHandler\ObjectHandler;
use UserAccessManager\Wrapper\Php;
use UserAccessManager\Wrapper\Wordpress;

class AdminSettingsController extends Controller
{
    /**
     * @var ObjectHandler
     */
    protected $ObjectHandler;

    /**
     * @var FileHandler
     */
    protected $FileHandler;

    /**
     * @var string
     */
    protected $sTemplate = 'AdminSettings.php';

    /**
     * AdminSettingsController constructor.
     *
     * @param Php           $Php
     * @param Wordpress     $Wordpress
     * @param Config        $Config
     * @param ObjectHandler $ObjectHandler
     * @param FileHandler   $FileHandler
     */
    public function __construct(
        Php $Php,
        Wordpress $Wordpress,
        Config $Config,
        ObjectHandler $ObjectHandler,
        FileHandler $FileHandler
    ) {
        parent::__construct($Php, $Wordpress, $Config);
        $this->ObjectHandler = $ObjectHandler;
        $this->FileHandler = $FileHandler;
    }

    /**
     * Returns true if the server is a nginx server.
     *
     * @return bool
     */
    public function isNginx()
    {
        return $this->Wordpress->isNginx();
    }

    /**
     * Returns the pages.
     *
     * @return array
     */
    public function getPages()
    {
        $aPages = $this->Wordpress->getPages('sort_column=menu_order');
        return is_array($aPages) !== false ? $aPages : [];
    }

    /**
     * Returns the config parameters.
     *
     * @return \UserAccessManager\Config\ConfigParameter[]
     */
    public function getConfigParameters()
    {
        return $this->Config->getConfigParameters();
    }

    /**
     * Returns the post types as object.
     *
     * @return \WP_Post_Type[]
     */
    protected function getPostTypes()
    {
        return $this->Wordpress->getPostTypes(['public' => true], 'objects');
    }

    /**
     * Returns the taxonomies as objects.
     *
     * @return \WP_Taxonomy[]
     */
    protected function getTaxonomies()
    {
        return $this->Wordpress->getTaxonomies(['public' => true], 'objects');
    }

    /**
     * Returns the grouped config parameters.
     *
     * @return array
     */
    public function getGroupedConfigParameters()
    {
        $aConfigParameters = $this->Config->getConfigParameters();

        $aGroupedConfigParameters = [];
        $aPostTypes = $this->getPostTypes();

        foreach ($aPostTypes as $sPostType => $PostType) {
            if ($sPostType === ObjectHandler::ATTACHMENT_OBJECT_TYPE) {
                continue;
            }

            $aGroupedConfigParameters[$sPostType] = [
                $aConfigParameters["hide_{$sPostType}"],
                $aConfigParameters["hide_{$sPostType}_title"],
                $aConfigParameters["{$sPostType}_title"],
                $aConfigParameters["{$sPostType}_content"],
                $aConfigParameters["hide_{$sPostType}_comment"],
                $aConfigParameters["{$sPostType}_comment_content"],
                $aConfigParameters["{$sPostType}_comments_locked"]
            ];

            if ($sPostType === 'post') {
                $aGroupedConfigParameters[$sPostType][] = $aConfigParameters["show_{$sPostType}_content_before_more"];
            }
        }

        $aTaxonomies = $this->getTaxonomies();

        foreach ($aTaxonomies as $sTaxonomy => $Taxonomy) {
            $aGroupedConfigParameters[$sTaxonomy][] = $aConfigParameters["hide_empty_{$sTaxonomy}"];
        }

        $aGroupedConfigParameters['file'] = [
            $aConfigParameters['lock_file'],
            $aConfigParameters['download_type']
        ];

        $aGroupedConfigParameters['author'] = [
            $aConfigParameters['authors_has_access_to_own'],
            $aConfigParameters['authors_can_add_posts_to_groups'],
            $aConfigParameters['full_access_role'],
        ];

        $aGroupedConfigParameters['other'] = [
            $aConfigParameters['lock_recursive'],
            $aConfigParameters['protect_feed'],
            $aConfigParameters['redirect'],
            $aConfigParameters['blog_admin_hint'],
            $aConfigParameters['blog_admin_hint_text'],
        ];

        if ($this->Config->isPermalinksActive() === true) {
            $aGroupedConfigParameters['file'][] = $aConfigParameters['lock_file_types'];
            $aGroupedConfigParameters['file'][] = $aConfigParameters['file_pass_type'];
        }

        return $aGroupedConfigParameters;
    }

    /**
     * Update settings action.
     */
    public function updateSettingsAction()
    {
        $this->verifyNonce('uamUpdateSettings');

        $aNewConfigParameters = $this->getRequestParameter('config_parameters');
        $aNewConfigParameters = array_map(
            function ($sEntry) {
                return htmlentities(str_replace('\\', '', $sEntry));
            },
            $aNewConfigParameters
        );
        $this->Config->setConfigParameters($aNewConfigParameters);

        if ($this->Config->lockFile() === false) {
            $this->FileHandler->deleteFileProtection();
        } else {
            $this->FileHandler->createFileProtection();
        }

        $this->Wordpress->doAction('uam_update_options', $this->Config);
        $this->setUpdateMessage(TXT_UAM_UPDATE_SETTINGS);
    }

    /**
     * Checks if the group is a post type.
     *
     * @param string $sGroupKey
     *
     * @return bool
     */
    public function isPostTypeGroup($sGroupKey)
    {
        $aPostTypes = $this->getPostTypes();

        return isset($aPostTypes[$sGroupKey]);
    }

    /**
     * Returns the right translation string.
     *
     * @param string $sGroupKey
     * @param string $sIdent
     * @param bool   $blDescription
     *
     * @return mixed|string
     */
    protected function getObjectText($sGroupKey, $sIdent, $blDescription = false)
    {
        $aObjects = $this->getPostTypes() + $this->getTaxonomies();
        $sIdent .= ($blDescription === true) ? '_DESC' : '';

        if (isset($aObjects[$sGroupKey]) === true) {
            $sIdent = str_replace(strtoupper($sGroupKey), 'OBJECT', $sIdent);
            $sText = constant($sIdent);
            $iCount = substr_count($sText, '%s');
            $aArguments = array_fill(0, $iCount, $aObjects[$sGroupKey]->labels->name);
            return vsprintf($sText, $aArguments);
        }

        return constant($sIdent);
    }

    /**
     * @param string $sGroupKey
     * @param bool   $blDescription
     *
     * @return string
     */
    public function getSectionText($sGroupKey, $blDescription = false)
    {
        return $this->getObjectText(
            $sGroupKey,
            'TXT_UAM_'.strtoupper($sGroupKey).'_SETTING',
            $blDescription
        );
    }

    /**
     * Returns the label for the parameter.
     *
     * @param string          $sGroupKey
     * @param ConfigParameter $ConfigParameter
     * @param bool            $blDescription
     *
     * @return string
     */
    public function getParameterText($sGroupKey, ConfigParameter $ConfigParameter, $blDescription = false)
    {
        $sIdent = 'TXT_UAM_'.strtoupper($ConfigParameter->getId());

        return $this->getObjectText(
            $sGroupKey,
            $sIdent,
            $blDescription
        );
    }
}
