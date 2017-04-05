<?php
class AccountController extends Module {
    private $_ph;
    function __construct() {
        $this->_ph = new PersonsHelper();
        // Hook events to methods
        Modules::HookEvent('routes.pageload', $this, 'RedirectIfNotAuthenticated');
        Modules::HookEvent('routes.pageload', $this, 'CheckIfAbleToActAsOtherUser');

        // Add routes
        Routes::Get('account/login', $this, 'Login');
        Routes::Post('account/login', $this, 'LoginPost');

        Routes::Get('account/logout', $this, 'Logout');

        Routes::Post('account/register', $this, 'RegisterPost');

        Routes::Get('account/act-as-user', $this, 'ActAsOtherUser');
    }

    public function RedirectIfNotAuthenticated($route) {
        // Check foreach page of a user is authenticated
        if (isset($_SESSION['user']['id'])) {
            $user = DB::table('persons_person')->find($_SESSION['user']['id'], 'id');
            if($user === null){
                session_destroy();
                return $this->RedirectResult('/account/login?somethingwentwrong=1');
            }
            
            Module::$Data['User'] = DB::table('users_user')->find($_SESSION['user']['id'], 'person_id');
            return;
        }

        if (isset($route['route']) && ($route['route'] == 'account/login' || $route['route'] == '')) {
            return;
        }

        return $this->RedirectResult('/account/login');
    }

    public function Login() {
        if (isset($_SESSION['user']['id'])) {
            return $this->RedirectResult('/dashboard');
        }

        self::$Data['posturl'] = "/account/login";

        Modules::PushEvent('account.loginsuccess', null);
        return $this->OkResult('login', 'login');
    }

    public function LoginPost($data) {

        $users = (array) DB::table('users_user')->where('username', $data['username'])->get();

        if (count($users) !== 1) {
            return $this->BadRequestResult('usernotfound', 'login');
        }

        $user = (array) $users[0];
        
        if($this->_ph->CheckPassword($user['person_id'],$data['password'])){
            $_SESSION['user']['id'] = $user['person_id'];
            
            return $this->RedirectResult('/dashboard?loginsuccess=1');
        }
        
        return $this->BadRequestResult('loginfailed', 'login');
    }

    public function Logout() {
        session_destroy();
        return $this->RedirectResult('/account/login');
    }

    public function RegisterPost() {
        
    }

    public function CheckIfAbleToActAsOtherUser()
    {
        if (isset($_SESSION['user']['id'])){
            self::$Data['AbleToActAsOtherUser'] = Permissions::Check($_SESSION['user']['id'], 'account',
                'act-as-other-user');

        }
    }

    public function ActAsOtherUser($data){
        if (Permissions::Check($_SESSION['user']['id'], 'account', 'act-as-other-user'))
        {
            $_SESSION['user']['id'] = $data['id'];
        }
        //dump($_SESSION['user']['id']);
        return $this->RedirectResult($_SERVER['HTTP_REFERER']);
    }

    private function Encrypt($password) {
        $cost = 10;

        // Create a random salt
        $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

        // Prefix information about the hash so PHP knows how to verify it later.
        // "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
        $salt = sprintf("$2a$%02d$", $cost) . $salt;

        // Value:
        // $2a$10$eImiTXuWVxfM37uY4JANjQ==
        // Hash the password with the salt
        return crypt($password, $salt);
    }

    private function CompareHash($password, $hash) {
        // Hashing the password with its hash as the salt returns the same hash
        return hash_equals($hash, crypt($password, $hash));
    }

}