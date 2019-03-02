<?php

namespace phpList\plugin\LoginBlock;

use phpList\plugin\Common\WebblerListing;

class Controller extends \phpList\plugin\Common\Controller
{
    protected function actionDefault()
    {
        $config = getConfig('login_block_failed_logins');
        $logins = $config == '' ? [] : unserialize($config);

        if (count($logins) == 0) {
            echo 'There are no blocked IP addresses';
        }
        $w = new WebblerListing();
        $w->setElementHeading('IP address');

        foreach ($logins as $ip => $data) {
            $element = $ip;
            $w->addElement($element);
            $w->addColumn($element, 'Expiry', $data->periodEnd);
            $w->addColumn($element, 'Attempts', $data->attempts);
            $w->addColumn($element, 'Blocked', !$data->isExpired() && $data->reachedLimit() ? 'Yes' : 'No');
        }
        echo $w->display();
    }
}
