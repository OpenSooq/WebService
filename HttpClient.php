<?php
namespace opensooq\webservice;

/**
 * Class HttpClient
 * Used to ceate HTTP request with different verbs
 * @package opensooq\webservice
 */
class HttpClient {
    
    /**
     * @var array  containing additional HTTP headers that you would like to send in your request.
     */
    public static $commonOptions=[
        CURLOPT_USERAGENT      => 'OpenSooqClient 1.0',
        CURLOPT_HTTPHEADER     => ['Expect:'],
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_FRESH_CONNECT  => false,
        CURLOPT_FORBID_REUSE   => false,
    ];
    
    /**
     * send a POST request to a website passing some requests parameters.
     * @param $url
     * @param $postParams
     * @param array $getParams
     * @param array $headers
     * @param array $options
     * @return array of request details
     */
    public static function post($url,$postParams,$getParams=[], $headers=[], $options=[]) {
        return static::request('POST',$url,$getParams,$postParams, $headers, $options);

    }
    
    /**
     * send a GET request to a website passing some requests parameters.
     * @param $url
     * @param array $getParams
     * @param array $headers
     * @param array $options
     * @return array
     */
    public static function get($url, $getParams=[], $headers=[],$options=[]) {
        return static::request('GET',$url,$getParams,[],$headers, $options);
        
    }
    
    /**
     * send a POST request as Json
     * @param $url
     * @param $postParams
     * @param array $getParams
     * @param array $headers
     * @param array $options
     */
    public static function jsonPost($url,$postParams,$getParams=[], $headers=[], $options=[]) {
        $headers['Content-Type']= 'application/json';
        static::post($url,$postParams,$getParams, $headers, $options);
    }
    
    /**
     * General HTTP request function used to send POST/GET/PUT/DELETE or any http request type
     * @param $verb
     * @param $url
     * @param array $getParams used on QueryString
     * @param null $body used on CURLOPT_POSTFIELDS
     * @param array $headers
     * @param array $options
     * @return array
     */
    public static function request($verb,$url,$getParams=[],$body=null, $headers=[], $options=[]) {
        $opt     = $options + static::$commonOptions;
        // Check if the header is Application Json
        $isJson = false;
        
        if(isset($headers['Content-Type']) && $headers['Content-Type'] =='application/json') {
            $isJson = true;
        }
        $headers = static::_formatHeaders($headers);
        $opt[CURLOPT_HTTPHEADER]   = $headers + $opt[CURLOPT_HTTPHEADER];
        $opt[CURLOPT_CUSTOMREQUEST] = $verb;
        $opt[CURLOPT_URL]           = static::_urlWithGetParams($url,$getParams);
        
        if ($body===null) {
            $opt[CURLOPT_NOBODY]=true;
        } elseif (is_string($body)) {
            $opt[CURLOPT_POSTFIELDS]=$body;
        } elseif ($isJson) {
            $opt[CURLOPT_POSTFIELDS]=json_encode($body);
        } elseif(!empty($body)) {
            $opt[CURLOPT_POSTFIELDS]=http_build_query($body);
            
        }
        $handler = curl_init();
        curl_setopt_array($handler, $opt);
        curl_setopt($handler, CURLINFO_HEADER_OUT, true);
        if (! $response = curl_exec($handler)) {
            return [
                'info' => [
                    'code'=>curl_getinfo($handler, CURLINFO_HTTP_CODE),
                    'Content-Type'=>curl_getinfo($handler, CURLINFO_CONTENT_TYPE),
                    'curl-error'=>curl_error($handler),
                    'curl-header' =>curl_getinfo($handler,CURLINFO_HEADER_OUT),
                ],
                'response' =>null
            ];
        }
    
        $info=[
            'code'         => curl_getinfo($handler, CURLINFO_HTTP_CODE),
            'Content-Type' => curl_getinfo($handler, CURLINFO_CONTENT_TYPE),
            'curl-error'   => null,
            'curl-header' =>curl_getinfo($handler,CURLINFO_HEADER_OUT),
        ];
    
        if (static::is_JSON($response)) {
            $response = json_decode($response);
        }
        return ['info'=>$info, 'response'=>$response];
    }
    
    /**
     * Concat URL with query string for get request
     * @param $url
     * @param array $getParams
     * @return string
     */
    private static function _urlWithGetParams($url,$getParams=null) {
        if ($getParams!==null) {
            $url .= (strpos($url, '?') === false ? '?' : '&')
                . (is_string($getParams) ? $getParams : http_build_query($getParams));
        }
        return $url;
    }
    
    private static function is_JSON($args) {
        json_decode($args);
        return (json_last_error()===JSON_ERROR_NONE);
    }
    
    /**
     * return curl headers on curl format
     * @return array
     */
    private static function _formatHeaders($headers)
    {
        $result= [];
        foreach ($headers as $name => $value) {
            if (is_null($value)) {
                $value='';
            }
            array_push($result, "$name: $value");
        }
        return $result;
    }
}