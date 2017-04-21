<?php
namespace Test\Fixture;

class WealthbotRebalancerGlobalFixtures {

    public static $accounts = array();

    public static $clients = array();

    public static function init()
    {
        self::$accounts = array(
            array(
                'id' => 1,
                'isInitialRebalance' => true
            ),
            array(
                'id' => 2,
                'isInitialRebalance' => false
            ),
            array(
                'id' => 3,
                'isInitialRebalance' => true
            ),
        );

        self::$clients = array(
            array(
                'id' => 1,
                'name'=> 'client1',
                'accounts' => array(
                    self::$accounts[0],
                    self::$accounts[1]
                )
            ),
            array(
                'id' => 2,
                'name'=> 'client2',
                'accounts' => array(
                    self::$accounts[2]
                )
            )

        );
    }
}