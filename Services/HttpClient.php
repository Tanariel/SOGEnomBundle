<?php

/*
 * This file is part of the SOG/EnomBundle
 *
 * (c) Shane O'Grady <shane.ogrady@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SOG\EnomBundle\Services;

use SOG\EnomBundle\Services\EnomException;

/**
 * cURL client
 *
 * @author Shane O'Grady <shane.ogrady@gmail.com>
 */
class HttpClient
{
    protected $url;
    protected $username;
    protected $password;

    protected $payload = array();
    
    protected $request;
    protected $response;
    
    protected $xmlResponse;
    protected $httpCode;
    protected $curlInfo;

    /**
     * Initializes Http client
     *
     * @param string $url      Enom reseller URL
     * @param string $username Enom Account login ID
     * @param string $password Enom Account password
     */
    public function __construct($url, $username, $password)
    {
        $this->url      = $url;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Send API request to Enom
     *
     * @param string $command Enom API command/method
     * @param string $payload Request information
     *
     * @return SimpleXMLElement
     */
    protected function makeRequest($command, $payload)
    {
        $payload['command'] = $command;
        $payload['uid']     = $this->username;
        $payload['pw']      = $this->password;
        // We want to return XML and not plain text
        // A JSON response is not yet implemented by Enom
        $payload['responsetype']      = "XML";

        $url = $this->url . '/interface.asp?' . http_build_query($payload);
        $this->request = $url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "SOGEnomBundle");
        curl_setopt($ch, CURLOPT_HTTPGET, true);

        $result = curl_exec($ch);
        $this->curlInfo = curl_getinfo($ch);
        curl_close($ch);

        $this->httpCode = $this->curlInfo['http_code'];
        $this->response = $result;
        $xml = simplexml_load_string($result);
        $this->xmlResponse = $xml;

        if ((isset($xml->ErrCount)) && ((int) $xml->ErrCount > 0)) {
            throw new EnomException($xml->errors->Err1);
        }
    }
    
    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param array $payload
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getXmlResponse()
    {
        return $this->xmlResponse;
    }

    /**
     * @param mixed $xmlResponse
     */
    public function setXmlResponse($xmlResponse)
    {
        $this->xmlResponse = $xmlResponse;
    }

    /**
     * @return mixed
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @param mixed $httpCode
     */
    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
    }

    /**
     * @return mixed
     */
    public function getCurlInfo()
    {
        return $this->curlInfo;
    }

    /**
     * @param mixed $curlInfo
     */
    public function setCurlInfo($curlInfo)
    {
        $this->curlInfo = $curlInfo;
    }
}
