<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Repository\User;

use DR\GitCommitNotification\Entity\User\UserSetting;
use DR\GitCommitNotification\Repository\User\UserSettingRepository;
use DR\GitCommitNotification\Tests\AbstractRepositoryTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Repository\User\UserSettingRepository
 * @covers ::__construct
 */
class UserSettingRepositoryTest extends AbstractRepositoryTestCase
{
    private UserSettingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(UserSettingRepository::class);
    }

    /**
     * @covers ::save
     */
    public function testSave(): void
    {
        $setting = new UserSetting();

        $this->expectPersist($setting);
        $this->expectFlush();
        $this->repository->save($setting, true);
    }

    /**
     * @covers ::remove
     */
    public function testRemove(): void
    {
        $setting = new UserSetting();

        $this->expectRemove($setting);
        $this->expectFlush();
        $this->repository->remove($setting, true);
    }

    protected function getRepositoryEntityClassString(): string
    {
        return UserSetting::class;
    }
}
