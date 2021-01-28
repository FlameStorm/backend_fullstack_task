<?php

namespace Model;
use App;
use CI_Emerald_Model;
use Exception;
use stdClass;

class Post_like_model extends CI_Emerald_Model {
    const CLASS_TABLE = 'post_like';


    /** @var int */
    protected $user_id;
    /** @var int */
    protected $post_id;
    /** @var int */
    protected $amount;

    /** @var string */
    protected $time_created;

    // generated
    protected $post;
    protected $user;


    /**
     * @return int
     */
    public function get_user_id(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function set_user_id(int $user_id)
    {
        $this->user_id = $user_id;
        return $this->save('user_id', $user_id);
    }

    /**
     * @return int
     */
    public function get_post_id(): int
    {
        return $this->post_id;
    }

    /**
     * @param int $post_id
     *
     * @return bool
     */
    public function set_post_id(int $post_id)
    {
        $this->post_id = $post_id;
        return $this->save('post_id', $post_id);
    }

    /**
     * @return int
     */
    public function get_amount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     *
     * @return bool
     */
    public function set_amount(int $amount)
    {
        $this->amount = $amount;
        return $this->save('amount', $amount);
    }


    /**
     * @return string
     */
    public function get_time_created(): string
    {
        return $this->time_created;
    }

    /**
     * @param string $time_created
     *
     * @return bool
     */
    public function set_time_created(string $time_created)
    {
        $this->time_created = $time_created;
        return $this->save('time_created', $time_created);
    }

    // generated

    /**
     * @return Post_model
     */
    public function get_post(): Post_model
    {
        $this->is_loaded(TRUE);

        if (empty($this->post)) {
            $this->post = new Post_model($this->get_post_id());
        }
        return $this->post;
    }

    /**
     * @return User_model
     */
    public function get_user(): User_model
    {
        $this->is_loaded(TRUE);

        if (empty($this->user)) {
            try {
                $this->user = new User_model($this->get_user_id());
            } catch (Exception $exception) {
                $this->user = new User_model();
            }
        }
        return $this->user;
    }

    public function __construct($id = NULL)
    {
        parent::__construct();
        $this->set_id($id);
    }

    public function delete()
    {
        App::get_ci()->s->start_trans();

        $this->is_loaded(TRUE);

        $user = $this->get_user();
        $post = $this->get_post();

        $amount = $this->get_amount();

        $post_new_likes_count = $post->get_likes_count() - $amount;
        if ($post_new_likes_count < 0) {
            // TODO: add recalculating logic later
            $post_new_likes_count = 0;
        }
        $post->set_likes_count($post_new_likes_count);

        //// Refund deleted likes ?
        //$new_balance = $user->get_likes_balance() + $amount;
        //$user->set_likes_balance($new_balance);
        //... (log it)

        $result = parent::delete();

        App::get_ci()->s->commit();

        return $result;
    }

    public static function like(Post_model $post): Post_like_model
    {
        App::get_ci()->s->start_trans();

        $user = User_model::get_user();
        $new_balance = $user->get_likes_balance() - 1;
        if ($new_balance < 0) {
            App::get_ci()->s->rollback();
            throw new Exception('Insufficient likes');
        }

        $user_like = static::get_by([
            'user_id' => $user->get_id(),
            'post_id' => $post->get_id(),
        ], null, true);

        if (!$user_like) {
            $user_like = static::create([
                'user_id' => $user->get_id(),
                'post_id' => $post->get_id(),
                'amount' => 1,
            ]);
        } else {
            $user_like->set_amount($user_like->get_amount() + 1);
        }

        $post->set_likes_count($post->get_likes_count() + 1);

        $user->set_likes_balance($new_balance);

        App::get_ci()->s->commit();

        return $user_like;
    }


    /**
     * @param Post_like_model|Post_like_model[] $data
     * @param string $preparation
     * @return stdClass|stdClass[]
     * @throws Exception
     */
    public static function preparation($data, $preparation = 'default')
    {
        switch ($preparation)
        {
            case 'full_info':
                return self::_preparation_full_info($data);
            case 'short_info':
                return self::_preparation_short_info($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }

    /**
     * @param Post_like_model $data
     * @return stdClass
     */
    private static function _preparation_full_info(Post_like_model $data)
    {
        $o = new stdClass();

        $o->id = $data->get_id();
        $o->amount = $data->get_amount();
        $o->user_id = $data->get_user_id();

        $o->user = User_model::preparation($data->get_user(), 'main_page');
        $o->post = Post_model::preparation($data->get_post(), 'short_info');

        $o->time_created = $data->get_time_created();

        return $o;
    }

    /**
     * @param Post_like_model $data
     * @return stdClass
     */
    private static function _preparation_short_info(Post_like_model $data)
    {
        $o = new stdClass();

        $o->id = $data->get_id();
        $o->amount = $data->get_amount();
        $o->user_id = $data->get_user_id();

        $o->time_created = $data->get_time_created();

        return $o;
    }


}
