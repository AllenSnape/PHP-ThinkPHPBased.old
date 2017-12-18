<?php
namespace allensnape\utils\locksystem;

class SQLLock implements ILock
{
    public function __construct($options)
    {
        $this->_db = new mysql(); 
    }

    public function getLock($key, $timeout=self::EXPIRE)
    {       
        $sql = "SELECT GET_LOCK('".$key."', '".$timeout."')";
        $res =  $this->_db->query($sql);
        return $res;
    }

    public function releaseLock($key)
    {
        $sql = "SELECT RELEASE_LOCK('".$key."')";
        return $this->_db->query($sql);
    }
}