<?php

/**
 * An Adapter for using a Zend_Cache_Core-Instance as Query or Result-Cache
 * for Doctrine
 *
 * Offers an additional Prefix for its entries for usage within prefix-based
 * Cache-Structure (for example when using one Zend_Cache_Core for a complete
 * system)
 *
 * @uses       Doctrine_Cache_Interface
 * @author     Benjamin Steininger
 * @license    New BSD License
 * @category   Robo47
 * @package    Robo47_Cache
 * @todo       Add support for Tags to automatically tag all Entry made with a
 *             set of Tags provided by the constructor
 */
class Rhema_Cache_DoctrineAdapter implements Doctrine_Cache_Interface
{
    /**
     * @var Zend_Cache_Core
     */
    protected $_cache = null;

    /**
     * @param string
     */
    protected $_prefix = '';

    /**
     * @param Zend_Cache_Core $cache
     * @param string $prefix
     */
    public function __construct(Zend_Cache_Core $cache, $prefix = '')
    {
        $this->_cache = $cache;
        $this->_prefix = $prefix;
    }

    /**
     * Test if a cache is available for the given id and (if yes) return it
     * (false else)
     *
     * Note : return value is always "string" (unserialization is done by the
     * core not by the backend)
     *
     * @param string    $id cache id
     * @param boolean   $testCacheValidity  if set to false, the cache
     *                                      validity won't be tested
     * @return string  cached datas (or false)
     */
    public function fetch($id, $testCacheValidity = true)
    {
        $id = $this->_prefix . $id;
        return $this->_cache->load($id, $testCacheValidity);
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param string  $id cache id
     * @return mixed  false (a cache is not available) or "last modified"
     *                timestamp (int) of the available cache record
     */
    public function contains($id)
    {
        $id = $this->_prefix . $id;
        return $this->_cache->test($id);
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always saved as a string
     *
     * @param string $id           cache id
     * @param string $data         data to cache
     * @param int    $lifeTime     if != false, set a specific
     *                             lifetime for this cache record
     *                             (null => infinite lifeTime)
     *                             ($id, $data, $lifeTime = false, $saveKey = true)
     * @return boolean true if no problem
     */
    public function save($id, $data, $lifeTime = false, $saveKey = true)
    {
        $id = $this->_prefix . $id;
        return $this->_cache->save($data, $id, array(), $lifeTime);
    }

    /**
     * Remove a cache record
     *
     * @param string $id cache id
     * @return boolean true if no problem
     */
    public function delete($id)
    {
        $id = $this->_prefix . $id;
        return $this->_cache->remove($id);
    }
    
    public function deleteAll(){
    	return $this->_cache->clean(Zend_Cache::CLEANING_MODE_ALL);
    }
}

