<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 * @version    $Id: Variables.php 40 2009-05-06 22:58:54Z gugakfugl $
 */

/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug_Plugin_Variables extends ZFDebug_Controller_Plugin_Debug_Plugin implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'variables';

    /**
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * Create ZFDebug_Controller_Plugin_Debug_Plugin_Variables
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Gets identifier for this plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        return ' Variables';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $this->_request = Zend_Controller_Front::getInstance()->getRequest();
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewVars = $viewRenderer->view->getVars();
        $vars = '';
        if ($this->_request->isPost())
        {
            $vars .= '<h4>$_POST</h4>'
                   . '<div id="ZFDebug_post">' . $this->_cleanData($this->_request->getPost()) . '</div>';
        }

        $vars .= '<h4>$_COOKIE</h4>'
               . '<div id="ZFDebug_cookie">' . $this->_cleanData($this->_request->getCookie()) . '</div>'
               . '<h4>Request</h4>'
               . '<div id="ZFDebug_requests">' . $this->_cleanData($this->_request->getParams()) . '</div>'
               . '<h4>View vars</h4>'
               . '<div id="ZFDebug_vars">' . $this->_cleanData($viewVars) . '</div>';
        return $vars;
    }

}