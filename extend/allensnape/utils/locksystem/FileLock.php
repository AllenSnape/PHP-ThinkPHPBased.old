<?php
namespace allensnape\utils\locksystem;

class FileLock implements ILock
{
    private $_fp;
    private $_single;

    public function __construct($options)
    {
        if (isset($options['path']) && is_dir($options['path']))
        {
            $this->_lockPath = $options['path'].'/';
        }
        else
        {
            $this->_lockPath = '/tmp/';
        }
       
        $this->_single = isset($options['single'])?$options['single']:false;
    }

    public function getLock($key, $timeout=self::EXPIRE)
    {
        $startTime = Timer::getTimeStamp();

        $file = md5(__FILE__.$key);
        $this->fp = fopen($this->_lockPath.$file.'.lock', "w+");
        if (true || $this->_single)
        {
            $op = LOCK_EX + LOCK_NB;
        }
        else
        {
            $op = LOCK_EX;
        }
        if (false == flock($this->fp, $op, $a))
        {
            throw new \Exception('failed');
        }
       
	    return true;
    }

    public function releaseLock($key)
    {
        flock($this->fp, LOCK_UN);
        fclose($this->fp);
    }
}