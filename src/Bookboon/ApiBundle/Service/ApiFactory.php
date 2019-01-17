<?php

namespace Bookboon\ApiBundle\Service;


use Bookboon\Api\Bookboon;
use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Client\Headers;
use Bookboon\Api\Client\Oauth\OauthGrants;
use Bookboon\Api\Client\OauthClient;
use Bookboon\ApiBundle\Helper\ConfigurationHolder;

class ApiFactory
{
    /**
     * @param ConfigurationHolder $config
     * @param Cache $cache
     * @return Bookboon
     */
    public static function create(ConfigurationHolder $config, Cache $cache)
    {
        $bookboon = new Bookboon(
            new OauthClient(
                $config->getId(),
                $config->getSecret(),
                self::headersFromConfig($config),
                $config->getScopes(),
                $cache,
                "",
                null,
                $config->getOverrideAuthUri(),
                $config->getOverrideApiUri()
            )
        );

        $bookboon->getClient()->setAccessToken(self::credentialFactory($bookboon, $cache, $config));

        return $bookboon;
    }

    public static function credentialFactory(Bookboon $bookboon, Cache $cache, ConfigurationHolder $config)
    {
        $token = $cache->get("bookboonapi.{$config->getId()}");

        if ($token === false) {
            $token = $bookboon->getClient()->requestAccessToken([], OauthGrants::CLIENT_CREDENTIALS);

            $ttl = $token->getExpires() - time();
            $cache->save("bookboonapi.{$config->getId()}", $token, $ttl);
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
        $acceptLanguage = "";
        for ($i=0; count($languages) > $i; $i++) {
            /* TODO: logic might need to be updated if $i > 10 */
            $acceptLanguage .= $i == 0 ? $languages[$i] . ',' : $languages[$i] . ';q=' . (1 - $i/10) . ',';
        }
        return rtrim($acceptLanguage,',');
    }
}