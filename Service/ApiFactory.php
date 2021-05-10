<?php

namespace Bookboon\ApiBundle\Service;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\Headers;
use Bookboon\Api\Client\Oauth\OauthGrants;
use Bookboon\Api\Client\OauthClient;
use Bookboon\ApiBundle\Helper\ConfigurationHolder;
use GuzzleHttp\HandlerStack;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class ApiFactory
{
    /**
     * @param ConfigurationHolder $config
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     * @return Bookboon
     * @throws \Bookboon\Api\Exception\UsageException
     */
    public static function create(
        ConfigurationHolder $config,
        CacheInterface $cache,
        LoggerInterface $logger,
        HandlerStack $stack
    ) {
        $bookboon = new Bookboon(
            new OauthClient(
                $config->getId(),
                $config->getSecret(),
                self::headersFromConfig($config),
                $config->getScopes(),
                $cache,
                '',
                null,
                $config->getOverrideAuthUri(),
                $config->getOverrideApiUri(),
                $logger,
                ['handler' => $stack]
            )
        );

        $bookboon->getClient()->setAccessToken(self::credentialFactory($bookboon, $cache, $config));

        return $bookboon;
    }

    public static function credentialFactory(Bookboon $bookboon, CacheInterface $cache, ConfigurationHolder $config)
    {
        $token = $cache->get("bookboonapi.{$config->getId()}");

        if ($token === null) {
            $token = $bookboon->getClient()->requestAccessToken([], OauthGrants::CLIENT_CREDENTIALS);

            $ttl = $token->getExpires() - time();
            $cache->set("bookboonapi.{$config->getId()}", $token, $ttl);
        }

        return $token;
    }

    /**
     * @param ConfigurationHolder $config
     * @return Headers
     */
    private static function headersFromConfig(ConfigurationHolder $config)
    {
        $headers = new Headers();

        $headers->set(Headers::HEADER_LANGUAGE, self::createAcceptLanguageString($config->getLanguages()));

        if (!empty($config->getBranding())) {
            $headers->set(Headers::HEADER_BRANDING, $config->getBranding());
        }

        if (!empty($config->getRotation())) {
            $headers->set(Headers::HEADER_ROTATION, $config->getRotation());
        }

        if (!empty($config->getCurrency())) {
            $headers->set(Headers::HEADER_CURRENCY, $config->getCurrency());
        }

        if (!empty($config->getPremiumLevel())) {
            $headers->set(Headers::HEADER_PREMIUM, $config->getPremiumLevel());
        }

        return $headers;
    }

    /**
     * @param $languages
     * @return string
     */
    private static function createAcceptLanguageString($languages)
    {
        $acceptLanguage = '';
        for ($i=0, $iMax = count($languages); $iMax > $i; $i++) {
            /* TODO: logic might need to be updated if $i > 10 */
            $acceptLanguage .= $i === 0 ? $languages[$i] . ',' : $languages[$i] . ';q=' . (1 - $i/10) . ',';
        }
        return rtrim($acceptLanguage,',');
    }
}