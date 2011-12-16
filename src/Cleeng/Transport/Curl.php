<?php

class Cleeng_Transport_Curl extends Cleeng_AbstractTransport
{
    protected $rpcId = 1;

    /**
     * @var string
     */
    protected $platformUrl;

    protected $callStack = array();

    protected $apiResponse;

    protected $apiResponseCode;

    protected $apiRequestData;

    /**
     * @param $platformUrl
     */
    public function __construct($platformUrl)
    {
        $this->platformUrl = $platformUrl;
    }

    /**
     * @param $endpoint
     * @param string $method
     * @param array $params
     * @return void
     */
    public function call($endpoint, $method, $params)
    {
        // construct API URL
//        $url = 'https://api.' . $this->platformUrl . '/2.0/json/' . $endpoint;
        $json = array(
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => $this->rpcId++
        );
        $transferObject = new Cleeng_TransferObject($this);
        $transferObject->_endpoint = $endpoint;
        $transferObject->_requestData = $json;
        $this->callStack[] = $transferObject;
        return $transferObject;
    }

    protected function _curl($url, $postData)
    {
        $this->apiRequestData = $postData;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

        /**
         * TODO: Validate certificate
         */
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $buffer = curl_exec($ch);
        $this->apiResponse = $buffer;
        $this->apiResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($this->apiResponseCode !== 200) {
            throw new Exception('Invalid HTTP response code (' . $this->apiResponseCode . ').');
        }

        return $buffer;
    }

    public function processPendingRequests()
    {
        $requestList = array();
        $idLookup = array();
        foreach ($this->callStack as $transferObject) {
            $idLookup[$transferObject->_requestData['id']] = $transferObject;
            $requestList[] = $transferObject->_requestData;
        }

        $url = 'https://api.' . $this->platformUrl . '/2.0/json-rpc';
        $json = $this->_curl($url, json_encode($requestList));

        if (!$json || !strlen($json) || !$result = json_decode($json)) {
            throw new Exception('Server response is not valid JSON string.');
        }

        foreach ($result as $response) {
            $transferObject = $idLookup[$response->id];
            $transferObject->_pending = false;
            if ($response->error) {
                $transferObject->_error = $response->error;
            } else {
                $transferObject->_error = false;
                $transferObject->setData($response->result);
            }
        }
    }

    public function getApiResponse()
    {
        return $this->apiResponse;
    }

    public function getApiResponseCode()
    {
        return $this->apiResponseCode;
    }

    public function getApiRequestData()
    {
        return $this->apiRequestData;
    }


}