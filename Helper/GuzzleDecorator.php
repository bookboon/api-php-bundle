<?php

namespace Bookboon\ApiBundle\Helper;

use Bookboon\ApiBundle\Client\Headers;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class GuzzleDecorator
{

    public static function decorate(HandlerStack $stack, ConfigurationHolder $config): HandlerStack
    {
        $stack->push(Middleware::mapRequest(function (RequestInterface $request) use ($config) {
            return $request
                ->withHeader(Headers::HEADER_LANGUAGE, self::createAcceptLanguageString($config->getLanguages()))
                ->withHeader(Headers::HEADER_BRANDING, $config->getBranding())
                ->withHeader(Headers::HEADER_ROTATION, $config->getRotation())
                ->withHeader(Headers::HEADER_CURRENCY, $config->getCurrency())
                ->withHeader(Headers::HEADER_PREMIUM, $config->getPremiumLevel());
        }));

        return $stack;
    }

    /**
     * @param $languages
     * @return string
     */
    private static function createAcceptLanguageString($languages)
    {
        $acceptLanguage = '';
        for ($i = 0, $iMax = count($languages); $iMax > $i; $i++) {
            /* TODO: logic might need to be updated if $i > 10 */
            $acceptLanguage .= $i === 0 ? $languages[$i] . ',' : $languages[$i] . ';q=' . (1 - $i / 10) . ',';
        }
        return rtrim($acceptLanguage, ',');
    }
}
