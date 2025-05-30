<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\User;

use DR\Review\Entity\User\User;
use DR\Review\Model\Api\Gitlab\User as GitlabUser;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\Api\Gitlab\Users;
use DR\Review\Service\User\GitlabUserService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(GitlabUserService::class)]
class GitlabUserServiceTest extends AbstractTestCase
{
    private UserRepository&MockObject $userRepository;
    private Users&MockObject          $users;
    private GitlabUserService         $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->users          = $this->createMock(Users::class);
        $gitlabApi            = $this->createMock(GitlabApi::class);
        $gitlabApi->method('users')->willReturn($this->users);
        $this->service = new GitlabUserService($this->userRepository, $gitlabApi);
    }

    /**
     * @throws Throwable
     */
    public function testGetUserByGitlabUserId(): void
    {
        $user = new User();

        $this->userRepository->expects($this->once())->method('findOneBy')->with(['gitlabUserId' => 123])->willReturn($user);

        static::assertSame($user, $this->service->getUser(123, 'username'));
    }

    /**
     * @throws Throwable
     */
    public function testGetUserByGitlabUserName(): void
    {
        $user = new User();

        $this->userRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->with(...consecutive([['gitlabUserId' => 123]], [['name' => 'username']]))
            ->willReturn(null, $user);
        $this->userRepository->expects($this->once())->method('save')->with($user, true);

        static::assertSame($user, $this->service->getUser(123, 'username'));
    }

    /**
     * @throws Throwable
     */
    public function testGetUserAbsentInApi(): void
    {
        $this->userRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturn(null, null);
        $this->users->expects($this->once())->method('getUser')->with(123)->willReturn(null);

        static::assertNull($this->service->getUser(123, 'username'));
    }

    /**
     * @throws Throwable
     */
    public function testGetUserByEmail(): void
    {
        $user              = new User();
        $gitlabUser        = new GitlabUser();
        $gitlabUser->email = 'email';

        $this->userRepository->expects($this->exactly(3))
            ->method('findOneBy')
            ->with(...consecutive([['gitlabUserId' => 123]], [['name' => 'username']], [['email' => 'email']]))
            ->willReturn(null, null, $user);
        $this->users->expects($this->once())->method('getUser')->with(123)->willReturn($gitlabUser);
        $this->userRepository->expects($this->once())->method('save')->with($user, true);

        static::assertSame($user, $this->service->getUser(123, 'username'));
    }

    /**
     * @throws Throwable
     */
    public function testGetUserUnknownUser(): void
    {
        $gitlabUser        = new GitlabUser();
        $gitlabUser->email = 'email';

        $this->userRepository->expects($this->exactly(3))
            ->method('findOneBy')
            ->with(...consecutive([['gitlabUserId' => 123]], [['name' => 'username']], [['email' => 'email']]))
            ->willReturn(null, null, null);
        $this->users->expects($this->once())->method('getUser')->with(123)->willReturn($gitlabUser);

        static::assertNull($this->service->getUser(123, 'username'));
    }
}
