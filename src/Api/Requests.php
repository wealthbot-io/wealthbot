<?php


namespace App\Api;


trait Requests
{

    /**
     * Get API Endpoint
     * @param bool $sandbox
     * @return string
     */
    private function getEndpoint(){
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
    private function createRequest($method, $path, $body = '',$headers){
        return $this->httpClient->request($method, $this->getEndpoint().$path,[
            'headers' =>  array_merge($headers, [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Bearer '. $this->apiKey,
                'Connection: close'
            ])
        ]);
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
    public function getQuotes($symbol){
        return $this->createRequest('GET','markets/quotes?symbols='.$symbol,'',[])->getContent();
    }

    /**
     * Get Profile
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getProfile(){
        return $this->createRequest('GET','user/profile', [],'',[])->getContent();
    }


    public function createAccessToken(){
        $body = 'grant_type=authorization_code&code=PRpnf1o7';
        $length = strlen($body);
        return $this->createRequest('POST','oauth/accesstoken',$body,[
            'Content-length: ' . $length
        ])->getContent();
    }

}