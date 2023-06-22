<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\User;

use DR\Review\Controller\App\User\UserMentionSuggestController;
use DR\Review\Entity\User\User;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \DR\Review\Controller\App\User\UserMentionSuggestController
 * @covers ::__construct
 */
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

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $request = new Request(['search' => 'search']);
        $user    = new User();
        $user->setId(123);
        $user->setName('Sherlock');

        $this->userRepository->expects(self::once())->method('findBySearchQuery')->with('search', Roles::ROLE_USER, 15)->willReturn([$user]);

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
