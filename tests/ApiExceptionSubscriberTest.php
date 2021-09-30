<?php

namespace Ivanstan\SymfonyRest\Tests;

use Ivanstan\SymfonyRest\EventSubscriber\ApiExceptionSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ApiExceptionSubscriberTest extends TestCase
{
    public function testRequestNotIntercepted(): void
    {
        $params = $this->createMock(ParameterBag::class);
        $params->method('get')
            ->withConsecutive(['kernel.environment'], ['symfony_rest.exception_subscriber'])
            ->willReturnOnConsecutiveCalls('prod', ['paths' => ['/bla']]);

        $kernel = $this->createMock(HttpKernelInterface::class);

        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/api');

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, new BadRequestHttpException());

        (new ApiExceptionSubscriber($params))->onException($event);

        self::assertNull($event->getResponse());
    }

    public function testRequestIntercepted(): void
    {
        $params = $this->createMock(ParameterBag::class);
        $params->method('get')
            ->withConsecutive(['kernel.environment'], ['symfony_rest.exception_subscriber'])
            ->willReturnOnConsecutiveCalls('prod', ['paths' => ['/api']]);

        $kernel = $this->createMock(HttpKernelInterface::class);

        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/api');

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, new BadRequestHttpException('Text'));

        (new ApiExceptionSubscriber($params))->onException($event);

        $response = json_decode($event->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('Text', $response['response']['message']);
    }

    public function testRequestInterceptedDevEnv(): void
    {
        $params = $this->createMock(ParameterBag::class);
        $params->method('get')
            ->withConsecutive(['kernel.environment'], ['symfony_rest.exception_subscriber'])
            ->willReturnOnConsecutiveCalls('dev', ['paths' => ['/api']]);

        $kernel = $this->createMock(HttpKernelInterface::class);

        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/api');

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, new \Exception('Text'));

        (new ApiExceptionSubscriber($params))->onException($event);

        $response = json_decode($event->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('Text', $response['response']['message']);
        self::assertArrayHasKey('exception', $response['response']);

        self::assertArrayHasKey('code', $response['exception']);
        self::assertArrayHasKey('file', $response['exception']);
        self::assertArrayHasKey('message', $response['exception']);
        self::assertArrayHasKey('trace', $response['exception']);
    }
}
