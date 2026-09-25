<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\User;

use DR\Review\Controller\App\User\UserMentionSuggestController;
use DR\Review\Entity\User\User;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Request\User\UserMentionSuggestRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(UserMentionSuggestController::class)]
class UserMentionSuggestControllerTest extends AbstractTestCase
{
    private UserRepository&MockObject    $userRepository;
    private UserMentionSuggestController $controller;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->controller     = new UserMentionSuggestController($this->userRepository);
    }

    public function testInvoke(): void
    {
        $request = static::createStub(UserMentionSuggestRequest::class);
        $request->method('getPreferredUserIds')->willReturn([1, 2, 3]);
        $request->method('getSearch')->willReturn('search');

        $user = new User();
        $user->setId(123);
        $user->setName('Sherlock');

        $this->userRepository->expects($this->once())
            ->method('findBySearchQuery')
            ->with('search', [1, 2, 3], Roles::ROLE_USER, 15)
            ->willReturn([$user]);

        $response = ($this->controller)($request);
        static::assertSame('[{"id":123,"name":"Sherlock"}]', $response->getContent());

        $headers = $response->headers->all();
        unset($headers['date']);
        static::assertSame(
            [
                'cache-control' => ['max-age=86400, public'],
                'content-type'  => ['application/json'],
            ],
            $headers
        );
    }
}
