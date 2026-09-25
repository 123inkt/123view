<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\ValidateRevisionsMessage;
use DR\Review\MessageHandler\ValidateRevisionsMessageHandler;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\Revision\RevisionValidationService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(ValidateRevisionsMessageHandler::class)]
class ValidateRevisionsMessageHandlerTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject      $repositoryRepository;
    private RevisionValidationService&MockObject $validationService;
    private ValidateRevisionsMessageHandler      $messageHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->validationService    = $this->createMock(RevisionValidationService::class);
        $this->messageHandler       = new ValidateRevisionsMessageHandler($this->repositoryRepository, $this->validationService);
    }

    public function testInvoke(): void
    {
        $repository = new Repository();
        $repository->setId(12);

        $this->repositoryRepository->expects($this->once())->method('find')->with(123)->willReturn($repository);
        $this->validationService->expects($this->once())->method('validate')->with($repository);

        $event = new ValidateRevisionsMessage(123);
        ($this->messageHandler)($event);
    }
}
