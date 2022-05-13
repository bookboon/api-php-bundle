<?php

namespace Bookboon\ApiBundle\Client;

use Bookboon\JsonLDClient\Mapping\MappingCollection;
use Bookboon\JsonLDClient\Serializer\JsonLDEncoder;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class RawClient
{
    const CACHE_KEY = "_raw_client_";
    /**
     * @var MappingCollection
     */
    private $_mappings;
    /**
     * @var ClientInterface
     */
    private $_client;
    /**
     * @var Serializer
     */
    private $_serializer;
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var AccessTokenInterface|null
     */
    private $accessToken;

    public function __construct(
        MappingCollection   $mappings,
        HandlerStack        $stack,
        SerializerInterface $serializer,
        CacheInterface      $cache
    )
    {
        $this->_mappings = $mappings;
        $this->_client = new Client(['handler' => $stack]);
        $this->_serializer = $serializer;
        $this->cache = $cache;
    }

    public function getAll(string $class, array $params, bool $useCache): string
    {
        $url = $this->getUrl($class, $params);
        $key = self::CACHE_KEY . $url;

        if ($useCache && $this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $resp = $this->makeRequest("GET", $url);
        $json = $resp->getBody()->getContents();

        if ($useCache) {
            $this->cache->set($key, $json);
        }

        return $json;
    }

    public function getById(string $id, string $class, array $params, bool $useCache): string
    {
        $url = $this->getUrl($class, $params, $id);
        $key = $this->getCacheKey($id);

        if ($useCache && $this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $resp = $this->makeRequest("GET", $url);
        $json = $resp->getBody()->getContents();

        if ($useCache) {
            $this->cache->set($key, $json);
        }

        return $json;
    }

    public function create($obj, array $params): string
    {
        $url = $this->getUrl($obj, $params);
        $jsonContents = $this->_serializer->serialize($obj, JsonLDEncoder::FORMAT);
        $resp = $this->makeRequest("POST", $url, [
            RequestOptions::BODY => $jsonContents,
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json'
            ]
        ]);

        return $resp->getBody()->getContents();
    }

    public function update($obj, array $params): string
    {
        $id = method_exists($obj, 'GetId') ? $obj->GetId() : '';
        $url = $this->getUrl($obj, $params, $id);
        $jsonContents = $this->_serializer->serialize($obj, JsonLDEncoder::FORMAT);
        $resp = $this->makeRequest("POST", $url, [
            RequestOptions::BODY => $jsonContents,
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json'
            ]
        ]);

        $this->cache->delete($this->getCacheKey($id));
        return $resp->getBody()->getContents();
    }

    public function delete($obj, array $params): string
    {
        $id = method_exists($obj, 'GetId') ? $obj->GetId() : '';
        $url = $this->getUrl($obj, $params, $id);
        $jsonContents = $this->_serializer->serialize($obj, JsonLDEncoder::FORMAT);
        $resp = $this->makeRequest("DELETE", $url, [
            RequestOptions::BODY => $jsonContents,
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json'
            ]
        ]);

        $this->cache->delete($this->getCacheKey($id));
        return $resp->getBody()->getContents();
    }

    /**
     * @param AccessTokenInterface|null $accessToken
     */
    public function setAccessToken(?AccessTokenInterface $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    protected function makeRequest(string $method, $url, array $options = []) {
        if (isset($this->accessToken)) {
            if (!isset($options[RequestOptions::HEADERS])) {
                $options[RequestOptions::HEADERS] = [];
            }

            $options[RequestOptions::HEADERS]['Authorization'] = 'Bearer: ' . $this->accessToken->getToken();
        }

        return $this->_client->request($method, $url, $options);
    }

    protected function getUrl($class, array $params, string $id = '')
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $endpoint = $this->_mappings->findEndpointByClass($class);
        $url = $endpoint->getUrl($params);

        if ($id !== '' && !$endpoint->isSingleton()) {
            $url = "$url/$id";
        }

        if (!empty($params)) {
            asort($params);
            $url = $url . "?" . http_build_query($params);
        }

        return $url;
    }

    protected function getCacheKey(string $id): string
    {
        return self::CACHE_KEY . $id;
    }
}
