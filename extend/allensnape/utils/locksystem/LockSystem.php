<?php
namespace allensnape\utils\locksystem;

class LockSystem
{
    const LOCK_TYPE_DB = 'allensnape\utils\locksystem\SQLLock';
    const LOCK_TYPE_FILE = 'allensnape\utils\locksystem\FileLock';
    const LOCK_TYPE_MEMCACHE = 'allensnape\utils\locksystem\MemcacheLock';
    
    private $_lock = null;
    private static $_supportLocks = array(self::LOCK_TYPE_FILE, self::LOCK_TYPE_DB, self::LOCK_TYPE_MEMCACHE);  
    
    public function __construct($type, $options = array()) 
    {
        if(false == empty($type))
        {
            $this->createLock($type, $options);
        }
    }   

    public function createLock($type, $options=array())
    {
        if (false == in_array($type, self::$_supportLocks))
        {
            throw new \Exception("not support lock of ${type}");
        }
        $this->_lock = new $type($options);
    }
    
    public function getLock($key, $timeout = ILock::EXPIRE)
    {
        if (false == $this->_lock instanceof ILock)  
        {
            throw new \Exception('false == $this->_lock instanceof ILock');          
        }  
        $this->_lock->getLock($key, $timeout);   
    }
    
    public function releaseLock($key)
    {
        if (false == $this->_lock instanceof ILock)  
        {
            throw new \Exception('false == $this->_lock instanceof ILock');          
        }  
        $this->_lock->releaseLock($key);         
    }   
}