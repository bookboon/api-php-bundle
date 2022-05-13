<?php

namespace Bookboon\ApiBundle\Service;

use Bookboon\OauthClient\OauthGrants;
use Bookboon\ApiBundle\Client\OauthClient;
use Bookboon\ApiBundle\Helper\ConfigurationHolder;
use GuzzleHttp\HandlerStack;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class ApiFactory
{
    public static function createOauth(
        ConfigurationHolder $config,
        CacheInterface      $cache,
        LoggerInterface     $logger,
        HandlerStack        $stack): OauthClient
    {
        return new OauthClient(
            $config->getId(),
            $config->getSecret(),
            new \Bookboon\ApiBundle\Client\Headers(),
            $config->getScopes(),
            $cache,
            '',
            null,
            $config->getOverrideAuthUri(),
            $config->getOverrideApiUri(),
            $logger,
            ['handler' => $stack]
        );
    }

    public static function credentialFactory(OauthClient $oauth, CacheInterface $cache, ConfigurationHolder $config)
    {
        $token = $cache->get("bookboonapi.{$config->getId()}");

        if ($token === null) {
            $token = $oauth->requestAccessToken([], OauthGrants::CLIENT_CREDENTIALS);

            $ttl = $token->getExpires() - time();
            $cache->set("bookboonapi.{$config->getId()}", $token, $ttl);
        }

        return $token;
    }
}
