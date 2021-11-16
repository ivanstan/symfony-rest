<?php

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
        $params = $this->parameterBagFactory('prod', ['/api']);
        $event = $this->exceptionEventFactory(Request::create('/bla'), new BadRequestHttpException());

        (new ApiExceptionSubscriber($params))->onException($event);

        self::assertNull($event->getResponse());
    }

    public function testRequestIntercepted(): void
    {
        $params = $this->parameterBagFactory('prod', ['/api']);
        $event = $this->exceptionEventFactory(Request::create('/api'), new BadRequestHttpException('Text'));

        (new ApiExceptionSubscriber($params))->onException($event);

        $response = json_decode($event->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('Text', $response['response']['message']);
    }

    public function testRequestInterceptedDevEnv(): void
    {
        $params = $this->parameterBagFactory('dev', ['/api']);
        $event = $this->exceptionEventFactory(Request::create('/api'), new \Exception('Text'));

        (new ApiExceptionSubscriber($params))->onException($event);

        $response = json_decode($event->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('Text', $response['response']['message']);
        self::assertArrayHasKey('exception', $response['response']);

        self::assertArrayHasKey('code', $response['response']['exception']);
        self::assertArrayHasKey('file', $response['response']['exception']);
        self::assertArrayHasKey('message', $response['response']['exception']);
        self::assertArrayHasKey('trace', $response['response']['exception']);
    }

    public function testEmptyPathParam(): void
    {
        $params = $this->parameterBagFactory('dev', []);
        $event = $this->exceptionEventFactory(Request::create('/api'), new \Exception('Text'));

        (new ApiExceptionSubscriber($params))->onException($event);

        self::assertNull($event->getResponse());
    }

    protected function exceptionEventFactory(Request $request, \Throwable $e): ExceptionEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $e);
    }

    protected function parameterBagFactory(string $env, array $paths): ParameterBag
    {
        $params = $this->createMock(ParameterBag::class);
        $params->method('get')
            ->withConsecutive(['kernel.environment'], ['symfony_rest.exception_subscriber'])
            ->willReturnOnConsecutiveCalls($env, ['paths' => $paths]);

        return $params;
    }
}
