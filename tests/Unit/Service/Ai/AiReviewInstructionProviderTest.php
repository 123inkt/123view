<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai;

use DR\Review\Entity\Ai\AiReviewConfiguration;
use DR\Review\Repository\Ai\AiReviewConfigurationRepository;
use DR\Review\Service\Ai\AiReviewInstructionProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(AiReviewInstructionProvider::class)]
class AiReviewInstructionProviderTest extends AbstractTestCase
{
    private AiReviewConfigurationRepository&MockObject $configurationRepository;
    private AiReviewInstructionProvider                $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->configurationRepository = $this->createMock(AiReviewConfigurationRepository::class);
        $this->provider                = new AiReviewInstructionProvider($this->configurationRepository, dirname(__DIR__, 4));
    }

    public function testGetSystemPromptShouldReturnCorePromptOnlyWhenNoInstructionsConfigured(): void
    {
        $configuration = new AiReviewConfiguration();
        $this->configurationRepository->expects($this->once())->method('getSingleton')->willReturn($configuration);

        $prompt = $this->provider->getSystemPrompt();

        static::assertSame($this->provider->getCorePrompt(), $prompt);
        static::assertStringNotContainsString('Additional Project-Specific Review Rules', $prompt);
    }

    public function testGetSystemPromptShouldReturnCorePromptOnlyWhenInstructionsAreBlank(): void
    {
        $configuration = new AiReviewConfiguration()->setInstructions("   \n  ");
        $this->configurationRepository->expects($this->once())->method('getSingleton')->willReturn($configuration);

        static::assertSame($this->provider->getCorePrompt(), $this->provider->getSystemPrompt());
    }

    public function testGetSystemPromptShouldAppendConfiguredInstructions(): void
    {
        $configuration = new AiReviewConfiguration()->setInstructions('Always check for null safety.');
        $this->configurationRepository->expects($this->once())->method('getSingleton')->willReturn($configuration);

        $prompt = $this->provider->getSystemPrompt();

        static::assertTrue(str_starts_with($prompt, $this->provider->getCorePrompt()));
        static::assertStringContainsString('Additional Project-Specific Review Rules', $prompt);
        static::assertStringContainsString('Always check for null safety.', $prompt);
    }

    public function testGetCorePromptShouldReturnCoreFileContents(): void
    {
        $this->configurationRepository->expects($this->never())->method('getSingleton');

        static::assertStringContainsString('code review agent', $this->provider->getCorePrompt());
    }
}
