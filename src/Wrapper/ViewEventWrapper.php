<?php

namespace App\Wrapper;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ViewEventWrapper extends RequestEvent
{
    public readonly ?ControllerArgumentsEvent $controllerArgumentsEvent;
    private mixed $controllerResult;

    public function __construct(HttpKernelInterface $kernel, Request $request, int $requestType, mixed $controllerResult, ControllerArgumentsEvent $controllerArgumentsEvent = null)
    {
        parent::__construct($kernel, $request, $requestType);

        $this->controllerResult = $controllerResult;
        $this->controllerArgumentsEvent = $controllerArgumentsEvent;
    }

    public function getControllerResult(): mixed
    {
        return $this->controllerResult;
    }

    public function setControllerResult(mixed $controllerResult): void
    {
        $this->controllerResult = $controllerResult;
    }
}
