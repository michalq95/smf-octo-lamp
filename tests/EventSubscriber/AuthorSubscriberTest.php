<?php

namespace tests\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Application;
use App\Entity\Company;
use App\Entity\Offer;
use App\Entity\User;
use App\EventSubscriber\AuthorSubscriber;
use App\Wrapper\ViewEventWrapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthhorSubscriberTest extends TestCase
{

    public function testConfiguration()
    {
        $result = AuthorSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::VIEW, $result);
        $this->assertEquals(['getAuthenticatedUser', EventPriorities::PRE_WRITE], $result[KernelEvents::VIEW]);
    }


    public function providerSetAuthorToken(): array
    {
        return [

            [Company::class, false, 'GET', false],
            [Company::class, true, 'POST'],
            [Application::class, true, 'POST'],
        ];
    }

    /**
     * @@dataProvider providerSetAuthorToken
     */
    public function testSetAuthorToken(string $className, bool $shouldCallSetAuthor, string $method, bool $shouldGetToken = true)
    {

        $entityMock = $this->getEntityMock($className, $shouldCallSetAuthor);
        $tokenStorageMock = $this->getTokenStorageMock($shouldGetToken);
        $eventMock = $this->getEventMock($method, $entityMock);

        (new AuthorSubscriber($tokenStorageMock))->getAuthenticatedUser(
            $eventMock
        );
    }

    /**
     * @return MockObject|ViewEventWrapper
     */
    private function getEventMock(string $method, $controllerResult): MockObject
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->getMock();
        $requestMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);

        $eventMock =
            $this->getMockBuilder(ViewEventWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eventMock->expects($this->once())
            ->method('getControllerResult')
            ->willReturn($controllerResult);
        $eventMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestMock);

        return $eventMock;
    }
    /**
     * @return MockObject
     */
    private function getEntityMock(string $className, bool $shouldCallSetAuthor): MockObject
    {
        $entityMock = $this->getMockBuilder($className)
            ->onlyMethods(['setOwner'])
            ->getMock();
        $entityMock->expects($shouldCallSetAuthor ? $this->once() : $this->never())
            ->method('setOwner');

        return $entityMock;
    }
    /**
     * @return MockObject|TokenStorageInterface
     */
    private function getTokenStorageMock(bool $hasToken = true): MockObject
    {
        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->getMockForAbstractClass();
        $tokenMock->expects($hasToken ? $this->once() : $this->never())
            ->method('getUser')
            ->willReturn(new User());

        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->getMockForAbstractClass();
        $tokenStorageMock->expects($hasToken ? $this->once() : $this->never())
            ->method('getToken')
            ->willReturn($hasToken ? $tokenMock : null);

        return $tokenStorageMock;
    }
}
