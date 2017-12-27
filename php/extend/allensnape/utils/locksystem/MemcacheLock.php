<?php
namespace allensnape\utils\locksystem;

class MemcacheLock implements ILock
{

    var $memcache = null;

    public function __construct($options)
    {
        
        $this->memcache = self::isWindows() ? (new \Memcache()) : (new \Memcached());
        $this->memcache->addServer("127.0.0.1", 11211);
    }

    public function getLock($key, $timeout = self::EXPIRE)
    {
        $waitime = 20000;
        $totalWaitime = 0;
        $time = $timeout*1000000;
        while ($totalWaitime < $time && false == $this->memcache->set($key, 1, self::isWindows() ? 0 : $timeout)) {
            usleep($waitime);
            $totalWaitime += $waitime;
        }
        if ($totalWaitime >= $time) {
            throw new \Exception('can not get lock for waiting '.$timeout.'s.');
        }
    }

    public function releaseLock($key)
    {
        $this->memcache->delete($key);
    }

    /**
     * 是否是Windows平台
     */
    public static function isWindows(){
        return strstr(PHP_OS, 'WIN') ? true : false;
    }

}
