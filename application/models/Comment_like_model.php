<?php

namespace Model;
use App;
use CI_Emerald_Model;
use Exception;
use stdClass;

class Comment_like_model extends CI_Emerald_Model {
    const CLASS_TABLE = 'comment_like';


    /** @var int */
    protected $user_id;
    /** @var int */
    protected $comment_id;
    /** @var int */
    protected $amount;

    /** @var string */
    protected $time_created;

    // generated
    protected $comment;
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
    public function get_comment_id(): int
    {
        return $this->comment_id;
    }

    /**
     * @param int $comment_id
     *
     * @return bool
     */
    public function set_comment_id(int $comment_id)
    {
        $this->comment_id = $comment_id;
        return $this->save('comment_id', $comment_id);
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
     * @return Comment_model
     */
    public function get_comment(): Comment_model
    {
        $this->is_loaded(TRUE);

        if (empty($this->comment)) {
            $this->comment = new Comment_model($this->get_comment_id());
        }
        return $this->comment;
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

        $user = $this->get_user();
        $comment = $this->get_comment();

        $amount = $this->get_amount();

        $comment_new_likes_count = $comment->get_likes_count() - $amount;
        if ($comment_new_likes_count < 0) {
            // TODO: add recalculating logic later
            $comment_new_likes_count = 0;
        }
        $comment->set_likes_count($comment_new_likes_count);

        //// Refund deleted likes ?
        //$new_balance = $user->get_likes_balance() + $amount;
        //$user->set_likes_balance($new_balance);
        //... (log it)

        $result = parent::delete();

        App::get_ci()->s->commit();

        return $result;
    }

    public static function like(Comment_model $comment): Comment_like_model
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
            'comment_id' => $comment->get_id(),
        ], null, true);

        if (!$user_like) {
            $user_like = static::create([
                'user_id' => $user->get_id(),
                'comment_id' => $comment->get_id(),
                'amount' => 1,
            ]);
        } else {
            $user_like->set_amount($user_like->get_amount() + 1);
        }

        $comment->set_likes_count($comment->get_likes_count() + 1);

        $user->set_likes_balance($new_balance);

        App::get_ci()->s->commit();

        return $user_like;
    }


    /**
     * @param Comment_like_model|Comment_like_model[] $data
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
     * @param Comment_like_model $data
     * @return stdClass
     */
    private static function _preparation_full_info(Comment_like_model $data)
    {
        $o = new stdClass();

        $o->id = $data->get_id();
        $o->amount = $data->get_amount();
        $o->user_id = $data->get_user_id();

        $o->user = User_model::preparation($data->get_user(), 'main_page');
        $o->comment = Comment_model::preparation($data->get_comment(), 'short_info');

        $o->time_created = $data->get_time_created();

        return $o;
    }

    /**
     * @param Comment_like_model $data
     * @return stdClass
     */
    private static function _preparation_short_info(Comment_like_model $data)
    {
        $o = new stdClass();

        $o->id = $data->get_id();
        $o->amount = $data->get_amount();
        $o->user_id = $data->get_user_id();

        $o->time_created = $data->get_time_created();

        return $o;
    }


}
