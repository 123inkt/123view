<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\Api;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\Api\CurrentUserController;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractControllerTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @coversDefaultClass \DR\Review\Controller\Api\CurrentUserController
 * @covers ::__construct
 */
class CurrentUserControllerTest extends AbstractControllerTestCase
{
    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $user = new User();
        $user->setId(5);
        $user->setName('name');
        $user->setEmail('email');

        $this->expectGetUser($user);

        $response = ($this->controller)();
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame('{"id":5,"name":"name","email":"email"}', $response->getContent());
    }

    public function getController(): AbstractController
    {
        return new CurrentUserController();
    }
}
