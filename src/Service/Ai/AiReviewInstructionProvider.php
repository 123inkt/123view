<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai;

use DR\Review\Repository\Ai\AiReviewConfigurationRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Provides the system prompt used for AI code reviews.
 *
 * The core prompt (safety rules, output format, workflow) is an immutable file bundled with the
 * application. Administrators may additionally configure project-specific review rules through the
 * database; when present, those rules are appended as an addendum. The core prompt can never be
 * replaced this way, only extended.
 */
class AiReviewInstructionProvider
{
    public function __construct(
        private readonly AiReviewConfigurationRepository $configurationRepository,
        #[Autowire(param: 'kernel.project_dir')] private readonly string $projectDir,
    ) {
    }

    public function getSystemPrompt(): string
    {
        $corePrompt = $this->getCorePrompt();

        $additionalInstructions = trim((string)$this->configurationRepository->getSingleton()->getInstructions());
        if ($additionalInstructions === '') {
            return $corePrompt;
        }

        return $corePrompt . "\n\n---\n\n## Additional Project-Specific Review Rules\n\n" . $additionalInstructions;
    }

    public function getCorePrompt(): string
    {
        return (string)file_get_contents($this->projectDir . '/resources/ai-prompt/code-review-agent.md');
    }
}
