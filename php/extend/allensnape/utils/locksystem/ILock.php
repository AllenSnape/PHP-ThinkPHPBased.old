<?php
namespace allensnape\utils\locksystem;

interface ILock
{
    const EXPIRE = 5;
    public function getLock($key, $timeout=self::EXPIRE);
    public function releaseLock($key);
}