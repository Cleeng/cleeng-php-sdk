<?php
/**
 * Cleeng PHP SDK (http://cleeng.com)
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * @link    https://github.com/Cleeng/cleeng-php-sdk for the canonical source repository
 * @package Cleeng_PHP_SDK
 */

/**
 * Main class that should be used to access Cleeng API
 *
 * @link http://developers.cleeng.com/PHP_SDK
 */
class Cleeng_Api
{

    /**
     * API endpoint for Cleeng Sandbox
     */
    const SANDBOX_ENDPOINT  = 'https://sandbox.cleeng.com/api/3.0/json-rpc';

    /**
     * Cleeng Javascript library for Cleeng Sandbox
     */
    const SANDBOX_JSAPI_URL  = 'https://sandbox.cleeng.com/js-api/3.0/api.js';

    /**
     * API endpoint - by default points to live platform
     *
     * @var string
     */
    protected $endpoint = 'https://api.cleeng.com/3.0/json-rpc';

    /**
     * Cleeng Javascript library URL
     */
    protected $jsApiUrl = 'https://cdn-static.cleeng.com/js-api/3.0/api.js';

    /**
     * Transport class used to communicate with Cleeng servers
     *
     * @var Cleeng_Transport_TransportInterface
     */
    protected $transport;

    /**
     * List of stacked API requests
     * @var array
     */
    protected $pendingCalls = array();

    /**
     * Batch mode - determines if requests should be automatically stacked and sent in batch request
     *
     * @var int
     */
    protected $batchMode = false;

    /**
     * Publisher's token - must be set manually with setPublisherToken()
     *
     * @var string
     */
    protected $publisherToken;

    /**
     * Distributor's token - must be set manually with setDistributorToken()
     *
     * @var string
     */
    protected $distributorToken;

    /**
     * Customer's access token - should be read automatically from cookie
     * @var string
     */
    protected $customerToken = '';

    /**
     * Name of cookie used to store customer's access token
     * @var string
     */
    protected $cookieName = 'CleengClientAccessToken';

    /**
     * "Default" application ID, indicating general, "Cleeng Open" based client.
     * Usually there's no need to change that.
     *
     * @var string
     */
    protected $appId = '35e97a6231236gb456heg6bd7a6bdsf7';

    /**
     * Last request sent to Cleeng server.
     *
     * Can be used for debugging purposes.
     *
     * @var string
     */
    protected $rawRequest;

    /**
     * Last response from Cleeng server.
     *
     * Can be used for debugging purposes.
     *
     * @var string
     */
    protected $rawResponse;

    /**
     * Send request to Cleeng API or put it on a list (batch mode)
     *
     * @param string $method
     * @param array $params
     * @param Cleeng_Entity_Base $objectToPopulate
     * @return Cleeng_Entity_Base
     */
    public function api($method, $params = array(), $objectToPopulate = null)
    {
        $id = count($this->pendingCalls)+1;
        $payload = json_encode(
            array(
                'method' => $method,
                'params' => $params,
                'jsonrpc' => '2.0',
                'id' => $id,
            )
        );

        if (null === $objectToPopulate) {
            $objectToPopulate = new Cleeng_Entity_Base();
        }

        $this->pendingCalls[$id] = array(
            'entity' => $objectToPopulate,
            'payload' => $payload
        );

        if (!$this->batchMode) {
            // batch requests disabled, send request
            $this->commit();
        }

        return $objectToPopulate;
    }

    private function processRequestData($requestData)
    {
        if (!count($requestData)) {
            return;
        }
        $encodedRequest = '[' . implode(',', $requestData) . ']';
        $this->rawRequest = $encodedRequest;
        $raw = $this->getTransport()->call($this->getEndpoint(), $encodedRequest);
        $this->rawResponse = $raw;
        $decodedResponse = json_decode($raw, true);

        if (!is_array($decodedResponse)) {
            $this->pendingCalls = array();
            throw new Cleeng_Exception_InvalidJsonException("Expected valid JSON string, received: $raw");
        }

        if (!count($decodedResponse)) {
            $this->pendingCalls = array();
            throw new Cleeng_Exception_InvalidJsonException("Empty response received.");
        }

        foreach ($decodedResponse as $response) {

            if (!isset($response['id'])) {
                $this->pendingCalls = array();
                throw new Cleeng_Exception_RuntimeException("Invalid response from API - missing JSON-RPC ID.");
            }

            if (isset($this->pendingCalls[$response['id']])) {
                $transferObject = $this->pendingCalls[$response['id']]['entity'];
                $transferObject->pending = false;

                if (isset($response['error']) && $response['error']) {
                    $this->pendingCalls = array();
                    throw new Cleeng_Exception_ApiErrorException($response['error']['message'], $response['error']['code']);
                }
                if (!isset($response['result']) || !is_array($response['result'])) {
                    throw new Cleeng_Exception_ApiErrorException(
                        "Invalid response type received from API. Expected array, got "
                        . getType($response['result']) . '.'
                    );
                }
                $transferObject->populate($response['result']);
            }
        }
    }

    /**
     * Process pending API requests in a batch call
     */
    public function commit()
    {
        $requestData = array();
        foreach ($this->pendingCalls as $req) {
            $payload = $req['payload'];
            $requestData[] = $payload;

            if (count($requestData) >= 25) {
                $this->processRequestData($requestData);
                $requestData = array();
            }
        }
        $this->processRequestData($requestData);

        $this->pendingCalls = array();
    }

    /**
     * @param string $endpoint
     * @return self
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function setJsApiUrl($jsApiUrl)
    {
        $this->jsApiUrl = $jsApiUrl;
        return $this;
    }

    public function getJsApiUrl()
    {
        return $this->jsApiUrl;
    }

    /**
     * Helper function for setting up test environment
     */
    public function enableSandbox()
    {
        $this->setEndpoint(self::SANDBOX_ENDPOINT);
        $this->setJsApiUrl(self::SANDBOX_JSAPI_URL);
    }

    /**
     * @param \Cleeng_Transport_TransportInterface $transport
     * @return self
     */
    public function setTransport(Cleeng_Transport_TransportInterface $transport)
    {
        $this->transport = $transport;
        return $this;
    }


    /**
     * Return transport object or create new (curl-based)
     *
     * @return Cleeng_Transport_TransportInterface
     */
    public function getTransport()
    {
        if (null === $this->transport) {
            $this->transport = new Cleeng_Transport_Curl();
        }
        return $this->transport;
    }

    /**
     * @return string
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * @return string
     */
    public function getRawRequest()
    {
        return $this->rawRequest;
    }

    /**
     * Class constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        if (array_key_exists('enableSandbox', $options) && $options['enableSandbox'] == true) {
            $this->enableSandbox();
        }

        foreach ($options as $name => $value) {
            $methodName = 'set' . ucfirst($name);
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }
    }

    /**
     * Set customer's token
     *
     * @param string $customerToken
     * @return self
     */
    public function setCustomerToken($customerToken)
    {
        $this->customerToken = $customerToken;
        return $this;
    }

    /**
     * Returns customer's token. If token is not set, this function will
     * try to read it from the cookie.
     *
     * @return string
     */
    public function getCustomerToken()
    {
        if (!$this->customerToken) {
            if (isset($_COOKIE[$this->cookieName])) {
                $this->customerToken = $_COOKIE[$this->cookieName];
            } elseif (isset($_GET[$this->cookieName])) {
                $this->customerToken = $_GET[$this->cookieName];
            }
        }
        return $this->customerToken;
    }

    /**
     * Set publisher's token
     *
     * @param string $publisherToken
     * @return self
     */
    public function setPublisherToken($publisherToken)
    {
        $this->publisherToken = $publisherToken;
        return $this;
    }

    /**
     * Returns publisher's token
     *
     * @return string
     */
    public function getPublisherToken()
    {
        return $this->publisherToken;
    }
    /**
     * Set distributor's token
     *
     * @param string $distributorToken
     * @return self
     */
    public function setDistributorToken($distributorToken)
    {
        $this->distributorToken = $distributorToken;
        return $this;
    }

    /**
     * Returns distributor's token
     *
     * @return string
     */
    public function getDistributorToken()
    {
        return $this->distributorToken;
    }

    /**
     * @param int $batchMode
     * @return self
     */
    public function setBatchMode($batchMode)
    {
        $this->batchMode = $batchMode;
        return $this;
    }

    /**
     * @return int
     */
    public function getBatchMode()
    {
        return $this->batchMode;
    }

    /**
     * Customer API: getCustomer
     *
     * @return Cleeng_Entity_Customer
     */
    public function getCustomer()
    {
        $userInfo = new Cleeng_Entity_Customer();
        return $this->api('getCustomer', array('customerToken' => $this->getCustomerToken()), $userInfo);
    }

    /**
     * Customer API: getCustomerEmail
     *
     * @return Cleeng_Entity_CustomerEmail
     */
    public function getCustomerEmail()
    {
        $customerEmail = new Cleeng_Entity_CustomerEmail();
        return $this->api(
            'getCustomerEmail',
            array(
                'publisherToken' => $this->getPublisherToken(),
                'customerToken' => $this->getCustomerToken()
            ),
            $customerEmail
        );
    }

    /**
     * Customer API: getAccessStatus
     *
     * @param $offerId
     * @param string $ipAddress
     * @return Cleeng_Entity_AccessStatus
     */
    public function getAccessStatus($offerId, $ipAddress = '')
    {
        $customerToken = $this->getCustomerToken();
        return $this->api(
            'getAccessStatus',
            array('customerToken' => $customerToken, 'offerId' => $offerId, 'ipAddress' => $ipAddress),
            new Cleeng_Entity_AccessStatus()
        );
    }

    /**
     * Customer API: prepareRemoteAuth
     *
     * @param $customerData
     * @param $flowDescription
     * @return Cleeng_Entity_RemoteAuth
     * @throws Cleeng_Exception_InvalidArgumentException
     * @throws Cleeng_Exception_RuntimeException
     */
    public function prepareRemoteAuth($customerData, $flowDescription)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        if (!is_array($customerData)) {
            throw new Cleeng_Exception_InvalidArgumentException("'customerData' must be an array.");
        }
        if (!is_array($flowDescription)) {
            throw new Cleeng_Exception_InvalidArgumentException("'flowDescription' must be an array.");
        }
        return $this->api(
            'prepareRemoteAuth',
            array('publisherToken' => $publisherToken, 'customerData' => $customerData, 'flowDescription' => $flowDescription),
            new Cleeng_Entity_RemoteAuth()
        );
    }

    /**
     * Customer API: generateCustomerToken
     *
     * @param $customerEmail
     * @return Cleeng_Entity_CustomerToken
     * @throws Cleeng_Exception_RuntimeException
     */
    public function generateCustomerToken($customerEmail)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'generateCustomerToken',
            array('publisherToken' => $publisherToken, 'customerEmail' => $customerEmail),
            new Cleeng_Entity_CustomerToken()
        );
    }

    /**
     * Customer API: updateCustomerEmail
     *
     * @param $customerEmail
     * @param $newEmail
     * @return Cleeng_Entity_OperationStatus
     * @throws Cleeng_Exception_RuntimeException
     */
    public function updateCustomerEmail($customerEmail, $newEmail)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'updateCustomerEmail',
            array('publisherToken' => $publisherToken, 'customerEmail' => $customerEmail, 'newEmail' => $newEmail),
            new Cleeng_Entity_OperationStatus()
        );
    }

    /**
     * Customer API: updateCustomerSubscription
     *
     * @param $customerEmail
     * @param $offerId
     * @param $subscriptionData
     * @return Cleeng_Entity_CustomerSubscription
     * @throws Cleeng_Exception_RuntimeException
     */
    public function updateCustomerSubscription($customerEmail, $offerId, $subscriptionData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'updateCustomerSubscription',
            array('publisherToken' => $publisherToken, 'customerEmail' => $customerEmail, 'offerId' => $offerId, 'subscriptionData' => $subscriptionData),
            new Cleeng_Entity_CustomerSubscription()
        );
    }

    /**
     * Customer API: updateCustomerRental
     *
     * @param $customerEmail
     * @param $offerId
     * @param $rentalData
     * @return Cleeng_Entity_CustomerRental
     * @throws Cleeng_Exception_RuntimeException
     */
    public function updateCustomerRental($customerEmail, $offerId, $rentalData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'updateCustomerRental',
            array('publisherToken' => $publisherToken, 'customerEmail' => $customerEmail, 'offerId' => $offerId, 'rentalData' => $rentalData),
            new Cleeng_Entity_CustomerRental()
        );
    }

    /**
     * Customer API: listCustomerSubscriptions
     *
     * @param $customerEmail
     * @param $offset
     * @param $limit
     * @return Cleeng_Entity_Collection
     * @throws Cleeng_Exception_RuntimeException
     */
    public function listCustomerSubscriptions($customerEmail, $offset, $limit)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'listCustomerSubscriptions',
            array('publisherToken' => $publisherToken, 'customerEmail' => $customerEmail, 'offset' => $offset, 'limit' => $limit),
            new Cleeng_Entity_Collection('Cleeng_Entity_CustomerSubscription')
        );
    }

    /**
     *
     * Publisher API: getPublisher()
     *
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_Publisher
     */
    public function getPublisher()
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'getPublisher',
            array('publisherToken' => $publisherToken),
            new Cleeng_Entity_Publisher()
        );
    }

    /**
     * Single Offer API: getSingleOffer
     *
     * @param string $offerId
     * @return Cleeng_Entity_SingleOffer
     */
    public function getSingleOffer($offerId)
    {
        $offer = new Cleeng_Entity_SingleOffer();
        return $this->api('getSingleOffer', array('offerId' => $offerId), $offer);
    }

    /**
     * Single Offer API: listSingleOffers
     *
     * @param array $criteria
     * @param int $offset
     * @param int $limit
     *
     * @return Cleeng_Entity_Collection
     */
    public function listSingleOffers($criteria = array(), $offset = 0, $limit = 20)
    {
        $collection = new Cleeng_Entity_Collection('Cleeng_Entity_SingleOffer');
        return $this->api(
            'listSingleOffers',
            array(
                'publisherToken' => $this->getPublisherToken(),
                'criteria' => $criteria,
                'offset' => $offset,
                'limit' => $limit,
            ),
            $collection
        );
    }

    /**
     * Single Offer API: createSingleOffer
     *
     * @param array $offerData
     * @return Cleeng_Entity_SingleOffer
     * @throws Cleeng_Exception_RuntimeException
     */
    public function createSingleOffer($offerData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'createSingleOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerData' => $offerData
            ),
            new Cleeng_Entity_SingleOffer()
        );
    }

    /**
     * Single Offer API: updateSingleOffer
     *
     * @param string $offerId
     * @param array $offerData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_SingleOffer
     */
    public function updateSingleOffer($offerId, $offerData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'updateSingleOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerId' => $offerId,
                'offerData' => $offerData,
            ),
            new Cleeng_Entity_SingleOffer()
        );
    }

    /**
     * Single Offer API: deactivateSingleOffer
     *
     * @param string $offerId
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_SingleOffer
     */
    public function deactivateSingleOffer($offerId)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'deactivateSingleOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerId' => $offerId,
            ),
            new Cleeng_Entity_SingleOffer()
        );
    }

    /**
     * Single Offer API: createMultiCurrencySingleOffer
     *
     * @param array $offerData
     * @param $localizedData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_Base
     */
    public function createMultiCurrencySingleOffer($offerData, $localizedData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'createMultiCurrencySingleOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerData' => $offerData,
                'localizedData' => $localizedData,
            ),
            new Cleeng_Entity_MultiCurrencyOffer()
        );
    }

    /**
     * Single Offer API: updateMultiCurrencySingleOffer
     *
     * @param $multiCurrencyOfferId
     * @param array $offerData
     * @param $localizedData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_Base
     */
    public function updateMultiCurrencySingleOffer($multiCurrencyOfferId, $offerData, $localizedData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'updateMultiCurrencySingleOffer',
            array(
                'publisherToken' => $publisherToken,
                'multiCurrencyOfferId' => $multiCurrencyOfferId,
                'offerData' => $offerData,
                'localizedData' => $localizedData,
            ),
            new Cleeng_Entity_MultiCurrencyOffer()
        );
    }

    /**
     * Event Offer API: getEventOffer
     *
     * @param string $offerId
     * @return Cleeng_Entity_EventOffer
     */
    public function getEventOffer($offerId)
    {
        $offer = new Cleeng_Entity_EventOffer();
        return $this->api('getEventOffer', array('offerId' => $offerId), $offer);
    }

    /**
     * Event Offer API: listEventOffers
     *
     * @param array $criteria
     * @param int $offset
     * @param int $limit
     *
     * @return Cleeng_Entity_Collection
     */
    public function listEventOffers($criteria = array(), $offset = 0, $limit = 20)
    {
        $collection = new Cleeng_Entity_Collection('Cleeng_Entity_EventOffer');
        return $this->api(
            'listEventOffers',
            array(
                'publisherToken' => $this->getPublisherToken(),
                'criteria' => $criteria,
                'offset' => $offset,
                'limit' => $limit,
            ),
            $collection
        );
    }

    /**
     * Event Offer API: createEventOffer
     *
     * @param array $offerData
     * @return Cleeng_Entity_EventOffer
     * @throws Cleeng_Exception_RuntimeException
     */
    public function createEventOffer($offerData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'createEventOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerData' => $offerData
            ),
            new Cleeng_Entity_EventOffer()
        );
    }

    /**
     * Event Offer API: updateEventOffer
     *
     * @param string $offerId
     * @param array $offerData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_EventOffer
     */
    public function updateEventOffer($offerId, $offerData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'updateEventOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerId' => $offerId,
                'offerData' => $offerData,
            ),
            new Cleeng_Entity_EventOffer()
        );
    }

    /**
     * Event Offer API: cancelEventOffer
     *
     * @param string $offerId
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_EventOffer
     */
    public function cancelEventOffer($offerId)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'cancelEventOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerId' => $offerId,
            ),
            new Cleeng_Entity_EventOffer()
        );
    }

    /**
     * Rental Offer API: getRentalOffer
     *
     * @param string $offerId
     * @return Cleeng_Entity_RentalOffer
     */
    public function getRentalOffer($offerId)
    {
        $offer = new Cleeng_Entity_RentalOffer();
        return $this->api('getRentalOffer', array('offerId' => $offerId), $offer);
    }

    /**
     * Rental Offer API: listRentalOffers
     *
     * @param array $criteria
     * @param int $offset
     * @param int $limit
     *
     * @return Cleeng_Entity_Collection
     */
    public function listRentalOffers($criteria = array(), $offset = 0, $limit = 20)
    {
        $collection = new Cleeng_Entity_Collection('Cleeng_Entity_RentalOffer');
        return $this->api(
            'listRentalOffers',
            array(
                'publisherToken' => $this->getPublisherToken(),
                'criteria' => $criteria,
                'offset' => $offset,
                'limit' => $limit,
            ),
            $collection
        );
    }

    /**
     * Rental Offer API: createRentalOffer
     *
     * @param array $offerData
     * @return Cleeng_Entity_SingleOffer
     * @throws Cleeng_Exception_RuntimeException
     */
    public function createRentalOffer($offerData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'createRentalOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerData' => $offerData
            ),
            new Cleeng_Entity_RentalOffer()
        );
    }

    /**
     * Rental Offer API: updateRentalOffer
     *
     * @param string $offerId
     * @param array $offerData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_RentalOffer
     */
    public function updateRentalOffer($offerId, $offerData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'updateRentalOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerId' => $offerId,
                'offerData' => $offerData,
            ),
            new Cleeng_Entity_RentalOffer()
        );
    }

    /**
     * Rental Offer API: deactivateRentalOffer
     *
     * @param string $offerId
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_RentalOffer
     */
    public function deactivateRentalOffer($offerId)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'deactivateRentalOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerId' => $offerId,
            ),
            new Cleeng_Entity_RentalOffer()
        );
    }

    /**
     * Rental Offer API: createMultiCurrencyRentalOffer
     *
     * @param array $offerData
     * @param $localizedData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_RentalOffer
     */
    public function createMultiCurrencyRentalOffer($offerData, $localizedData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'createMultiCurrencyRentalOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerData' => $offerData,
                'localizedData' => $localizedData,
            ),
            new Cleeng_Entity_MultiCurrencyOffer()
        );
    }

    /**
     * Rental Offer API: updateMultiCurrencyRentalOffer
     *
     * @param $multiCurrencyOfferId
     * @param array $offerData
     * @param $localizedData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_Base
     */
    public function updateMultiCurrencyRentalOffer($multiCurrencyOfferId, $offerData, $localizedData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'updateMultiCurrencyRentalOffer',
            array(
                'publisherToken' => $publisherToken,
                'multiCurrencyOfferId' => $multiCurrencyOfferId,
                'offerData' => $offerData,
                'localizedData' => $localizedData,
            ),
            new Cleeng_Entity_MultiCurrencyOffer()
        );
    }

    /**
     * Subscription Offer API: getSubscriptionOffer
     *
     * @param string $offerId
     * @return Cleeng_Entity_SubscriptionOffer
     */
    public function getSubscriptionOffer($offerId)
    {
        $offer = new Cleeng_Entity_SubscriptionOffer();
        return $this->api('getSubscriptionOffer', array('offerId' => $offerId), $offer);
    }


    /**
     * Subscription Offer API: listSubscriptionOffers
     *
     * @param array $criteria
     * @param int $offset
     * @param int $limit
     *
     * @return Cleeng_Entity_Collection
     */
    public function listSubscriptionOffers($criteria = array(), $offset = 0, $limit = 20)
    {
        $collection = new Cleeng_Entity_Collection('Cleeng_Entity_SubscriptionOffer');
        return $this->api(
            'listSubscriptionOffers',
            array(
                'publisherToken' => $this->getPublisherToken(),
                'criteria' => $criteria,
                'offset' => $offset,
                'limit' => $limit,
            ),
            $collection
        );
    }

    /**
     * Subscription Offer API: createSubscriptionOffer
     *
     * @param array $offerData
     * @return Cleeng_Entity_SubscriptionOffer
     * @throws Cleeng_Exception_RuntimeException
     */
    public function createSubscriptionOffer($offerData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'createSubscriptionOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerData' => $offerData
            ),
            new Cleeng_Entity_SubscriptionOffer()
        );
    }

    /**
     * Subscription Offer API: updateSubscriptionOffer
     *
     * @param string $offerId
     * @param array $offerData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_SubscriptionOffer
     */
    public function updateSubscriptionOffer($offerId, $offerData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'updateSubscriptionOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerId' => $offerId,
                'offerData' => $offerData,
            ),
            new Cleeng_Entity_SubscriptionOffer()
        );
    }

    /**
     * Subscription Offer API: deactivateSubscriptionOffer
     *
     * @param string $offerId
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_SubscriptionOffer
     */
    public function deactivateSubscriptionOffer($offerId)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'deactivateSubscriptionOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerId' => $offerId,
            ),
            new Cleeng_Entity_SubscriptionOffer()
        );
    }

    /**
     * Subscription Offer API: createMultiCurrencySubscriptionOffer
     *
     * @param array $offerData
     * @param $localizedData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_MultiCurrencyOffer
     */
    public function createMultiCurrencySubscriptionOffer($offerData, $localizedData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'createMultiCurrencySubscriptionOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerData' => $offerData,
                'localizedData' => $localizedData,
            ),
            new Cleeng_Entity_MultiCurrencyOffer()
        );
    }


    /**
     * Subscription Offer API: updateMultiCurrencySubscriptionOffer
     *
     * @param string $multiCurrencyOfferId
     * @param array $offerData
     * @param $localizedData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_MultiCurrencyOffer
     */
    public function updateMultiCurrencySubscriptionOffer($multiCurrencyOfferId, $offerData, $localizedData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'updateMultiCurrencySubscriptionOffer',
            array(
                'publisherToken' => $publisherToken,
                'multiCurrencyOfferId' => $multiCurrencyOfferId,
                'offerData' => $offerData,
                'localizedData' => $localizedData,
            ),
            new Cleeng_Entity_MultiCurrencyOffer()
        );
    }


    /**
     * Pass Offer API: getPassOffer
     *
     * @param string $offerId
     * @return Cleeng_Entity_PassOffer
     */
    public function getPassOffer($offerId)
    {
        $offer = new Cleeng_Entity_PassOffer();
        return $this->api('getPassOffer', array('offerId' => $offerId), $offer);
    }


    /**
     * Pass Offer API: listPassOffers
     *
     * @param array $criteria
     * @param int $offset
     * @param int $limit
     *
     * @return Cleeng_Entity_Collection
     */
    public function listPassOffers($criteria = array(), $offset = 0, $limit = 20)
    {
        $collection = new Cleeng_Entity_Collection('Cleeng_Entity_PassOffer');
        return $this->api(
            'listPassOffers',
            array(
                'publisherToken' => $this->getPublisherToken(),
                'criteria' => $criteria,
                'offset' => $offset,
                'limit' => $limit,
            ),
            $collection
        );
    }

    /**
     * Pass Offer API: createPassOffer
     *
     * @param array $offerData
     * @return Cleeng_Entity_PassOffer
     * @throws Cleeng_Exception_RuntimeException
     */
    public function createPassOffer($offerData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'createPassOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerData' => $offerData
            ),
            new Cleeng_Entity_PassOffer()
        );
    }

    /**
     * Pass Offer API: updatePassOffer
     *
     * @param string $offerId
     * @param array $offerData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_PassOffer
     */
    public function updatePassOffer($offerId, $offerData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'updatePassOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerId' => $offerId,
                'offerData' => $offerData,
            ),
            new Cleeng_Entity_PassOffer()
        );
    }

    /**
     * Pass Offer API: deactivatePassOffer
     *
     * @param string $offerId
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_PassOffer
     */
    public function deactivatePassOffer($offerId)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'deactivatePassOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerId' => $offerId,
            ),
            new Cleeng_Entity_PassOffer()
        );
    }

    /**
     * Pass Offer API: createMultiCurrencyPassOffer
     *
     * @param array $offerData
     * @param $localizedData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_MultiCurrencyOffer
     */
    public function createMultiCurrencyPassOffer($offerData, $localizedData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'createMultiCurrencyPassOffer',
            array(
                'publisherToken' => $publisherToken,
                'offerData' => $offerData,
                'localizedData' => $localizedData,
            ),
            new Cleeng_Entity_MultiCurrencyOffer()
        );
    }


    /**
     * Pass Offer API: updateMultiCurrencyPassOffer
     *
     * @param string $multiCurrencyOfferId
     * @param array $offerData
     * @param $localizedData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_MultiCurrencyOffer
     */
    public function updateMultiCurrencyPassOffer($multiCurrencyOfferId, $offerData, $localizedData)
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'updateMultiCurrencyPassOffer',
            array(
                'publisherToken' => $publisherToken,
                'multiCurrencyOfferId' => $multiCurrencyOfferId,
                'offerData' => $offerData,
                'localizedData' => $localizedData,
            ),
            new Cleeng_Entity_MultiCurrencyOffer()
        );
    }

    /**
     * Associate API: getAssociate
     *
     * @param $associateEmail
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_Associate
     */
    public function getAssociate($associateEmail)
    {
        $distributorToken = $this->getDistributorToken();
        if (!$distributorToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setDistributorToken must be used first.");
        }
        return $this->api(
            'getAssociate',
            array('distributorToken' => $distributorToken, 'associateEmail' => $associateEmail),
            new Cleeng_Entity_Associate()
        );
    }

    /**
     * Associate API: createAssociate
     *
     * @param $associateData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_Associate
     */
    public function createAssociate($associateData)
    {
        $distributorToken = $this->getDistributorToken();
        if (!$distributorToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setDistributorToken must be used first.");
        }
        return $this->api(
            'createAssociate',
            array('distributorToken' => $distributorToken, 'associateData' => $associateData),
            new Cleeng_Entity_Associate()
        );
    }

    /**
     * Associate API: updateAssociate
     *
     * @param $associateEmail
     * @param $associateData
     * @throws Cleeng_Exception_RuntimeException
     * @return Cleeng_Entity_Associate
     */
    public function updateAssociate($associateEmail, $associateData)
    {
        $distributorToken = $this->getDistributorToken();
        if (!$distributorToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setDistributorToken must be used first.");
        }
        return $this->api(
            'updateAssociate',
            array('distributorToken' => $distributorToken, 'associateEmail' => $associateEmail, 'associateData' => $associateData),
            new Cleeng_Entity_Associate()
        );
    }

    /**
     * List Transactions API: listTransactions
     *
     * @param array $criteria
     * @param int $offset
     * @param int $limit
     *
     * @return Cleeng_Entity_Collection
     */
    public function listTransactions($criteria = array(), $offset = 0, $limit = 20)
    {
        $collection = new Cleeng_Entity_Collection('Cleeng_Entity_Base');
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }

        return $this->api(
            'listTransactions',
            array(
                'publisherToken' => $this->getPublisherToken(),
                'criteria' => $criteria,
                'offset' => $offset,
                'limit' => $limit,
            ),
            $collection
        );
    }

    /**
     * @param array $criteria
     * @param int $offset
     * @param int $limit
     * @return Cleeng_Entity_Collection
     * @throws Cleeng_Exception_RuntimeException
     */
    public function listCustomerLibrary($customerEmail = null, $criteria = array(), $offset = 0, $limit = 20)
    {
        $collection = new Cleeng_Entity_Collection('Cleeng_Entity_CustomerLibrary');
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'listCustomerLibrary',
            array(
                'publisherToken' => $publisherToken,
                'customerEmail' => $customerEmail,
                'criteria' => $criteria,
                'offset' => $offset,
                'limit' => $limit
            ),
            $collection
        );
    }

    /**
     * Wrapper for getAccessStatus method
     *
     * @param $offerId
     * @param string $ipAddress
     * @return bool
     */
    public function isAccessGranted($offerId, $ipAddress='')
    {
        return $this->getAccessStatus($offerId, $ipAddress)->accessGranted;
    }


    /**
     * Coupon campaign API: createCouponCampaign
     * @param $campaignData
     * @return Cleeng_Entity_Base
     * @throws Cleeng_Exception_RuntimeException
     */
    public function createCouponCampaign($campaignData)
    {
        $distributorToken = $this->getDistributorToken();
        if (!$distributorToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setDistributorToken must be used first.");
        }
        return $this->api(
            'createCouponCampaign',
            array('distributorToken' => $distributorToken, 'campaignData' => $campaignData),
            new Cleeng_Entity_CouponCampaign()
        );
    }

    /**
     * Coupon campaign API: createSubscriptionCouponCampaign
     * @param $campaignData
     * @return Cleeng_Entity_Base
     * @throws Cleeng_Exception_RuntimeException
     */
    public function createSubscriptionCouponCampaign($campaignData)
    {
        $distributorToken = $this->getDistributorToken();
        if (!$distributorToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setDistributorToken must be used first.");
        }
        return $this->api(
            'createSubscriptionCouponCampaign',
            array('distributorToken' => $distributorToken, 'campaignData' => $campaignData),
            new Cleeng_Entity_SubscriptionCouponCampaign()
        );
    }

    /**
     * Coupon campaign API: activateCouponCampaign
     * @param $campaignId
     * @return Cleeng_Entity_Base
     * @throws Cleeng_Exception_RuntimeException
     */
    public function activateCouponCampaign($campaignId)
    {
        $distributorToken = $this->getDistributorToken();
        if (!$distributorToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setDistributorToken must be used first.");
        }
        return $this->api(
            'activateCouponCampaign',
            array('distributorToken' => $distributorToken, 'campaignId' => $campaignId),
            new Cleeng_Entity_OperationStatus()
        );
    }

    /**
     * Coupon campaign API: deactivateCouponCampaign
     * @param $campaignId
     * @return Cleeng_Entity_Base
     * @throws Cleeng_Exception_RuntimeException
     */
    public function deactivateCouponCampaign($campaignId)
    {
        $distributorToken = $this->getDistributorToken();
        if (!$distributorToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setDistributorToken must be used first.");
        }
        return $this->api(
            'deactivateCouponCampaign',
            array('distributorToken' => $distributorToken, 'campaignId' => $campaignId),
            new Cleeng_Entity_OperationStatus()
        );
    }

    /**
     * Coupon campaign API: listCouponCampaigns
     * @param $criteria
     * @param $offset
     * @param $limit
     * @return Cleeng_Entity_Base
     * @throws Cleeng_Exception_RuntimeException
     */
    public function listCouponCampaigns($criteria, $offset, $limit)
    {

        $distributorToken = $this->getDistributorToken();
        if (!$distributorToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setDistributorToken must be used first.");
        }

        $collection = new Cleeng_Entity_Collection('Cleeng_Entity_ListCouponCampaign');

        return $this->api(
            'listCouponCampaigns',
            array(
                'distributorToken' => $distributorToken,
                'criteria' => $criteria,
                'offset' => $offset,
                'limit' => $limit
            ),
            $collection
        );
    }

    /**
     * Coupon campaign API: listSubscriptionCouponCampaigns
     * @param $criteria
     * @param $offset
     * @param $limit
     * @return Cleeng_Entity_Base
     * @throws Cleeng_Exception_RuntimeException
     */
    public function listSubscriptionCouponCampaigns($criteria, $offset, $limit)
    {
        $distributorToken = $this->getDistributorToken();
        if (!$distributorToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setDistributorToken must be used first.");
        }

        $collection = new Cleeng_Entity_Collection('Cleeng_Entity_ListSubscriptionCouponCampaign');

        return $this->api(
            'listSubscriptionCouponCampaigns',
            array(
                'distributorToken' => $distributorToken,
                'criteria' => $criteria,
                'offset' => $offset,
                'limit' => $limit
            ),
            $collection
        );
    }

    /**
     * Coupon campaign API: listCoupons
     * @param $criteria
     * @return Cleeng_Entity_Base
     * @throws Cleeng_Exception_RuntimeException
     */
    public function listCoupons($criteria)
    {
        $distributorToken = $this->getDistributorToken();
        if (!$distributorToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setDistributorToken must be used first.");
        }

        $collection = new Cleeng_Entity_Collection('Cleeng_Entity_Base');

        return $this->api(
            'listCoupons',
            array('distributorToken' => $distributorToken, 'criteria' => $criteria),
            $collection
        );
    }

    /**
     * Coupon campaign API: importCoupons
     * @param $campaignId
     * @param array $coupons
     * @return Cleeng_Entity_Base
     * @throws Cleeng_Exception_RuntimeException
     */
    public function importCoupons($campaignId, $coupons = array())
    {
        $distributorToken = $this->getDistributorToken();
        if (!$distributorToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setDistributorToken must be used first.");
        }
        return $this->api(
            'importCoupons',
            array(
                'distributorToken' => $distributorToken,
                'campaignId' => $campaignId,
                'coupons' => $coupons
            ),
            new Cleeng_Entity_OperationStatus()
        );
    }

    /**
     * Coupon campaign API: applyCoupon
     * @param $customerEmail
     * @param $couponCode
     * @param $offerId
     * @param array $couponOptions
     * @return Cleeng_Entity_Base
     * @throws Cleeng_Exception_RuntimeException
     */
    public function applyCoupon($customerEmail, $couponCode, $offerId, $couponOptions = array())
    {
        $publisherToken = $this->getPublisherToken();
        if (!$publisherToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setPublisherToken must be used first.");
        }
        return $this->api(
            'applyCoupon',
            array(
                'publisherToken' => $publisherToken,
                'customerEmail' => $customerEmail,
                'couponCode' => $couponCode,
                'offerId' => $offerId,
                'couponOptions' => $couponOptions
            ),
            new Cleeng_Entity_OperationStatus()

        );
    }

    /**
     * Coupon campaign API: grantAccessWithCoupon
     * @param $customerEmail
     * @param $couponCode
     * @param $offerId
     * @param null $couponOptions
     * @return Cleeng_Entity_Base
     * @throws Cleeng_Exception_RuntimeException
     */
    public function grantAccessWithCoupon($customerEmail, $couponCode, $offerId, $couponOptions = null)
    {
        $distributorToken = $this->getDistributorToken();
        if (!$distributorToken) {
            throw new Cleeng_Exception_RuntimeException("Cannot call " . __FUNCTION__ . ": setDistributorToken must be used first.");
        }
        return $this->api(
            'grantAccessWithCoupon',
            array(
                'publisherToken' => $distributorToken,
                'customerEmail' => $customerEmail,
                'couponCode' => $couponCode,
                'offerId' => $offerId,
                'couponOptions' => $couponOptions
            ),
            new Cleeng_Entity_OperationStatus()
        );
    }


}
