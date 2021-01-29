<?php

namespace Model;
use App;
use CI_Emerald_Model;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class Boosterpack_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'boosterpack';


    /** @var float Цена бустерпака */
    protected $price;
    /** @var float Банк, который наполняется */
    protected $bank;

    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    /**
     * @return float
     */
    public function get_price(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return bool
     */
    public function set_price(float $price)
    {
        $this->price = $price;
        return $this->save('price', $price);
    }

    /**
     * @return float
     */
    public function get_bank(): float
    {
        return $this->bank;
    }

    /**
     * @param float $bank
     *
     * @return bool
     */
    public function set_bank(float $bank)
    {
        $this->bank = $bank;
        return $this->save('bank', $bank);
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
    public function set_time_updated(string $time_updated)
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }

    function __construct($id = NULL)
    {
        parent::__construct();

        $this->set_id($id);
    }

    public function reload(bool $for_update = FALSE)
    {
        parent::reload($for_update);

        return $this;
    }

    public function buy()
    {
        App::get_ci()->s->start_trans();

        $user = User_model::get_user();
        $user->reload(TRUE);

        $price = $this->get_price();

        $wallet_balance = $user->get_wallet_balance() - $price;
        if ($wallet_balance < 0) {
            App::get_ci()->s->rollback();
            throw new \Exception('Not enough money');
        }

        $likes_amount = $this->produce_likes_after_buy();

        $likes_balance = $user->get_likes_balance() + $likes_amount;
        $total_withdrawn = $user->get_wallet_total_withdrawn() + $price;

        $user->set_wallet_balance($wallet_balance);
        $user->set_likes_balance($likes_balance);
        $user->set_wallet_total_withdrawn($total_withdrawn);

        Transaction_log_model::add_log($user,
            Transaction_type::BUY_BOOSTER_PACK(),
            -$price,
            $likes_amount,
            $this->get_id()
        );

        App::get_ci()->s->commit();

        return $likes_amount;
    }

    protected function produce_likes_after_buy(): int
    {
        $max_likes = intval( $this->get_bank() + $this->get_price());
        $amount = mt_rand(1, $max_likes);
        $this->set_bank($this->get_bank() + $this->get_price() - $amount);
        return $amount;
    }

}
