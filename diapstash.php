<?php
/**
 * This class interfaces with the diapstash API https://api.diapstash.com/api/docs
 * 
 * @author Rowedahelicon <rowdy@cruxes.space>
 * @link https://github.com/rowedahelicon/PHP-Diapstash-API
 */
class diapStash
{
    private string $base_url = "https://api.diapstash.com/api/v1";
    private string $oidc_auth_url = 'https://account.diapstash.com/oidc/auth';
    private string $oidc_token_url = 'https://account.diapstash.com/oidc/token';

    private string $scope = 'cloud-sync.history cloud-sync.stock cloud-sync.types offline_access';

    private $auth_header;
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    
    function __construct(string $client_id, string $client_secret, string $redirect_uri)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;
    }

    /* API CALLS */

    /**
     * Returns information about specified brands
     *
     * @param array|null $query
     * @return array
     * 
     * @link https://api.diapstash.com/api/docs/#/Brand/brandGetBrands
     */
    public function getBrands(array | null $query = null) : array
    {        
        return $this->curl($this->base_url.'/brand/brands', 'GET', !empty($query) ? $this->prepareQueryString($query, ['page', 'size']) : null);
    }

    /**
     * Returns information about a specific brand
     *
     * @param string $code
     * @return array
     * 
     * @link https://api.diapstash.com/api/docs/#/Brand/brandGetBrandByCode
     */
    public function getBrand(string $code) : array
    {        
        return $this->curl($this->base_url.'/brand/brands/'.$code, 'GET');
    }

    /**
     * Returns all users changes
     *
     * @param array|null $query
     * @return array
     * 
     * @link https://api.diapstash.com/api/docs/#/History/historyGetChanges
     */
    public function getChanges(array | null $query = null) : array
    {
        return $this->curl($this->base_url.'/history/changes', 'GET', !empty($query) ? $this->prepareQueryString($query, ['page', 'size', 'startTime', 'endTime', 'state', 'leak', 'messyOverflow', 'wetness', 'messyLevel', 'tags', 'changePeriod']) : null, $this->auth_header);
    }

    /**
     * Returns all users accidents
     *
     * @param array|null $query
     * @return array
     * 
     * @link https://api.diapstash.com/api/docs/#/History/historyGetAccident
     */
    public function getAccidents(array | null $query = null) : array
    {
        return $this->curl($this->base_url.'/history/accidents', 'GET', !empty($query) ? $this->prepareQueryString($query, ['page', 'size', 'type', 'cause', 'precisionTime', 'location', 'posistion', 'when', 'level', 'causeLeak']) : null, $this->auth_header);
    }

    /**
     * Returns all users stocks
     *
     * @param array|null $query
     * @return array
     * 
     * @link https://api.diapstash.com/api/docs/#/Stock/stockGetStock
     */
    public function getStocks(array | null $query = null) : array
    {
        return $this->curl($this->base_url.'/stock/stocks', 'GET', !empty($query) ? $this->prepareQueryString($query, ['page', 'size', 'name', 'principal', 'masked']) : null, $this->auth_header);
    }

    /**
     * Returns all users disposables stock
     *
     * @param array|null $query
     * @return array
     * 
     * @link https://api.diapstash.com/api/docs/#/Stock/stockGetDds
     */
    public function getDisposables(array | null $query = null) : array
    {
        return $this->curl($this->base_url.'/stock/disposables', 'GET', !empty($query) ? $this->prepareQueryString($query, ['page', 'size', 'stockId', 'typeId', 'left']) : null, $this->auth_header);
    }

    /**
     * Returns all users reusables stock
     *
     * @param array|null $query
     * @return array
     * 
     * @link https://api.diapstash.com/api/docs/#/Stock/stockGetRds
     */
    public function getReusables(array | null $query = null) : array
    {
        return $this->curl($this->base_url.'/stock/reusables', 'GET', !empty($query) ? $this->prepareQueryString($query, ['page', 'size', 'stockId', 'typeId']) : null, $this->auth_header);
    }

    /**
     * Returns diaper types
     *
     * @param array|null $query
     * @return array
     * 
     * @link https://api.diapstash.com/api/docs/#/Type/typeGetTypes
     */
    public function getTypes()
    {
        return $this->curl($this->base_url.'/type/types', 'GET', !empty($query) ? $this->prepareQueryString($query, ['page', 'size']) : null, $this->auth_header);
    }

    /**
     * Returns custom diaper types
     *
     * @param array|null $query
     * @return array
     * 
     * @link https://api.diapstash.com/api/docs/#/Type/typeGetTypesCustom
     */
    public function getCustomTypes()
    {
        return $this->curl($this->base_url.'/type/types/custom', 'GET', !empty($query) ? $this->prepareQueryString($query, ['page', 'size']) : null, $this->auth_header);
    }

    /**
     * Returns a diaper type by id
     *
     * @param integer $id
     * @return array
     * 
     * @link https://api.diapstash.com/api/docs/#/Type/typeGetTypesById
     */
    public function getType(int $id) : array
    {
        return $this->curl($this->base_url.'/type/types/'.$id, 'GET', null, $this->auth_header);
    }

    /**
     * Returns a custom diaper type by id
     *
     * @param integer $id
     * @return array
     * 
     * @link https://api.diapstash.com/api/docs/#/Type/typeGetTypesCustomsById
     */
    public function getCustomType(int $id) : array
    {
        return $this->curl($this->base_url.'/type/types/custom/'.$id, 'GET', null, $this->auth_header);
    }

    /* AUTH FUNCTIONS */

    /**
     * Returns the login URL based on the specified information and the code verifier to compare later
     *
     * @return array
     */
    public function getLoginUrl() : array
    {
        $code_verifier = $this->generateRandomString(100);
        $code_challenge = $this->codeChallenge($code_verifier);

        $state = bin2hex(random_bytes(5));

        $fields = [];
        $fields['scope'] = $this->scope;
        $fields['code_challenge'] = $code_challenge;
        $fields['code_challenge_method'] = 'S256';
        $fields['client_id'] = $this->client_id;
        $fields['redirect_uri'] = $this->redirect_uri;
        $fields['response_type'] = 'code';
        $fields['state'] = $state;
        $fields['prompt'] = 'consent';

        return ['url' => $this->oidc_auth_url.'?'.http_build_query($fields), 'code_verifier' => $code_verifier];
    }

    /**
     * Gets a user token
     *
     * @param string $code
     * @param string $state
     * @param string $code_verifier
     * @return array
     */
    public function getToken(string $code, string $state, string $code_verifier) : array
    {
        //Error check state - TODO
        //if ($state != ) throw new error();

        $fields = [];
        $fields['code_verifier'] = $code_verifier;
        $fields['code'] = $code;
        $fields['client_id'] = $this->client_id;
        $fields['client_secret'] = $this->client_secret;
        $fields['redirect_uri'] = $this->redirect_uri;
        $fields['grant_type'] = 'authorization_code';
        $fields['prompt'] = 'consent';

        return $this->curl($this->oidc_token_url, 'POST', $fields);
    }

    /**
     * Refreshes a user token
     *
     * @param string $refresh_token
     * @return array
     */
    public function refreshToken(string $refresh_token) : array
    {
        $fields = [];
        $fields['refresh_token'] = $refresh_token;
        $fields['client_id'] = $this->client_id;
        $fields['client_secret'] = $this->client_secret;
        $fields['redirect_uri'] = $this->redirect_uri;
        $fields['grant_type'] = 'refresh_token';
        $fields['prompt'] = 'consent';

        return $this->curl($this->oidc_token_url, 'POST', $fields);
    }

    /* STOCK FUNCTIONS */
    
    /**
     * Loads the auth header we use to make authenticated requests
     *
     * @param string $access_token
     * @return void
     */
    public function generateAuthHeader(string $access_token) : void
    {
        $this->auth_header = ['Authorization: Bearer '.$access_token, 'DS-API-CLIENT-ID: '.$this->client_id];
    }

    /**
     * Generates a random string
     *
     * @param integer $length
     * @return string
     * 
     * @source https://stackoverflow.com/a/4356295
     */
    function generateRandomString(int $length = 10) : string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i=0; $i<$length;$i++)
        {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Returns a PKCE code challenge
     *
     * @param string $verifier
     * @return string
     */
    public function codeChallenge(string $verifier) : string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    /**
     * Filters out potentially useless / empty values
     *
     * @param array $input
     * @param array $list
     * @return array
     */
    public function prepareQueryString(array $input, array $list) : array
    {
        $qfields = [];
        
        foreach ($input as $k => $v)
        {
            if (in_array($k, $list)) $qfields[$k] = $v;
        }

        return $qfields;
    }

    /**
     * Helper function for curl calls
     *
     * @param string $url
     * @param string $method
     * @param array|null|null $fields
     * @param array $header
     * @param array|null|null $options
     * @return array
     */
    private function curl(string $url, string $method = "GET", array|null $fields = null, array $header = [], array|null $options = null) : array
    {        
        $ch = curl_init();
        
        switch (strtolower($method))
        {
            case "delete":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                if (!empty($fields)) $url = $url . '?' . http_build_query($fields);
            break;
            case "post":
                if (!empty($fields)) curl_setopt($ch, CURLOPT_POST, count($fields));
                if (!empty($fields)) curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
            break;
            case "put":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                if (!empty($fields)) $url = $url . '?' . http_build_query($fields);
            break;
            case "get":
            default:
                if (!empty($fields)) $url = $url . '?' . http_build_query($fields);
            break;
        }   

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //curl_setopt($ch, CURLOPT_VERBOSE, 1);
    
        if (!is_null($options)) curl_setopt_array($ch, $options);
    
        $return = curl_exec($ch);        
        $json_return = json_decode($return, TRUE);

        $httpCode = curl_getinfo($ch , CURLINFO_HTTP_CODE); // this results 0 every time
    
        if (!$return && curl_errno($ch) > 0) throw new Exception('A Curl error has occured! HttpCode: '.$httpCode.'Error code: '.curl_errno($ch).'-- Details: '.curl_error($ch));

        //TO-DO : Check for errors?
        //if (empty($return)) throw new Exception('The query returned empty');
        //if ($json_return['status'] == '401') throw new Exception('Error');

        curl_close($ch);
        return $json_return;
    }
}
