<?php
/**
 * Debug panel for 
 *
 */
 
class Rhema_Plugin_ZFDebug_PageInfo extends ZFDebug_Controller_Plugin_Debug_Plugin 
implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'page_info';
    

    /**
     * Setup
     *
     * @param array $options
     */
    public function __construct($options)
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
        return "SVN and PageInfo";
    }
 
    
    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $request = Zend_Controller_Front::getInstance()->getRequest();
        
       
        $ret = sprintf(
        	'Controller: %s<br>Action: %s<br>Route Name: %s<br>Params: <pre>%s</pre>'
            .'Current Revision: %s',
            $request->getControllerName(),
            $request->getActionName(),
            $router->getCurrentRouteName(),
            print_r($request->getParams(), true),            
            Rhema_View_Helper_IncludeCss::getRevisionNumber() 
        );
        
        return $ret;
    }
    
    

}