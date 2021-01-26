<?php

namespace Model;
use App;
use CI_Model;
use CriticalException;
use Exception;

class Login_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * @param string $login
     * @param string $password
     * @return User_model
     * @throws Exception
     */
    public static function login(string $login, string $password): User_model
    {
        $user = User_model::get_by_email($login);

        if ($user->get_password() !== $password) {
            throw new Exception('Auth failed');
        }

        Login_model::start_session($user);

        return $user;
    }

    public static function logout()
    {
        App::get_ci()->session->unset_userdata('id');
    }

    public static function start_session(User_model $user)
    {
        $user->is_loaded(TRUE);

        App::get_ci()->session->set_userdata('id', $user->get_id());
    }


}
