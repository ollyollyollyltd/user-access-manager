<?php
/**
 * Controller.php
 *
 * The Controller class file.
 *
 * PHP versions 5
 *
 * @author    Alexander Schneider <alexanderschneider85@gmail.com>
 * @copyright 2008-2017 Alexander Schneider
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 * @version   SVN: $id$
 * @link      http://wordpress.org/extend/plugins/user-access-manager/
 */
namespace UserAccessManager\Controller;

use UserAccessManager\Config\MainConfig;
use UserAccessManager\Wrapper\Php;
use UserAccessManager\Wrapper\Wordpress;

/**
 * Class Controller
 *
 * @package UserAccessManager\Controller
 */
abstract class Controller
{
    use BaseControllerTrait {
        render as traitRender;
    }

    const ACTION_PARAMETER = 'uam_action';
    const ACTION_SUFFIX = 'Action';

    /**
     * @var Php
     */
    protected $php;

    /**
     * @var Wordpress
     */
    protected $wordpress;

    /**
     * @var MainConfig
     */
    protected $config;

    /**
     * @var string
     */
    protected $updateMessage = null;

    /**
     * Controller constructor.
     *
     * @param Php        $php
     * @param Wordpress  $wordpress
     * @param MainConfig $config
     */
    public function __construct(Php $php, Wordpress $wordpress, MainConfig $config)
    {
        $this->php = $php;
        $this->wordpress = $wordpress;
        $this->config = $config;
    }

    /**
     * Returns the nonce field.
     *
     * @param string $name
     *
     * @return string
     */
    public function createNonceField($name)
    {
        return $this->wordpress->getNonceField($name, $name.'Nonce');
    }

    /**
     * Returns the nonce.
     *
     * @param string $name
     *
     * @return string
     */
    public function getNonce($name)
    {
        return $this->wordpress->createNonce($name);
    }

    /**
     * Verifies the nonce and terminates the application if the nonce is wrong.
     *
     * @param string $name
     */
    protected function verifyNonce($name)
    {
        $nonce = $this->getRequestParameter($name.'Nonce');

        if ($this->wordpress->verifyNonce($nonce, $name) === false) {
            $this->wordpress->wpDie(TXT_UAM_NONCE_FAILURE_MESSAGE, TXT_UAM_NONCE_FAILURE_TITLE, ['response' => 401]);
        }
    }

    /**
     * Sets the update message.
     *
     * @param $message
     */
    protected function setUpdateMessage($message)
    {
        $this->updateMessage = $message;
    }

    /**
     * Returns the update message.
     *
     * @return string
     */
    public function getUpdateMessage()
    {
        return $this->updateMessage;
    }

    /**
     * Returns true if a update message is set.
     *
     * @return bool
     */
    public function hasUpdateMessage()
    {
        return $this->updateMessage !== null;
    }

    /**
     * Process the action.
     */
    protected function processAction()
    {
        $postAction = $this->getRequestParameter(self::ACTION_PARAMETER);
        $postActionSplit = explode('_', $postAction);
        $postAction = array_shift($postActionSplit);
        $postAction .= implode('', array_map('ucfirst', $postActionSplit));
        $actionMethod = $postAction.self::ACTION_SUFFIX;

        if (method_exists($this, $actionMethod) === true) {
            $this->{$actionMethod}();
        }
    }

    /**
     * Renders the given template
     */
    public function render()
    {
        $this->processAction();
        $this->traitRender();
    }
}
