<?php

/**
 * With RESTful support.
 *

 * @author    opensooq
 * @version   1.0
 *
 */
namespace opensooq\webservice;

/**
 * WebService class
 */
class WebService
{

    /**
     * @var string
     * Holds response data right after sending a request.
     */
    public $response = null;
    /**
     * @var integer HTTP-Status Code
     * This value will hold HTTP-Status Code. False if request was not successful.
     */
    public $responseCode = null;
    /**
     * @var array HTTP-Status Code
     * Custom options holder
     */
    private $_options = [];
    /**
     * @var object
     * Holds cURL-Handler
     */
    private $_curl = null;

    /**
     * @var array Build Query String
     */
    private $_data =[];
    /**
     * @var array default heaers
     * Custom options holder
     */
    private $_headers = ['Expect'=>null];
    /**
     * @var array default curl options
     * Default curl options
     */
    private $_defaultOptions = [
        CURLOPT_USERAGENT      => 'OpenSooqClient 1.0',
        CURLOPT_HTTPHEADER     => ['Expect:'],
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_BINARYTRANSFER => true,
    ];

    /**
     * Start performing GET-HTTP-Request
     *
     * @param string  $url
     * @param boolean $raw if response body contains JSON and should be decoded
     *
     * @return mixed response
     */
    public function get($url, $raw = true)
    {
        return $this->_httpRequest('GET', $this->_buildGetUrl($url), $raw);
    }
    /**
     * Start performing HEAD-HTTP-Request
     *
     * @param string $url
     *
     * @return mixed response
     */
    public function head($url)
    {
        return $this->_httpRequest('HEAD', $url);
    }
    /**
     * Start performing POST-HTTP-Request
     *
     * @param string  $url
     * @param boolean $raw if response body contains JSON and should be decoded
     *
     * @return mixed response
     */
    public function post($url, $raw = true)
    {
        $this->setOption(CURLOPT_POST, true);
        $this->setOption(CURLOPT_POSTFIELDS, $this->_getBuildQuery());
        return $this->_httpRequest('POST', $url, $raw);

    }
    /**
     * Start performing POST-HTTP-Request
     * @return $this
     */
    public function jsonPost($url, $raw = true) {

        $this->setOption(CURLOPT_POSTFIELDS, json_encode($this->_data));
        $this->setHeaders(['Content-Type'=> 'application/json','Content-Length'=> strlen(json_encode($this->_data))]);
        return $this->_httpRequest('POST', $url, $raw);

    }
    /**
     * Start performing PUT-HTTP-Request
     *
     * @param string  $url
     * @param boolean $raw if response body contains JSON and should be decoded
     *
     * @return mixed response
     */
    public function put($url, $raw = true)
    {
        return $this->_httpRequest('PUT', $url, $raw);
    }
    /**
     * Start performing DELETE-HTTP-Request
     *
     * @param string  $url
     * @param boolean $raw if response body contains JSON and should be decoded
     *
     * @return mixed response
     */
    public function delete($url, $raw = true)
    {
        return $this->_httpRequest('DELETE', $this->_buildGetUrl($url), $raw);
    }

    /**
     * Start performing GET-HTTP-Request to download file
     * @param $url
     * @param string $filePath
     * @return $this
     */
    public function downloadFile($url,$filePath){
        $this->setOption(CURLOPT_FOLLOWLOCATION, true);
        $response = $this->_httpRequest('GET', $url);

        if(file_exists($filePath)){
            unlink($filePath);
        }
        $fp = fopen($filePath,'x');
        fwrite($fp, $response);
        fclose($fp);
        return $this;

    }
    /**
     * Set curl option
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOption($key, $value)
    {
        //set value
        if (array_key_exists($key, $this->_defaultOptions) && $key !== CURLOPT_WRITEFUNCTION) {
            $this->_defaultOptions[$key] = $value;
        } else {
            $this->_options[$key] = $value;
        }
        //return self
        return $this;
    }
    /**
     * Set curl options
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->_options = $options + $this->_options;
        return $this;
    }
    /**
     * Unset a single curl option
     *
     * @param string $key
     *
     * @return $this
     */
    public function unsetOption($key)
    {
        //reset a single option if its set already
        if (isset($this->_options[$key])) {
            unset($this->_options[$key]);
        }
        return $this;
    }
    /**
     * Unset all curl option, excluding default options.
     *
     * @return $this
     */
    public function unsetOptions()
    {
        //reset all options
        if (isset($this->_options)) {
            $this->_options = [];
        }
        return $this;
    }


    /**
     * Set HTTP Build Query
     *
     * @param mixed  $data
     *
     * @return $this
     */
    public function setData($data) {
        if (!is_array($data))
            $data =[$data];
        $this->_data = array_merge($data,$this->_data);
    }

    /**
     * Unset a single Data option
     *
     * @param string $key
     *
     * @return $this
     */

    public function unsetData($key=null)
    {
        if (is_null($key)) {
            $this->_data = [];
        } elseif (isset($this->_data[$key])) {
            //reset a single option if its set already
            unset($this->_data[$key]);
        }
        return $this;
    }
    /**
     * Total reset of options, responses, etc.
     *
     * @return $this
     */
    public function reset()
    {
        if ($this->_curl !== null) {
            curl_close($this->_curl); //stop curl
        }
        //reset all options
        if (isset($this->_options)) {
            $this->_options = [];
        }
        //reset response & status code
        $this->_data        = [];
        $this->_headers     = ['Expect'=>null];
        $this->_curl        = null;
        $this->response     = null;
        $this->responseCode = null;
        return $this;
    }
    /**
     * Return a single option
     *
     * @param string|integer $key
     * @return mixed|boolean
     */
    public function getOption($key)
    {
        //get merged options depends on default and user options
        $mergesOptions = $this->getOptions();
        //return value or false if key is not set.
        return isset($mergesOptions[$key]) ? $mergesOptions[$key] : false;
    }
    /**
     * Return merged curl options and keep keys!
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options + $this->_defaultOptions;
    }

    /**
     * Generate URL-encoded query string
     *
     * @return string
     */
    private function _getBuildQuery() {
        if (!is_array($this->_data))
            $this->_data =[$this->_data];
        return http_build_query($this->_data);
    }

    /**
     * Generate GET URL-encoded query string
     * @param $url
     * @return string
     */
    private  function _buildGetUrl($url) {
        $url .=(strpos($url, '?') === false ? '?' : '&')
            .$this->_getBuildQuery();
        return $url;
    }

    /**
     * Set curl header
     * @param $name string
     * @param $value string
     * @return $this
     */
    public function setHeader($name, $value)
    {
        $this->_headers[$name]= $value;
        return $this;
    }

    /**
     *  Set curl headers
     * @param $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers + $this->_headers;
        return $this;
    }

    /**
     * return curl headers on curl format
     * @return array
     */
    public function getHeaders()
    {
        $headers= [];
        foreach ($headers as $name => $value) {
            if (is_null($value)) {
                $value='';
            }
            array_push($headers, "$name: $value");
        }
        return $headers;
    }


    /**
     * Get curl info according to http://php.net/manual/de/function.curl-getinfo.php
     *
     * @return mixed
     */
    public function getInfo($opt = null)
    {
        if ($this->_curl !== null && $opt === null) {
            return curl_getinfo($this->_curl);
        } elseif ($this->_curl !== null && $opt !== null) {
            return curl_getinfo($this->_curl, $opt);
        } else {
            return [];
        }
    }

    /**
     * Performs HTTP request
     *
     * @param string  $method
     * @param string  $url
     * @param boolean $raw if response body contains JSON and should be decoded -> helper.
     *
     * @throws Exception if request failed
     *
     * @return mixed
     */
    private function _httpRequest($method, $url, $raw = false)
    {
        //set request type and writer function
        $this->setOption(CURLOPT_CUSTOMREQUEST, strtoupper($method));
        //check if method is head and set no body
        if ($method === 'HEAD') {
            $this->setOption(CURLOPT_NOBODY, true);
            $this->unsetOption(CURLOPT_WRITEFUNCTION);
        }
        $this->setOption(CURLOPT_HTTPHEADER, $this->getHeaders());
        /**
         * proceed curl
         */
        $this->_curl = curl_init($url);
        curl_setopt_array($this->_curl, $this->getOptions());
        $body = curl_exec($this->_curl);
        //check if curl was successful
        if ($body === false) {
            switch (curl_errno($this->_curl)) {
                case 7:
                    $this->responseCode = 'timeout';
                    return false;
                    break;
                default:
                    throw new \Exception('curl request failed: ' . curl_error($this->_curl), curl_errno($this->_curl));
                    break;
            }
        }
        //retrieve response code
        $this->responseCode = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
        $this->response     = $body;
        //check responseCode and return data/status
        if ($this->getOption(CURLOPT_CUSTOMREQUEST) === 'HEAD') {
            return true;
        } else {
            $this->response = $raw ? $this->response : json_decode($this->response);
            return $this->response;
        }
    }

}