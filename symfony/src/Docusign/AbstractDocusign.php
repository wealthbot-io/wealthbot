<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 13.08.13
 * Time: 18:32
 * To change this template use File | Settings | File Templates.
 */

namespace App\Docusign;

use App\Model\Envelope;
use App\Model\RecipientInterface;

abstract class AbstractDocusign
{
    protected static $supportedMethods = ['get', 'post', 'put', 'delete'];

    protected $username;
    protected $password;
    protected $integratorKey;
    protected $brandId;

    protected $accountId;
    protected $baseUrl;

    public function __construct(array $config)
    {
        $this->setUsername($config['username']);
        $this->setPassword($config['password']);
        $this->setIntegratorKey($config['integratorKey']);

        if (isset($config['brand_id'])) {
            $this->brandId = $config['brand_id'];
        }
    }

    /**
     * DocuSign authentication.
     * Get account id and base url from DocuSign.
     */
    public function authenticate()
    {
        $this->removeAllPersistentData();

        $response = $this->getLoginInfo();

        $accountId = $response->loginAccounts[0]->accountId;
        $baseUrl = $response->loginAccounts[0]->baseUrl;

        $this->setAccountId($accountId);
        $this->setBaseUrl($baseUrl);
        $this->setPersistentData('accountId', $accountId);
        $this->setPersistentData('baseUrl', $baseUrl);
    }

    /**
     * Get DocuSign login information.
     *
     * @return mixed
     */
    public function getLoginInfo()
    {
        return $this->makeRequest('https://demo.docusign.net/restapi/v2/login_information');
    }

    /**
     * Send envelope.
     *
     * @param Envelope $envelope
     * @param array    $options
     *
     * @return mixed
     */
    public function sendEnvelope(Envelope $envelope, array $options = [])
    {
        $defaultOptions = [
            'accountId' => $this->getAccountId(),
            'emailSubject' => $envelope->getEmailSubject(),
            'emailBlurb' => $envelope->getEmailBlurb(),
            'status' => $envelope->getStatus(),
        ];

        if (null !== $this->brandId) {
            $defaultOptions['brandId'] = $this->brandId;
        }

        if (count($options)) {
            $data = array_merge($defaultOptions, $options);
        } else {
            $data = $defaultOptions;
        }

        if (isset($options['templateId'])) {
            $templateRoles = $this->prepareRecipients($envelope);
            if (count($templateRoles)) {
                $data['templateRoles'] = $templateRoles;
            }
        }

        $dataString = json_encode($data);
        $headers = ['Content-Length: '.strlen($dataString)];

        //echo '<pre>' . $dataString; die;

        return $this->makeRequest(
            $this->getBaseUrl().'/envelopes',
            ['method' => 'post', 'data' => $dataString, 'headers' => $headers]
        );
    }

    /**
     * Send envelope from template.
     *
     * @param Envelope $envelope
     * @param $templateId
     * @param array $options
     *
     * @return mixed
     */
    public function sendEnvelopeFromTemplate(Envelope $envelope, $templateId, array $options = [])
    {
        $options['templateId'] = $templateId;

        return $this->sendEnvelope($envelope, $options);
    }

    /**
     * Embedded signing.
     *
     * @param string             $envelopId
     * @param RecipientInterface $recipient
     * @param string             $returnUrl
     *
     * @return string
     */
    public function getEmbeddedSigningUrl($envelopId, RecipientInterface $recipient, $returnUrl)
    {
        $data = [
            'returnUrl' => $returnUrl,
            'authenticationMethod' => 'None',
            'email' => $recipient->getEmail(),
            'userName' => $recipient->getName(),
        ];

        if (null !== $recipient->getClientUserId()) {
            $data['clientUserId'] = $recipient->getClientUserId();
        }

        $dataString = json_encode($data);
        $headers = ['Content-Length: '.strlen($dataString)];

        $result = $this->makeRequest(
            $this->getBaseUrl().'/envelopes/'.$envelopId.'/views/recipient',
            ['method' => 'post', 'data' => $dataString, 'headers' => $headers]
        );

        return $result ? $result->url : null;
    }

    /**
     * Get envelope status.
     *
     * @param string $envelopeId
     *
     * @return string
     */
    public function getEnvelopeStatus($envelopeId)
    {
        $url = $this->getBaseUrl().'/envelopes/'.$envelopeId;
        $result = $this->makeRequest($url);

        return $result->status;
    }

    /**
     * Get envelope signers statuses
     * array(recipient_email => status).
     *
     * @param string $envelopeId
     *
     * @return array
     */
    public function getEnvelopeRecipientsStatuses($envelopeId)
    {
        $url = $this->getBaseUrl().'/envelopes/'.$envelopeId.'/recipients';
        $response = $this->makeRequest($url);

        $result = [];
        foreach ($response->signers as $signer) {
            $result[$signer->email] = $signer->status;
        }

        return $result;
    }

    /**
     * Update user profile.
     *
     * @param string      $userId
     * @param array       $names
     * @param string|null $companyName
     *
     * @return mixed|null
     */
    public function updateUserProfile($userId, array $names = [], $companyName = null)
    {
        $url = $this->getBaseUrl().'/users/'.$userId.'/profile';

        $data = [];
        if (null !== $companyName) {
            $data['companyName'] = $companyName;
        }

        if (isset($names['userName'])) {
            $data['userDetails']['userName'] = $names['userName'];
        }
        if (isset($names['firstName'])) {
            $data['userDetails']['firstName'] = $names['firstName'];
        }
        if (isset($names['lastName'])) {
            $data['userDetails']['lastName'] = $names['lastName'];
        }
        if (isset($names['middleName'])) {
            $data['userDetails']['middleName'] = $names['middleName'];
        }

        if (!empty($data)) {
            return $this->makeRequest(
                $url,
                ['method' => 'put', 'data' => json_encode($data), $headers = ['Accept: application/json']]
            );
        }

        return;
    }

    /**
     * Update account profile.
     *
     * @param array       $names
     * @param string|null $companyName
     *
     * @return mixed|null
     */
    public function updateAccountProfile(array $names = [], $companyName = null)
    {
        return $this->updateUserProfile($this->getUsername(), $names, $companyName);
    }

    /**
     * Get envelope documents.
     *
     * @param string $envelopeId
     *
     * @return mixed
     */
    public function getEnvelopeDocuments($envelopeId)
    {
        $result = $this->makeRequest(
            $this->getBaseUrl().'/envelopes/'.$envelopeId.'/documents'
        );

        return $result;
    }

    public function getEnvelopeDocument($envelopeId, $documentId)
    {
        $result = $this->makeRequest(
            $this->getBaseUrl().'/envelopes/'.$envelopeId.'/documents/'.$documentId
        );

        return $result;
    }

    /**
     * Call api url.
     *
     * @param string $url
     * @param array  $params
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function makeRequest($url, array $params = [])
    {
        $authHeader = [
            'Username' => $this->username,
            'Password' => $this->password,
            'IntegratorKey' => $this->integratorKey,
        ];

        if (isset($params['authParams']) && is_array($params['authParams'])) {
            $authHeader = array_merge($params['authParams'], $authHeader);
        }

        $curlParams = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-DocuSign-Authentication: '.json_encode($authHeader),
            ],
        ];

        if (isset($params['method']) && in_array($params['method'], self::$supportedMethods)) {
            switch ($params['method']) {
                case 'post':
                    $curlParams[CURLOPT_POST] = true;
                    break;
                case 'put':
                    $curlParams[CURLOPT_CUSTOMREQUEST] = 'PUT';
                    break;
                default:
                    break;
            }
        }

        if (isset($params['headers'])) {
            $headers = (array) $params['headers'];
            $curlParams[CURLOPT_HTTPHEADER] = array_merge($curlParams[CURLOPT_HTTPHEADER], $headers);
        }

        if (isset($params['data'])) {
            $curlParams[CURLOPT_POSTFIELDS] = $params['data'];
        }

        $curl = curl_init($url);
        curl_setopt_array($curl, $curlParams);

        $result = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $response = json_decode($result);

        if (false === $result) {
            curl_close($curl);
            throw new \Exception(sprintf('Server return status: %s. %s', $status, curl_error($curl)));
        } elseif (is_object($response) && property_exists($response, 'errorCode')) {
            curl_close($curl);
            throw new \Exception(sprintf('Server return error: %s. %s', $response->errorCode, $response->message));
        }

        curl_close($curl);

        return $response ? $response : $result;
    }

    /**
     * Set DocuSign user name.
     *
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get DocuSign user name.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set DocuSign user password.
     *
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get DocuSign user password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set DocuSign user integrator key.
     *
     * @param string $integratorKey
     *
     * @return $this
     */
    public function setIntegratorKey($integratorKey)
    {
        $this->integratorKey = $integratorKey;

        return $this;
    }

    /**
     * Get DocuSign user integrator key.
     *
     * @return string
     */
    public function getIntegratorKey()
    {
        return $this->integratorKey;
    }

    /**
     * Set DocuSign account id.
     *
     * @param string $accountId
     *
     * @return $this
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    /**
     * Get DocuSign account id.
     *
     * @return string
     */
    public function getAccountId()
    {
        if (null !== $this->accountId) {
            return $this->accountId;
        }

        $accountId = $this->getPersistentData('accountId');
        if (null !== $accountId) {
            return $accountId;
        }

        $this->authenticate();

        return $this->accountId;
    }

    /**
     * Set DocuSign base url.
     *
     * @param string $baseUrl
     *
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Get DocuSign base url.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if (null !== $this->baseUrl) {
            return $this->baseUrl;
        }

        $baseUrl = $this->getPersistentData('baseUrl');
        if (null !== $baseUrl) {
            return $baseUrl;
        }

        $this->authenticate();

        return $this->baseUrl;
    }

    /**
     * Returns prepared array of recipients.
     *
     * @param Envelope $envelope
     *
     * @return array
     */
    private function prepareRecipients(Envelope $envelope)
    {
        $templateRoles = [];

        $recipients = $envelope->getRecipients();
        if (is_array($recipients) && count($recipients)) {
            foreach ($recipients as $recipient) {
                if ($recipient instanceof RecipientInterface) {
                    $templateRole = [
                        'name' => $recipient->getName(),
                        'email' => $recipient->getEmail(),
                        'roleName' => $recipient->getRoleName(),
                        /*'requireIdLookup' => true,
                        "idCheckConfigurationName" => "ID Check $"
                        "idCheckInformationInput" => array(
                            "addressInformationInput" => array(
                                "addressInformation" => array(
                                    "street1" => "trertert",
                                    "city" => "fasdfsdf",
                                    "state" => "AK",
                                    "zip" => "555555",
                                ),
                                "displayLevelCode" => "ReadOnly",
                                "receiveInResponse" => false
                            ),
                            "dobInformationInput" => array(
                                "dateOfBirth" => "03-03-1990",
                                "displayLevelCode" => "ReadOnly",
                                "receiveInResponse" => false
                            ),
                            "ssn4InformationInput" => array(
                                "ssn4" => "6789",
                                "displayLevelCode" => "ReadOnly",
                                "receiveInResponse" => false
                            )
                        )*/
                    ];

                    $tabs = $recipient->getTabs();
                    if ($tabs->count()) {
                        $templateRole['tabs'] = $tabs->toArray();
                    }

                    if ($recipient->getClientUserId()) {
                        $templateRole['clientUserId'] = $recipient->getClientUserId();
                    }

                    $templateRoles[] = $templateRole;
                }
            }
        }

        return $templateRoles;
    }

    /**
     * Set $value data for $key in the persistent storage.
     *
     * @param string $key
     * @param $value
     */
    abstract protected function setPersistentData($key, $value);

    /**
     * Get data for $key from the persistent storage.
     *
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    abstract protected function getPersistentData($key, $default = null);

    /**
     * Remove data with $key from the persistent storage.
     *
     * @param string $key
     */
    abstract protected function removePersistentData($key);

    /**
     * Remove all data from the persistent storage.
     */
    abstract protected function removeAllPersistentData();
}
