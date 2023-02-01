<?php

namespace Bookboon\ApiBundle\Tests\Helper;

use Bookboon\ApiBundle\Helper\ConfigurationHolder;
use Bookboon\ApiBundle\Helper\GuzzleDecorator;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class GuzzleDecoratorTest extends TestCase
{
    public function testXffNormal(): void
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], 'Hello, World')
        ]);

        $handlerStack = HandlerStack::create($mock);
        $configHolder = new ConfigurationHolder([]);
        $requestStack = new RequestStack();
        $mainRequest = Request::create('/', 'get', []);
        $requestStack->push($mainRequest);

        GuzzleDecorator::decorate($handlerStack, $configHolder, $requestStack);

        $client = new Client(['handler' => $handlerStack]);
        $client->request('GET', '/');
        $moddedRequest = $mock->getLastRequest();

        self::assertEquals('127.0.0.1', $moddedRequest?->getHeader("x-forwarded-for")[0] ?? null);
    }

    public function testXffInRequest(): void
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], 'Hello, World')
        ]);

        $handlerStack = HandlerStack::create($mock);
        $configHolder = new ConfigurationHolder([]);
        $requestStack = new RequestStack();
        Request::setTrustedProxies(['127.0.0.1'], Request::HEADER_X_FORWARDED_FOR);
        $mainRequest = Request::create('/', 'get', []);
        $mainRequest->headers->set('X-Forwarded-For', '81.81.20.20');
        $requestStack->push($mainRequest);

        GuzzleDecorator::decorate($handlerStack, $configHolder, $requestStack);
        $client = new Client(['handler' => $handlerStack]);
        $client->request('GET', '/');
        $moddedRequest = $mock->getLastRequest();

        self::assertEquals('81.81.20.20', $moddedRequest?->getHeader("x-forwarded-for")[0] ?? null);
    }
}
