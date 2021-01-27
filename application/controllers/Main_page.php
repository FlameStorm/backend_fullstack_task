<?php

use Model\Comment_model;
use Model\Login_model;
use Model\Post_model;
use Model\User_model;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 10.11.2018
 * Time: 21:36
 */
class Main_page extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (is_prod())
        {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function index()
    {
        $user = User_model::get_user();

        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    public function get_all_posts()
    {
        $posts =  Post_model::preparation(Post_model::get_all(), 'main_page');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_post($post_id)
    {
        // or can be $this->input->get('post_id') , but better for GET REQUEST USE THIS
        $post_id = intval($post_id);

        if (empty($post_id)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }


        $posts =  Post_model::preparation($post, 'full_info');
        return $this->response_success(['post' => $posts]);
    }


    public function comment($action)
    {
        if ($action === 'add') {
            return $this->comment_add();
        }

        if ($action === 'delete') {
            return $this->comment_delete();
        }

        if ($action === 'add_sub') {
            return $this->comment_sub_add();
        }


        // Default - read comment

        if (!$comment_id = intval($action)) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        // read comments for guests too.
        //if (!User_model::is_logged()){
        //    return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        //}

        try {
            $comment = new Comment_model($comment_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        $result = Comment_model::preparation($comment, 'full_info');
        return $this->response_success(['comment' => $result]);
    }

    protected function comment_add()
    {
        // It must be :
        // $this->input->post('post_id')
        // or
        // $this->input->input_stream('post_id');
        // but for GET REQUEST USE THIS ( just for "backend" tests )

        if (!User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $post_id = intval($this->input->get('post_id'));
        $message = trim($this->input->get('message'));

        if (empty($post_id) || empty($message)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        $comment = $post->comment($message);

        $result = Comment_model::preparation($comment, 'full_info');
        return $this->response_success(['comment' => $result]);
    }

    protected function comment_delete()
    {
        if (!User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $comment_id = intval($this->input->get('id'));

        if (empty($comment_id)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try {
            $comment = new Comment_model($comment_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        $user = User_model::get_user();
        if (!$user->is_admin() && $user->get_id() != $comment->get_user_id()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_ACCESS);
        }

        $result = $comment->delete();

        return $this->response_success(['deleted' => $result]);
    }

    protected function comment_sub_add()
    {
        if (!User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $comment_id = intval($this->input->get('id'));
        $message = trim($this->input->get('message'));

        if (empty($comment_id) || empty($message)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try {
            $comment = new Comment_model($comment_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        $sub_comment = $comment->comment($message);

        $result = Comment_model::preparation($sub_comment, 'full_info');
        return $this->response_success(['comment' => $result]);
    }


    public function login()
    {
        // Right now for tests we use from controller
        $login = App::get_ci()->input->post('login');
        $password = App::get_ci()->input->post('password');

        if (empty($login) || empty($password)) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try {
            $user = Login_model::login($login, $password);
        } catch (Exception $e){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_ACCESS);
        }

        return $this->response_success(['user' => $user->get_id()]);
    }


    public function logout()
    {
        Login_model::logout();
        redirect(site_url('/'));
    }

    public function add_money(){
        // todo: 4th task  add money to user logic
        return $this->response_success(['amount' => rand(1,55)]); // Колво лайков под постом \ комментарием чтобы обновить . Сейчас рандомная заглушка
    }

    public function buy_boosterpack(){
        // todo: 5th task add money to user logic
        return $this->response_success(['amount' => rand(1,55)]); // Колво лайков под постом \ комментарием чтобы обновить . Сейчас рандомная заглушка
    }


    public function like(){
        // todo: 3rd task add like post\comment logic
        return $this->response_success(['likes' => rand(1,55)]); // Колво лайков под постом \ комментарием чтобы обновить . Сейчас рандомная заглушка
    }

}
