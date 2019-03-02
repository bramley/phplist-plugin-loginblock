<?php

namespace phpList\plugin\LoginBlock;

class IpLogins
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    public $periodEnd;
    public $attempts;

    public function __construct()
    {
        $this->periodEnd = date(self::DATE_FORMAT, time() + getConfig('login_block_period') * 60);
        $this->attempts = 0;
    }

    public function isExpired()
    {
        return date(self::DATE_FORMAT) > $this->periodEnd;
    }

    public function reachedLimit()
    {
        return $this->attempts >= getConfig('login_block_limit');
    }

    public function failedLogin()
    {
        ++$this->attempts;
    }
}
