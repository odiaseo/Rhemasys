<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 * @version    $Id: Cache.php 70 2009-05-15 12:01:16Z gugakfugl $
 */

/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class Rhema_Plugin_ZFDebug_Cache extends ZFDebug_Controller_Plugin_Debug_Plugin_Cache  
{
 
    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $panel = ''; 

        foreach ($this->_cacheBackends as $name => $backend) {
            $fillingPercentage = $backend->getFillingPercentage();
            $ids = $backend->getIds();
            
            # Print full class name, backends might be custom
            $panel .= '<h4>Cache '.$name.' ('.get_class($backend).')</h4>';
            $panel .= count($ids).' Entr'.(count($ids)>1?'ies':'y').'<br />'
                    . 'Filling Percentage: '.$backend->getFillingPercentage().'%<br />';
            
            $cacheSize = 0;
            foreach ($ids as $id)
            {
                # Calculate valid cache size
                $mem_pre = memory_get_usage();
                if ($cached = $backend->load($id)) {
                    $mem_post = memory_get_usage();
                    $cacheSize += $mem_post-$mem_pre;
                    unset($cached);
                }                
            }
            $panel .= 'Valid Cache Size: '.round($cacheSize/1024, 1). 'K';
        }
        return $panel;
    }
}