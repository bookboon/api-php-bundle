<?php

namespace Bookboon\ApiBundle\Helper;

use Bookboon\ApiBundle\Client\Headers;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class GuzzleDecorator
{
    public static function decorate(
        HandlerStack $stack,
        ConfigurationHolder $config,
        RequestStack $requestStack
    ): HandlerStack {
        $stack->push(Middleware::mapRequest(function (RequestInterface $request) use ($config, $requestStack) {
            if (!$request->hasHeader(Headers::HEADER_LANGUAGE)) {
                $request = $request
                    ->withHeader(Headers::HEADER_LANGUAGE, self::createAcceptLanguageString($config->getLanguages()));
            }

            if (!$request->hasHeader(Headers::HEADER_BRANDING) && null !== $branding = $config->getBranding()) {
                $request = $request->withHeader(Headers::HEADER_BRANDING, $branding);
            }
            if (!$request->hasHeader(Headers::HEADER_ROTATION) && null !== $rotation = $config->getRotation()) {
                $request = $request->withHeader(Headers::HEADER_ROTATION, $rotation);
            }
            if (!$request->hasHeader(Headers::HEADER_CURRENCY) && null !== $currency = $config->getCurrency()) {
                $request = $request->withHeader(Headers::HEADER_CURRENCY, $currency);
            }
            if (!$request->hasHeader(Headers::HEADER_PREMIUM) && null !== $premiumLevel = $config->getPremiumLevel()) {
                $request = $request->withHeader(Headers::HEADER_PREMIUM, $premiumLevel);
            }

            // Api-go and others rely on knowing the current >user< IP for tracking geolocation
            if (null !== $ip = $requestStack->getMainRequest()?->getClientIp()) {
                return $request->withHeader(Headers::HEADER_XFF, $ip);
            }

            return $request;
        }), 'bookboon-api');

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
