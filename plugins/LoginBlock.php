<?php

class LoginBlock extends phplistPlugin
{
    public $name = 'Login Block Plugin';
    public $authors = 'Duncan Cameron';
    public $description = 'Blocks logins from an IP address after a number of failed attempts';
    public $authProvider = true;
    public $topMenuLinks = ['main' => ['category' => 'subscribers']];
    public $pageTitles = ['main' => 'Logins blocked'];

    private $auth;

    public function __construct()
    {
        $this->coderoot = __DIR__ . '/' . __CLASS__ . '/';
        $this->settings = [
            'login_block_limit' => [
                'description' => s('The number of failed login attempts to allow'),
                'type' => 'integer',
                'min' => 2,
                'max' => 10,
                'value' => 3,
                'allowempty' => false,
                'category' => 'Login blocking',
            ],
            'login_block_period' => [
                'description' => s('The number of minutes to block an IP address'),
                'type' => 'integer',
                'min' => 1,
                'max' => 1440,
                'value' => 60,
                'allowempty' => false,
                'category' => 'Login blocking',
            ],
        ];
        parent::__construct();
    }

    public function activate()
    {
        global $systemroot;

        require_once $systemroot . '/phpListAdminAuthentication.php';

        $this->auth = new phpListAdminAuthentication();
        parent::activate();
    }

    public function adminmenu()
    {
        return [];
    }

    public function validateLogin($login, $password)
    {
        $config = getConfig('login_block_failed_logins');

        if ($config === '') {
            $ipFailedLogins = [];
        } else {
            $ipFailedLogins = unserialize($config);

            if (false === $ipFailedLogins) {
                $ipFailedLogins = [];
            }
        }
        // remove any expired periods
        $ipFailedLogins = array_filter(
            $ipFailedLogins,
            function ($item) {
                return !$item->isExpired();
            }
        );
        $ip = getClientIP();

        if (isset($ipFailedLogins[$ip])) {
            $logins = $ipFailedLogins[$ip];
        } else {
            $logins = new phpList\plugin\LoginBlock\IpLogins();
        }

        if ($logins->reachedLimit()) {
            //~ return [0, s('Login blocked due to too many failed attempts')];
            echo s('Login blocked due to too many failed attempts');
            ob_flush();
            exit;
        }
        $authResult = $this->auth->validateLogin($login, $password);

        if ($authResult[0] > 0) {
            unset($ipFailedLogins[$ip]);
        } else {
            $logins->failedLogin();
            $ipFailedLogins[$ip] = $logins;
        }
        SaveConfig('login_block_failed_logins', serialize($ipFailedLogins));

        return $authResult;
    }

    public function getPassword($email)
    {
        return $this->auth->getPassword($email);
    }

    public function validateAccount($id)
    {
        return $this->auth->validateAccount($id);
    }

    public function adminName($id)
    {
        return $this->auth->adminName($id);
    }

    public function adminEmail($id)
    {
        return $this->auth->adminEmail($id);
    }

    public function adminIdForEmail($email)
    {
        return $this->auth->adminIdForEmail($email);
    }

    public function isSuperUser($id)
    {
        return $this->auth->isSuperUser($id);
    }

    public function listAdmins()
    {
        return $this->auth->listAdmins();
    }
}
