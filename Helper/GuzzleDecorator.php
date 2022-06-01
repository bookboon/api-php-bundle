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
            $requestOut = $request
                ->withHeader(Headers::HEADER_LANGUAGE, self::createAcceptLanguageString($config->getLanguages()));

            if (null !== $branding = $config->getBranding()) {
                $requestOut = $requestOut->withHeader(Headers::HEADER_BRANDING, $branding);
            }
            if (null !== $rotation = $config->getRotation()) {
                $requestOut = $requestOut->withHeader(Headers::HEADER_ROTATION, $rotation);
            }
            if (null !== $currency = $config->getCurrency()) {
                $requestOut = $requestOut->withHeader(Headers::HEADER_CURRENCY, $currency);
            }
            if (null !== $premiumLevel = $config->getPremiumLevel()) {
                $requestOut = $requestOut->withHeader(Headers::HEADER_PREMIUM, $premiumLevel);
            }

            return $requestOut;
        }));

        return $stack;
    }

    private static function createAcceptLanguageString(array $languages) : string
    {
        $acceptLanguage = '';
        for ($i = 0, $iMax = count($languages); $iMax > $i; $i++) {
            /* TODO: logic might need to be updated if $i > 10 */
            $acceptLanguage .= $i === 0 ? $languages[$i] . ',' : $languages[$i] . ';q=' . (1 - $i / 10) . ',';
        }
        return rtrim($acceptLanguage, ',');
    }
}
