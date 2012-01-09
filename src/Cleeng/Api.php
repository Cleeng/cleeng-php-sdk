<?php

class Cleeng_Api
{

    /**
     * @var string
     */
    protected $platformUrl = 'cleeng.com';

    /**
     * Transport class used to communicate with Cleeng servers
     *
     * @var Cleeng_AbstractTransport
     */
    protected $transport;

    /**
     * Publisher's token - must be set manually with setPublisherToken()
     *
     * @var string
     */
    protected $publisherToken = '';

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
     * If set to false, API client wont make any requests to server automatically
     *
     * @var bool
     */
    protected $autocommitPublisherApis = true;

    /**
     * Set if any publisher APIs are queued
     *
     * @var bool
     */
    protected $publisherApiCallPending = false;

    /**
     * "Default" application ID. Usually there's no need to change that.
     *
     * @var string
     */
    protected $appId = '35e97a6231236gb456heg6bd7a6bdsf7';

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
     * Commit API request if any Publisher API calls are pending.
     */
    public function processPendingPublisherApis()
    {
        if ($this->publisherApiCallPending) {
            $this->getTransport()->commit();
        }
        $this->publisherApiCallPending = false;
    }

    /**
     * Executes queued API calls (if any are waiting)
     */
    public function commit()
    {
        $this->getTransport()->commit();
    }

    /**
     * Cleeng Query API: getItemOffer
     *
     * @param int $itemOfferId
     * @return Cleeng_TransferObject
     */
    public function getItemOffer($itemOfferId)
    {
        $this->processPendingPublisherApis();
        return $this->getTransport()->call('customer', 'getItemOffer',
            array('itemOfferId' => $itemOfferId));
    }

    /**
     * Cleeng Query API: getUserInfo
     *
     * @return Cleeng_TransferObject
     */
    public function getUserInfo()
    {
        $this->processPendingPublisherApis();
        return $this->getTransport()->call('customer', 'getUserInfo',
            array('token' => $this->getCustomerToken()));
    }

    /**
     * Cleeng Query API: getAccessStatus
     *
     * @param int $itemOfferId
     * @return Cleeng_TransferObject
     */
    public function getAccessStatus($itemOfferId)
    {
        $this->processPendingPublisherApis();
        return $this->getTransport()->call('customer', 'getAccessStatus',
            array('token' => $this->getCustomerToken(), 'itemOfferId' => $itemOfferId));
    }

    /**
     * Cleeng Query API: isAccessGranted
     *
     * Wrapper for getAccessStatus. Return true
     *
     * @param int $itemOfferId
     * @return Cleeng_TransferObject
     */
    public function isAccessGranted($itemOfferId)
    {
        $this->processPendingPublisherApis();
        $accessStatus = $this->getAccessStatus($itemOfferId);
        return $accessStatus->accessGranted;
    }

    /**
     * Cleeng Publisher API: createItemOffer
     *
     * @param array $itemOfferData
     * @return Cleeng_TransferObject
     */
    public function createItemOffer($itemOfferData)
    {
        $this->publisherApiCallPending = true;
        $itemOffer = $this->getTransport()->call('publisher', 'createItemOffer',
            array('token' => $this->publisherToken,
                  'itemOfferData' => $itemOfferData));
        if ($this->autocommitPublisherApis) {
            $this->commit();
        } else {
            $this->publisherApiCallPending = true;
        }
        return $itemOffer;
    }

    /**
     * Cleeng Publisher API: updateItemOffer
     *
     * @param array $itemOfferData
     * @param int $itemOfferId
     * @return Cleeng_TransferObject
     */
    public function updateItemOffer($itemOfferId, $itemOfferData)
    {
        $itemOffer = $this->getTransport()->call('publisher', 'updateItemOffer',
            array('token' => $this->publisherToken,
                  'itemOfferId' => $itemOfferId,
                  'itemOfferData' => $itemOfferData));
        if ($this->autocommitPublisherApis) {
            $this->commit();
        } else {
            $this->publisherApiCallPending = true;
        }
        return $itemOffer;
    }

    /**
     * Cleeng Publisher API: removeItemOffer
     *
     * @param int $itemOfferId
     * @return Cleeng_TransferObject
     */
    public function removeItemOffer($itemOfferId)
    {
        $ret = $this->getTransport()->call(
            'publisher', 'removeItemOffer',
            array('token' => $this->getPublisherToken(), 'itemOfferId' => $itemOfferId)
        );
        if ($this->autocommitPublisherApis) {
            $this->commit();
        } else {
            $this->publisherApiCallPending = true;
        }
        return $ret;
    }

    /**
     * Return transport object or create new (curl-based)
     *
     * @return Cleeng_AbstractTransport
     */
    public function getTransport()
    {
        if (null === $this->transport) {
            $this->transport = new Cleeng_Transport_Curl($this->platformUrl);
        }
        return $this->transport;
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
     * Sets URL to Cleeng platform.
     *
     * Use sandbox.cleeng.com for testing, and cleeng.com for production
     *
     * @param string $platformUrl
     * @return Cleeng_Client provides fluent interface
     */
    public function setPlatformUrl($platformUrl)
    {
        $this->platformUrl = $platformUrl;
        return $this;
    }

    /**
     * Returns platform URL
     *
     * @return string
     */
    public function getPlatformUrl()
    {
        return $this->platformUrl;
    }

    /**
     * @param bool $flag
     */
    public function setAutocommitPublisherApis($flag)
    {
        $this->autocommitPublisherApis = $flag;
    }

}