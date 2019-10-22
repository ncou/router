<?php

declare(strict_types=1);

namespace Chiron\Tests\Router;

use Chiron\Router\Route;
use Chiron\Router\RouteResult;
use Error;
use PHPUnit\Framework\TestCase;

class RouteResultTest extends TestCase
{
    /**
     * @expectedException Error
     * @expectedExceptionMessage Call to private Chiron\Routing\RouteResult::__construct()
     */
    public function testRouteResultCantBeInstancied()
    {
        $result = new RouteResult();
    }

    public function testRouteNameIsNotRetrievable()
    {
        $result = RouteResult::fromRouteFailure([]);
        $this->assertFalse($result->getMatchedRouteName());
    }

    public function testRouteMiddlewareStackIsNotRetrievable()
    {
        $result = RouteResult::fromRouteFailure([]);
        $this->assertFalse($result->getMatchedRouteMiddlewareStack());
    }

    // TODO : à corriger
    public function testRouteFailureRetrieveAllHttpMethods()
    {
        $result = RouteResult::fromRouteFailure(RouteResult::HTTP_METHOD_ANY);
        $this->assertSame(RouteResult::HTTP_METHOD_ANY, $result->getAllowedMethods());
    }

    public function testRouteFailureRetrieveHttpMethods()
    {
        $result = RouteResult::fromRouteFailure([]);
        $this->assertSame([], $result->getAllowedMethods());
    }

    public function testRouteMatchedParams()
    {
        $params = ['foo' => 'bar'];
        $route = $this->prophesize(Route::class);
        $result = RouteResult::fromRoute($route->reveal(), $params);
        $this->assertSame($params, $result->getMatchedParams());
    }

    public function testRouteMethodFailure()
    {
        $result = RouteResult::fromRouteFailure(['GET']);
        $this->assertTrue($result->isMethodFailure());
    }

    public function testRouteSuccessMethodFailure()
    {
        $params = ['foo' => 'bar'];
        $route = $this->prophesize(Route::class);
        $result = RouteResult::fromRoute($route->reveal(), $params);
        $this->assertFalse($result->isMethodFailure());
    }

    public function testFromRouteShouldComposeRouteInResult()
    {
        $route = $this->prophesize(Route::class);
        $result = RouteResult::fromRoute($route->reveal(), ['foo' => 'bar']);
        $this->assertInstanceOf(RouteResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertSame($route->reveal(), $result->getMatchedRoute());

        return ['route' => $route, 'result' => $result];
    }

    /**
     * @depends testFromRouteShouldComposeRouteInResult
     *
     * @param array $data
     */
    public function testAllAccessorsShouldReturnExpectedDataWhenResultCreatedViaFromRoute(array $data)
    {
        $result = $data['result'];
        $route = $data['route'];
        $route->getName()->willReturn('route');
        $route->getMiddlewareStack()->willReturn(['middleware']);
        $route->getAllowedMethods()->willReturn(['HEAD', 'OPTIONS', 'GET']);
        $this->assertEquals('route', $result->getMatchedRouteName());
        $this->assertEquals(['middleware'], $result->getMatchedRouteMiddlewareStack());
        $this->assertEquals(['HEAD', 'OPTIONS', 'GET'], $result->getAllowedMethods());
    }

    public function testRouteFailureWithNoAllowedHttpMethodsShouldReportTrueForIsMethodFailure()
    {
        $result = RouteResult::fromRouteFailure([]);
        $this->assertTrue($result->isMethodFailure());
    }

    // TODO : à corriger
    public function testFailureResultDoesNotIndicateAMethodFailureIfAllMethodsAreAllowed()
    {
        $result = RouteResult::fromRouteFailure(RouteResult::HTTP_METHOD_ANY);
        $this->assertTrue($result->isFailure());
        $this->assertFalse($result->isMethodFailure());

        return $result;
    }

    /**
     * @depends testFailureResultDoesNotIndicateAMethodFailureIfAllMethodsAreAllowed
     */
    public function testAllowedMethodsIncludesASingleWildcardEntryWhenAllMethodsAllowedForFailureResult(
        RouteResult $result
    ) {
        $this->assertSame(RouteResult::HTTP_METHOD_ANY, $result->getAllowedMethods());
    }
}
