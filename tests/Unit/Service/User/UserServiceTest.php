<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\User;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\User\UserService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\User\UserService
 * @covers ::__construct
 */
class UserServiceTest extends AbstractTestCase
{
    private UserRepository&MockObject $userRepository;
    private UserService               $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->service        = new UserService($this->userRepository);
    }

    /**
     * @covers ::getUsersForRevisions
     */
    public function testGetUsersForRevisions(): void
    {
        $userA    = new User();
        $revision = new Revision();
        $revision->setAuthorEmail('sherlock@example.com');

        $this->userRepository->expects(self::once())->method('findBy')->with(['email' => ['sherlock@example.com']])->willReturn([$userA]);

        $users = $this->service->getUsersForRevisions([$revision]);
        static::assertSame([$userA], $users);
    }

    /**
     * @covers ::getUsersForRevisions
     */
    public function testGetUsersForRevisionsWithoutEmail(): void
    {
        $this->userRepository->expects(self::never())->method('findBy');

        static::assertSame([], $this->service->getUsersForRevisions([]));
    }
}
