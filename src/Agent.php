<?php
namespace Exinfinite\YTC;
use Exinfinite\YTC\Cache;
use GuzzleHttp\Client;

class Agent {
    private $apikey = "";
    public function __construct($api_key, $cache_path) {
        $this->apikey = $api_key;
        $this->cache = new Cache($cache_path);
        $this->httpClient = new Client();
    }
    public function getClient() {
        return $this->httpClient;
    }
    public function getApikey() {
        return $this->apikey;
    }
    public function getCache() {
        return $this->cache;
    }
    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendRequest($api, Array $query) {
        $query['key'] = $this->getApikey();
        return $this->httpClient->request('GET', $api, [
            'query' => $query,
        ]);
    }
}