<?php

class Cleeng_Api
{

    /**
     * API endpoint for Cleeng Sandbox
     */
    const SANDBOX_ENDPOINT  = 'https://sandbox.cleeng.com/api/2.1/json-rpc';

    /**
     * API endpoint
     *
     * @var string
     */
    protected $endpoint = 'https://api.cleeng.com/2.1/json-rpc';

    /**
     * Transport class used to communicate with Cleeng servers
     *
     * @var Cleeng_Transport_AbstractTransport
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
     * Customer's access token - should be read automatically from cookie
     * @var string
     */
    protected $customerToken;

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
     * Last response from Cleeng server
     *
     * @var string
     */
    protected $rawResponse;

    /**
     * Send request to Cleeng API or put it on a list (batch mode)
     *
     * @param string $method
     * @param array $params
     * @param Cleeng_Entity_Base $objectToPopuplate
     * @return Cleeng_Entity_Base
     */
    public function api($method, $params = array(), $objectToPopuplate = null)
    {
        $id = count($this->pendingCalls)+1;
        $payload = json_encode(
            array(
                'jsonrpc' => '2.0',
                'id' => $id,
                'method' => $method,
                'params' => $params
            )
        );

        if (null === $objectToPopuplate) {
            $objectToPopuplate = new Cleeng_Entity_Base();
        }

        $this->pendingCalls[$id] = array(
            'entity' => $objectToPopuplate,
            'payload' => $payload
        );

        if (!$this->batchMode) {
            // batch requests disabled, send request
            $this->commit();
        }

        return $objectToPopuplate;
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
        }

        $encodedRequest = '[' . implode(',', $requestData) . ']';
        $raw = $this->getTransport()->call($this->getEndpoint(), $encodedRequest);
        $this->rawResponse = $raw;
        $decodedResponse = json_decode($raw, true);

        if (!$decodedResponse) {
            throw new Cleeng_Exception_InvalidJsonException("Expected valid JSON string, received: $raw");
        }

        foreach ($decodedResponse as $response) {

            if (!isset($response['id'])) {
                throw new Cleeng_Exception_RuntimeException("Invalid response from API - missing JSON-RPC ID.");
            }

            if (isset($this->pendingCalls[$response['id']])) {
                $transferObject = $this->pendingCalls[$response['id']]['entity'];
                $transferObject->pending = false;

                if ($response['error']) {
                    throw new Cleeng_Exception_ApiErrorException($response['error']['message']);
                } else {
                    if (!is_array($response['result'])) {
                        throw new Cleeng_Exception_ApiErrorException(
                            "Invalid response type received from API. Expected array, got "
                                . getType($response['result']) . '.'
                        );
                    }
                    $transferObject->populate($response['result']);
                }
            }
        }
        $this->pendingCalls = array();
    }

    /**
     * @param string $endpoint
     * @return Cleeng_Api provides fluent interface
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

    /**
     * Helper function for setting up test environment
     */
    public function enableSandbox()
    {
        $this->setEndpoint(self::SANDBOX_ENDPOINT);
    }

    /**
     * @param \Cleeng_Transport_AbstractTransport $transport
     * @return Cleeng_Api provides fluent interface
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;
        return $this;
    }


    /**
     * Return transport object or create new (curl-based)
     *
     * @return Cleeng_Transport_AbstractTransport
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
     * Class constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
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
     * @return Cleeng_Client provides fluent interface
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
            }
        }
        return $this->customerToken;
    }

    /**
     * Set publisher's token
     *
     * @param string $publisherToken
     * @return Cleeng_Client provides fluent interface
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
     * Cleeng Query API: getSingleOffer
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
     * Cleeng Query API: getPublisherSingleOffers
     *
     * @param $publisherId
     * @param array $criteria
     * @param int $page
     * @param int $itemsPerPage
     *
     * @return \Cleeng_Entity_Collection
     */
    public function getPublisherSingleOffers($publisherId, $criteria = array(), $page = 1, $itemsPerPage = 20)
    {
        $collection = new Cleeng_Entity_Collection('Cleeng_Entity_SingleOffer');
        return $this->api(
            'getPublisherSingleOffers',
            array(
                'publisherId' => $publisherId,
                'criteria' => $criteria,
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
            ),
            $collection
        );
    }

    /**
     * Cleeng Query API: getRentalOffer
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
     * Cleeng Query API: getPublisherRentalOffers
     *
     * @param $publisherId
     * @param array $criteria
     * @param int $page
     * @param int $itemsPerPage
     *
     * @return \Cleeng_Entity_Collection
     */
    public function getPublisherRentalOffers($publisherId, $criteria = array(), $page = 1, $itemsPerPage = 20)
    {
        $collection = new Cleeng_Entity_Collection('Cleeng_Entity_RentalOffer');
        return $this->api(
            'getPublisherRentalOffers',
            array(
                'publisherId' => $publisherId,
                'criteria' => $criteria,
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
            ),
            $collection
        );
    }

    /**
     * Cleeng Query API: getSubscriptionOffer
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
     * Cleeng Query API: getPublisherSubscriptionOffers
     *
     * @param $publisherId
     * @param array $criteria
     * @param int $page
     * @param int $itemsPerPage
     *
     * @return \Cleeng_Entity_Collection
     */
    public function getPublisherSubscriptionOffers($publisherId, $criteria = array(), $page = 1, $itemsPerPage = 20)
    {
        $collection = new Cleeng_Entity_Collection('Cleeng_Entity_SubscriptionOffer');
        return $this->api(
            'getPublisherSubscriptionOffers',
            array(
                'publisherId' => $publisherId,
                'criteria' => $criteria,
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
            ),
            $collection
        );
    }

    /**
     * Cleeng Customer API: getCustomerInfo()
     *
     */
    public function getCustomerInfo()
    {
        $userInfo = new Cleeng_Entity_Customer();
        return $this->api('getCustomer', array('customerToken' => $this->getCustomerToken()), $userInfo);
    }

    /**
     * Cleeng Publisher API: getPublisherInfo()
     *
     */
    public function getPublisherInfo()
    {
        $userInfo = new Cleeng_Entity_Publisher();
        return $this->api('getPublisher', array('publisherToken' => $this->getPublisherToken()), $userInfo);
    }

}
