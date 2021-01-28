<?php

namespace Model;
use App;
use CI_Emerald_Model;
use Exception;
use stdClass;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class Comment_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'comment';

    const CLASS_FLAGS = CI_Emerald_Model::FLAGS_DEFAULT | CI_Emerald_Model::FLAGS_SOFT_DELETE;


    /** @var int */
    protected $user_id;
    /** @var int */
    protected $post_id;
    /** @var string */
    protected $text;

    /** @var int */
    protected $level;
    /** @var int|null */
    protected $parent_id;

    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    // denormalized
    /** @var int */
    protected $likes_count;

    // generated
    protected $comments;
    protected $parent_comment;
    protected $likes;
    protected $user;
    protected $post;


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
     * @return int|null
     */
    public function get_parent_id(): ?int
    {
        return $this->parent_id;
    }

    /**
     * @param int|null $parent_id
     *
     * @return bool
     */
    public function set_parent_id(int $parent_id = null)
    {
        $this->parent_id = $parent_id;
        return $this->save('parent_id', $parent_id);
    }

    /**
     * @return int
     */
    public function get_level(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     *
     * @return bool
     */
    public function set_level(int $level)
    {
        $this->level = $level;
        return $this->save('level', $level);
    }


    /**
     * @return string
     */
    public function get_text(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return bool
     */
    public function set_text(string $text)
    {
        $this->text = $text;
        return $this->save('text', $text);
    }

    /**
     * @return string
     */
    public function get_text_prepared(): string
    {
        return $this->is_deleted() ? '[deleted]' : $this->get_text();
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

    /**
     * @return string
     */
    public function get_time_updated(): string
    {
        return $this->time_updated;
    }

    /**
     * @param string $time_updated
     *
     * @return bool
     */
    public function set_time_updated(int $time_updated)
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }

    // denormalized

    /**
     * @return int
     */
    public function get_likes_count(): int
    {
        return $this->likes_count;
    }

    /**
     * @param int $likes_count
     *
     * @return bool
     */
    public function set_likes_count(int $likes_count)
    {
        $this->likes_count = $likes_count;
        return $this->save('likes_count', $likes_count);
    }

    // generated

    /**
     * @return mixed
     */
    public function get_likes()
    {
        if (!isset($this->likes)) {
            $this->likes = Comment_like_model::get_all_by([
                'comment_id' => $this->get_id()
            ]);
        }
        return $this->likes;
    }

    /**
     * @return Comment_model[]
     */
    public function get_comments()
    {
        if (!isset($this->comments)) {
            $this->comments = Comment_model::get_all_by_parent_id($this->get_id());
        }
        return $this->comments;
    }

    /**
     * @return Comment_model|null
     */
    public function get_parent() : ?Comment_model
    {
        if (!$this->get_parent_id()) {
            return null;
        }

        if (empty($this->parent_comment)){
             $this->parent_comment = new Comment_model($this->get_parent_id());
        }
        return $this->parent_comment;
    }

    /**
     * @param Comment_model $parent_comment
     */
    protected function set_parent(Comment_model $parent_comment)
    {
        $this->set_level($parent_comment->get_level() + 1);
        $this->set_parent_id($parent_comment->get_id());

        if (empty($this->parent_comment)){
             $this->parent_comment = new Comment_model($this->get_parent_id());
        }
        return $this->parent_comment;
    }

    /**
     * @return User_model
     */
    public function get_user():User_model
    {
        if (empty($this->user))
        {
            try {
                $this->user = new User_model($this->get_user_id());
            } catch (Exception $exception)
            {
                $this->user = new User_model();
            }
        }
        return $this->user;
    }

    /**
     * @return Post_model
     */
    public function get_post():Post_model
    {
        if (empty($this->post)) {
            $this->post = new Post_model($this->get_post_id());
        }
        return $this->post;
    }

    public function __construct($id = NULL)
    {
        parent::__construct();
        $this->set_id($id);
    }

    public function reload(bool $for_update = FALSE)
    {
        parent::reload($for_update);

        return $this;
    }

    public static function comment_post(Post_model $post, $message): Comment_model
    {
        $user = User_model::get_user();
        $data = [
            'user_id' => $user->get_id(),
            'post_id' => $post->get_id(),
            'text' => $message,
        ];
        return self::create($data);
    }

    public static function comment_comment(Comment_model $comment, $message): Comment_model
    {
        $user = User_model::get_user();
        $data = [
            'parent_id' => $comment->get_id(),
            'level' => $comment->get_level() + 1,
            'user_id' => $user->get_id(),
            'post_id' => $comment->get_post_id(),
            'text' => $message,
        ];
        return self::create($data);
    }

    public function comment(string $message): Comment_model
    {
        return Comment_model::comment_comment($this, $message);
    }

    public function delete()
    {
        // TODO: cascade delete features?

        return parent::delete();
    }

    /**
     * @return array
     */
    protected static function get_default_order(): array
    {
        return ['time_created' => 'ASC'];
    }

    /**
     * @param int $post_id
     * @return self[]
     * @throws Exception
     */
    public static function get_all_by_post_id(int $post_id)
    {
        return static::get_all_by(['post_id' => $post_id], static::get_default_order());
    }

    /**
     * @param int $parent_id
     * @return self[]
     * @throws Exception
     */
    public static function get_all_by_parent_id(int $parent_id)
    {
        return static::get_all_by(['parent_id' => $parent_id], static::get_default_order());
    }

    /**
     * @param self|self[] $data
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
            case 'post_info':
                return self::_preparation_post_info($data);
            case 'subcomments_info':
                return self::_preparation_subcomments_info($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }


    /**
     * @param self $data
     * @return stdClass
     * @throws Exception
     */
    private static function _preparation_full_info($data)
    {
        $o = new stdClass();

        $o->id = $data->get_id();
        $o->text = $data->get_text_prepared();

        $o->level = $data->get_level();
        if ($data->get_parent_id()) {
            $o->parent_id = $data->get_parent_id();
            //$o->parent = Comment_model::preparation($d->get_parent(), 'full_info');
        }

        $o->post = Post_model::preparation($data->get_post(),'comment_info');

        $o->user = User_model::preparation($data->get_user(),'main_page');

        $o->likes = $data->is_deleted() ? 0 : $data->get_likes_count();

        $o->comments = Comment_model::preparation($data->get_comments(), 'subcomments_info');

        $o->time_created = $data->get_time_created();
        $o->time_updated = $data->get_time_updated();


        return $o;
    }

    /**
     * @param self[] $datas
     * @return stdClass[]
     * @throws Exception
     */
    private static function _preparation_post_info($datas)
    {
        $ret = [];

        foreach ($datas as $data){
            $o = new stdClass();

            $o->id = $data->get_id();
            $o->text = $data->get_text_prepared();

            $o->level = $data->get_level();
            if ($data->get_parent_id()) {
                $o->parent_id = $data->get_parent_id();
                //$o->parent = Comment_model::preparation($data->get_parent(), 'full_info');
            }

            $o->user = User_model::preparation($data->get_user(),'main_page');

            $o->likes = $data->is_deleted() ? 0 : $data->get_likes_count();

            $o->time_created = $data->get_time_created();
            $o->time_updated = $data->get_time_updated();

            $ret[] = $o;
        }

        return $ret;
    }

    /**
     * @param self[] $datas
     * @return stdClass[]
     * @throws Exception
     */
    private static function _preparation_subcomments_info($datas)
    {
        $ret = [];

        foreach ($datas as $data){
            $o = new stdClass();

            $o->id = $data->get_id();
            $o->text = $data->get_text_prepared();

            $o->level = $data->get_level();
            if ($data->get_parent_id()) {
                $o->parent_id = $data->get_parent_id();
                //$o->parent = Comment_model::preparation($data->get_parent(), 'full_info');
            }

            $o->user = User_model::preparation($data->get_user(),'main_page');

            $o->likes = $data->is_deleted() ? 0 : $data->get_likes_count();

            $o->comments = Comment_model::preparation($data->get_comments(), 'subcomments_info');

            $o->time_created = $data->get_time_created();
            $o->time_updated = $data->get_time_updated();

            $ret[] = $o;
        }

        return $ret;
    }

    /**
     * @param self $datas
     * @return stdClass
     * @throws Exception
     */
    private static function _preparation_short_info($data)
    {
        $o = new stdClass();

        $o->id = $data->get_id();
        $o->text = $data->get_text_prepared();

        $o->level = $data->get_level();
        if ($data->get_parent_id()) {
            $o->parent_id = $data->get_parent_id();
        }

        $o->likes = $data->is_deleted() ? 0 : $data->get_likes_count();

        $o->time_created = $data->get_time_created();
        $o->time_updated = $data->get_time_updated();

        return $o;
    }


}
