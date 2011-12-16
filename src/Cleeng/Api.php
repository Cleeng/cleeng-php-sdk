<?php

class Cleeng_Api
{

    /**
     * @var string
     */
    protected $platformUrl = 'cleeng.com';

    /**
     * @var Cleeng_AbstractTransport
     */
    protected $transport;

    /**
     * @var string
     */
    protected $publisherToken = '';

    /**
     * @var string
     */
    protected $customerToken = '';

    protected $cookieName = 'CleengClientAccessToken';

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
     * @return Cleeng_AbstractTransport
     */
    public function getTransport()
    {
        if (null === $this->transport) {
            $this->transport = new Cleeng_Transport_Curl($this->platformUrl);
        }
        return $this->transport;
    }

    public function getItemOffer($itemOfferId)
    {
        return $this->getTransport()->call('customer', 'getItemOffer', array('itemOfferId' => $itemOfferId));
    }

    public function getUserInfo()
    {
        return $this->getTransport()->call('customer', 'getUserInfo', array('token' => $this->getCustomerToken()));
    }

    public function createItemOffer($itemOfferData)
    {
        return $this->getTransport()->call('publisher', 'createItemOffer', array('token' => $this->publisherToken, 'itemOfferData' => $itemOfferData));
    }

    public function updateItemOffer($itemOfferId, $itemOfferData)
    {
        return $this->getTransport()->call('publisher', 'updateItemOffer', array('token' => $this->publisherToken, 'itemOfferId' => $itemOfferId, 'itemOfferData' => $itemOfferData));
    }

    public function getAccessStatus($itemOfferId)
    {
        return $this->getTransport()->call('customer', 'getAccessStatus', array('token' => $this->getCustomerToken(), 'itemOfferId' => $itemOfferId));
    }

    public function isAccessGranted($itemOfferId)
    {
        $accessStatus = $this->getAccessStatus($itemOfferId);
        return $accessStatus->accessGranted;
    }

    /**
     * @param string $customerToken
     * @return Cleeng_Client provides fluent interface
     */
    public function setCustomerToken($customerToken)
    {
        $this->customerToken = $customerToken;
        return $this;
    }

    /**
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
     * @param string $publisherToken
     * @return Cleeng_Client provides fluent interface
     */
    public function setPublisherToken($publisherToken)
    {
        $this->publisherToken = $publisherToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getPublisherToken()
    {
        return $this->publisherToken;
    }

    /**
     * @param string $platformUrl
     */
    public function setPlatformUrl($platformUrl)
    {
        $this->platformUrl = $platformUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlatformUrl()
    {
        return $this->platformUrl;
    }


}