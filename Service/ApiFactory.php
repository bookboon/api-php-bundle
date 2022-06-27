<?php

namespace Bookboon\ApiBundle\Service;

use Bookboon\OauthClient\OauthGrants;
use Bookboon\ApiBundle\Client\AccessTokenClient;
use Bookboon\ApiBundle\Helper\ConfigurationHolder;
use GuzzleHttp\HandlerStack;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class ApiFactory
{
    public static function createOauth(
        ConfigurationHolder $config,
        CacheInterface      $cache,
        LoggerInterface     $logger,
        HandlerStack        $stack
    ) : AccessTokenClient {
        return new AccessTokenClient(
            $config->getId(),
            $config->getSecret(),
            $config->getScopes(),
            '',
            null,
            $config->getOverrideAuthUri(),
            $logger,
            ['handler' => $stack]
        );
    }

    public static function credentialFactory(
        AccessTokenClient $oauth,
        CacheInterface $cache,
        ConfigurationHolder $config
    ) : AccessTokenInterface {
        $token = $cache->get("bookboonapi.{$config->getId()}");

        if ($token === null) {
            $token = $oauth->requestAccessToken([], OauthGrants::CLIENT_CREDENTIALS);

            $ttl = $token->getExpires() - time();
            $cache->set("bookboonapi.{$config->getId()}", $token, $ttl);
        }

        return $token;
    }
}
