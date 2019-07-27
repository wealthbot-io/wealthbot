<?php


namespace App\Api;

/**
 * Trait Requests
 * @package App\Api
 */
trait Requests
{

    /**
     * Get API Endpoint
     * @param bool $sandbox
     * @return string
     */
    private function getEndpoint()
    {
        return ($this->sandbox==true)? $this->apiSandboxGateway : $this->apiGateway;
    }


    /**
     * Creates request
     * @param $method
     * @param $path
     * @param array $body
     * @return \Symfony\Contracts\HttpClient\ResponseInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function createRequest($method, $path, $body = '', $headers)
    {
        return json_decode($this->httpClient->request($method, $this->getEndpoint().$path, [
            'headers' =>  array_merge($headers, [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Bearer '. $this->apiKey,
                'Connection: close'
            ])
        ])->getContent());
    }

    /**
     * Get Quotes
     * @param $symbol
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getQuotes($symbol)
    {
        return $this->createRequest('GET', 'markets/quotes?symbols='.$symbol, '', []);
    }

    /**
     * Get Profile
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getProfile()
    {
        return $this->createRequest('GET', 'user/profile', '', []);
    }


    /**
     * Creates Access Token
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function createAccessToken()
    {
        $body = 'grant_type=authorization_code&code=PRpnf1o7';
        $length = strlen($body);
        return $this->createRequest('POST', 'oauth/accesstoken', $body, [
            'Content-length: ' . $length
        ]);
    }


    /**
     * New order
     * @param $account_id
     * @param $symbol
     * @param $side
     * @param $quantity
     * @param $price
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function placeOrder($account_id, $symbol, $side, $quantity, $price)
    {
        $body = 'class=equity&symbol='.$symbol.'&side='.$side.'&quantity='.$quantity.
            '&type=market&duration=day&price='.$price.'&stop='.$price;
        $length = strlen($body);
        return $this->createRequest('POST', 'accounts/'.$account_id.'/orders', $body, [
            'Content-length: ' . $length
        ]);
    }

    /**
     * Cancel order
     * @param $account_id
     * @param $order_id
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function cancelOrder($account_id, $order_id)
    {
        return $this->createRequest('DELETE', 'accounts/' . $account_id . '/orders/' . $order_id, '', []);
    }
}
