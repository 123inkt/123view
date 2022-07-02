<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ExternalTool\Upsource;

use DR\GitCommitNotification\Entity\Config\RepositoryProperty;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Event\CommitEvent;
use DR\GitCommitNotification\ExternalTool\Upsource\UpsourceApi;
use DR\GitCommitNotification\ExternalTool\Upsource\UpsourceIntegration;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ExternalTool\Upsource\UpsourceIntegration
 * @covers ::__construct
 */
class UpsourceIntegrationTest extends AbstractTestCase
{
    /** @var UpsourceApi&MockObject */
    private UpsourceApi         $api;
    private UpsourceIntegration $integration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->api         = $this->createMock(UpsourceApi::class);
        $this->integration = new UpsourceIntegration($this->log, 'https://upsource.example.com/', $this->api);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        static::assertSame([CommitEvent::class => ['onCommitEvent']], UpsourceIntegration::getSubscribedEvents());
    }

    /**
     * @covers ::onCommitEvent
     * @covers ::tryAddLink
     */
    public function testOnCommitEventShouldSkipOnMissingUpsourceApiUrl(): void
    {
        $this->api         = $this->createMock(UpsourceApi::class);
        $this->integration = new UpsourceIntegration($this->log, '', $this->api);

        // setup mock
        $this->api->expects(static::never())->method('getReviewId');

        $this->integration->onCommitEvent(new CommitEvent($this->createMock(Commit::class)));
    }

    /**
     * @covers ::onCommitEvent
     * @covers ::tryAddLink
     */
    public function testOnCommitEventShouldSkipOnMissingUpsourceProjectId(): void
    {
        $repository = $this->createRepository('upsource', 'https://git.repository.example.com/');

        $commit             = $this->createCommit();
        $commit->repository = $repository;

        // setup mock
        $this->api->expects(static::never())->method('getReviewId');

        $this->integration->onCommitEvent(new CommitEvent($commit));
        static::assertEmpty($commit->integrationLinks);
    }

    /**
     * @covers ::onCommitEvent
     * @covers ::tryAddLink
     */
    public function testOnCommitEventShouldSkipOnNoReviewId(): void
    {
        $repository = $this->createRepository('upsource', 'https://git.repository.example.com/');
        $repository->addRepositoryProperty(new RepositoryProperty("upsource-project-id", "foobar"));

        $commit             = $this->createCommit();
        $commit->repository = $repository;

        // setup mock
        $this->api->expects(static::once())->method('getReviewId')->with("foobar", $commit->getSubjectLine())->willReturn(null);

        $this->integration->onCommitEvent(new CommitEvent($commit));
        static::assertEmpty($commit->integrationLinks);
    }

    /**
     * @covers ::onCommitEvent
     * @covers ::tryAddLink
     */
    public function testOnCommitEventShouldSkipOnHttpClientException(): void
    {
        $repository                    = $this->createRepository('upsource', 'https://git.repository.example.com/');
        $repository->addRepositoryProperty(new RepositoryProperty("upsource-project-id", "foobar"));

        $commit             = $this->createCommit();
        $commit->repository = $repository;

        // setup mock
        $this->api->expects(static::once())->method('getReviewId')->willThrowException(new Exception());

        $this->integration->onCommitEvent(new CommitEvent($commit));
        static::assertEmpty($commit->integrationLinks);
    }

    /**
     * @covers ::onCommitEvent
     * @covers ::tryAddLink
     * @covers ::getIcon
     */
    public function testOnCommitEvent(): void
    {
        $repository                    = $this->createRepository('upsource', 'https://git.repository.example.com/');
        $repository->addRepositoryProperty(new RepositoryProperty("upsource-project-id", "foobar"));

        $commit             = $this->createCommit();
        $commit->repository = $repository;

        // setup mock
        $this->api->expects(static::once())
            ->method('getReviewId')
            ->with("foobar", $commit->getSubjectLine())
            ->willReturn('cr-12345');

        $this->integration->onCommitEvent(new CommitEvent($commit));
        static::assertNotEmpty($commit->integrationLinks);
    }
}
