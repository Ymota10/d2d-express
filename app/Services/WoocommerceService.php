<?php

namespace App\Services;

use Automattic\WooCommerce\Client;

class WooCommerceService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(
            config('https://kuddl-eg.com'),
            config('ck_7e7a9014dddfad0270a2ebc8159dfd23fdba0416'),
            config('cs_6b31900e26880150882cf2bc1fe40dfe409d5c4b'),
            [
                'version' => 'wc/v3',
            ]
        );
    }

    public function getOrders($params = [])
    {
        return $this->client->get('orders', $params);
    }
}
