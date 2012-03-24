<?php

class Cleeng_Transport_Curl extends Cleeng_Transport_Abstract
{
    /**
     * Counter that keeps track of "id" property in JSON-RPC requests
     *
     * @var int
     */
    protected $rpcId = 1;

    /**
     * URL to Cleeng platform: cleeng.com or sandbox.cleeng.com
     *
     * @var string
     */
    protected $apiEndpoint = 'https://api.cleeng.com/2.0/json-rpc';

    /**
     *
     * @var Cleeng_TransferObject[]
     */
    protected $callStack = array();

    /**
     * Response from last API call
     *
     * @var string
     */
    protected $apiResponse;

    /**
     * HTTP response code from last API call
     *
     * @var int
     */
    protected $apiResponseCode;

    /**
     * Last request sent to Cleeng servers
     *
     * @var string
     */
    protected $apiRequest;

    /**
     * CURL handle
     *
     * @var resource
     */
    protected $curl;

    /**
     * @param $apiEndpoint
     */
    public function __construct($apiEndpoint = null)
    {
        if (null !== $apiEndpoint) {
            $this->apiEndpoint = $apiEndpoint;
        }
    }

    /**
     *
     * @param string $method
     * @param array $params
     * @return Cleeng_TransferObject
     */
    public function call($method, $params)
    {
        $json = array(
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => $this->rpcId++
        );
        $transferObject = new Cleeng_TransferObject($this);
        $transferObject->_requestData = $json;
        $this->callStack[] = $transferObject;
        return $transferObject;
    }

    /**
     * Performs actual request to Cleeng servers using curl
     *
     * @param $url
     * @param $postData
     * @return string
     * @throws Exception
     */
    protected function _curl($url, $postData)
    {
        $this->apiRequest = $postData;

        if (null == $this->curl) {
            $this->curl = curl_init($url);
        }

        $ch = $this->curl;
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
            throw new Cleeng_RuntimeException('Invalid HTTP response code (' . $this->apiResponseCode . ').');
        }

        return $buffer;
    }

    /**
     * Packs pending API requests into JSON array and sends them to API endpoint.
     *
     * @throws Cleeng_RuntimeException
     */
    public function commit()
    {
        if (!count($this->callStack)) {
            return;
        }

        while (count($this->callStack)) {

            $requestList = array();
            $idLookup = array();

            $numberOfCalls = 0;
            foreach ($this->callStack as $key => $transferObject) {
                $numberOfCalls++;
                $idLookup[$transferObject->_requestData['id']] = $transferObject;
                $requestList[] = $transferObject->_requestData;
                unset($this->callStack[$key]);
                if ($numberOfCalls > 25) {
                    break;
                }
            }

            $url = $this->apiEndpoint;
            $json = $this->_curl($url, json_encode($requestList));

            if (!$json || !strlen($json) || !$result = json_decode($json, true)) {
                throw new Cleeng_RuntimeException('Server response is not valid JSON string.');
            }

            if ($json[0] == '{') {     // just in case we received single JSON object instead of an array
                $result = array($result);
            }

            $errorObject = null;

            foreach ($result as $response) {

                if (!isset($response['id']) || !isset($idLookup[$response['id']])) {
                    throw new Cleeng_RuntimeException("Unable to process server response.");
                }

                $transferObject = $idLookup[$response['id']];
                $transferObject->_pending = false;
                if ($response['error']) {

                    if (!$errorObject) {
                        $errorObject = $transferObject;
                    }

                    $transferObject->_error = $response['error'];
                } else {
                    $transferObject->_error = false;
                    $transferObject->setData($response['result']);
                }
            }

            if ($errorObject) {
                $e = new Cleeng_RuntimeException("Cleeng API error: " . $errorObject->_error['message'], $errorObject->_error['code']);
                if (isset($errorObject->_error['data']) && isset($errorObject->_error['data']['details'])) {
                    $e->details = $errorObject->_error['data']['details'];
                } else {
                    $e->details = array();
                }
                throw $e;
            }
        }

        $this->callStack = array();
    }

    /**
     * Debug/test method.
     * Returns last reposnse string received from Cleeng servers.
     *
     * @return string
     */
    public function getApiResponse()
    {
        return $this->apiResponse;
    }

    /**
     * Debug/test method.
     * Returns last HTTP response code received from Cleeng servers.
     *
     * @return int
     */
    public function getApiResponseCode()
    {
        return $this->apiResponseCode;
    }

    /**
     * Debug/test method.
     * Returns data sent to Cleeng servers with last request.
     *
     * @return string
     */
    public function getApiRequest()
    {
        return $this->apiRequest;
    }


}