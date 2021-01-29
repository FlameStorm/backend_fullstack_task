<?php

namespace Model;
use App;
use CI_Emerald_Model;
use Exception;
use stdClass;
use System\Core\Emerald_enum;


/**
 * Class Transaction_type
 * @method static static REFILL_ACCOUNT()
 * @method static static BUY_BOOSTER_PACK()
 * @package Model
 */
class Transaction_type extends Emerald_enum
{
    const REFILL_ACCOUNT = 1;
    const BUY_BOOSTER_PACK = 2;
}

/**
 * Class Transaction_log_model
 * @package Model
 */
class Transaction_log_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'transaction_log';
    //const TYPE_ADD_MONEY = 1;
    //const TYPE_BUY_PACK = 2;

    /** @var int */
    protected $user_id;
    /** @var int */
    protected $initiator_user_id;
    /** @var int */
    protected $type;
    /** @var float */
    protected $amount;
    /** @var int */
    protected $likes_amount;
    /** @var string */
    protected $time_created;
    /** @var string|stdClass */
    protected $data;

    // generated
    protected $user;
    protected $initiator_user;


    function __construct($id = NULL)
    {
        parent::__construct();
        $this->set_id($id);
    }

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
    public function get_initiator_user_id(): int
    {
        return $this->initiator_user_id;
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function set_initiator_user_id(int $user_id)
    {
        $this->initiator_user_id = $user_id;
        return $this->save('initiator_user_id', $user_id);
    }


    /**
     * @return int
     */
    public function get_type(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return bool
     */
    public function set_type(int $type)
    {
        //Transaction_type::TYPE_REFILL_ACCOUNT();
        $this->type = $type;
        return $this->save('type', $type);
    }

    /**
     * @return float
     */
    public function get_amount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return bool
     */
    public function set_amount(float $amount)
    {
        $this->amount = $amount;
        return $this->save('amount', $amount);
    }

    /**
     * @return int
     */
    public function get_likes_amount(): int
    {
        return $this->likes_amount;
    }

    /**
     * @param int $likes_amount
     * @return bool
     */
    public function set_likes_amount(int $likes_amount)
    {
        $this->likes_amount = $likes_amount;
        return $this->save('likes_amount', $likes_amount);
    }

    /**
     * @return stdClass
     */
    public function get_data()
    {
        if (is_string($this->data)) {
            $this->data = json_decode($this->data);
        }
        return $this->data;
    }

    /**
     * @param string $data
     * @return bool
     */
    public function set_data(string $data): bool
    {
        $this->data = $data;
        return $this->save('data', json_encode($data));
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
     * @return bool
     */
    public function set_time_created(string $time_created): bool
    {
        $this->time_created = $time_created;
        return $this->save('time_created', $time_created);
    }

    // generated

    /**
     * @return User_model
     */
    public function get_user(): User_model
    {
        if (empty($this->user)) {
            try {
                $this->user = new User_model($this->get_user_id());
            } catch (Exception $exception) {
                $this->user = new User_model();
            }
        }
        return $this->user;
    }

    /**
     * @return User_model
     */
    public function get_initiator_user(): User_model
    {
        if (empty($this->initiator_user)) {
            try {
                $this->initiator_user = new User_model($this->get_initiator_user_id());
            } catch (Exception $exception) {
                $this->initiator_user = new User_model();
            }
        }
        return $this->initiator_user;
    }


    /**
     * @param User_model $user
     * @param Transaction_type $type
     * @param float $amount
     * @param int $likes_amount
     * @param mixed $data
     * @param User_model|null $initiator_user
     * @return Transaction_log_model
     */
    public static function add_log(
        User_model $user,
        Transaction_type $type,
        float $amount,
        int $likes_amount = 0,
        $data = null,
        ?User_model $initiator_user = null
    )
    {
        if (!$initiator_user) {
            $initiator_user = $user;
        }

        return static::create([
            'user_id' => $user->get_id(),
            'initiator_user_id' => $initiator_user->get_id(),
            'type' => $type->get_value(),
            'amount' => $amount,
            'likes_amount' => $likes_amount,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
        ]);
    }

    public static function create_boosterpack_log(User_model $user, Boosterpack_model $boosterpack, int $likes)
    {
        static::create([
            'user_id' => $user->get_id(),
            'type' => Transaction_type::BUY_BOOSTER_PACK,
            'data' => json_encode([
                'price' => $boosterpack->get_price(),
                'wallet_balance' => $user->get_wallet_balance(),
                'likes' => $likes,
                'likes_balance' => $user->get_likes_balance(),
            ]),
        ]);
    }

    /**
     * @param static[] $data
     * @param string $preparation
     * @return stdClass[]
     * @throws Exception
     */
    public static function preparation($data, $preparation = 'default')
    {
        switch ($preparation) {
            case 'short_info':
                return self::_preparation_short_info($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }

    /**
     * @param static[] $datas
     * @return stdClass[]
     * @throws Exception
     */
    private static function _preparation_short_info($datas)
    {
        $ret = [];
        foreach ($datas as $data) {
            $o = new stdClass();

            $o->type = $data->get_type();
            $o->amount = $data->get_amount();

            $o->user_id = $data->get_user_id();
            $o->initiator_user_id = $data->get_initiator_user_id();

            $o->time_created = $data->get_time_created();

            /** @see Log_model::create_money_log() */
            /** @see Log_model::create_boosterpack_log() */
            $o->data = $data->get_data();

            $ret[] = $o;
        }
        return $ret;
    }
}
