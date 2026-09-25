<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Checkout;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\Reset\GitResetService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RecoverableGitCheckoutService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly GitCheckoutService $checkoutService, private readonly GitResetService $resetService)
    {
    }

    /**
     * @throws RepositoryException
     */
    public function checkoutRevision(Revision $revision): string
    {
        // the local repository _might_ have local changes due to prior failed git calls. If the checkout fails try to reset.
        try {
            return $this->checkoutService->checkoutRevision($revision);
        } catch (ProcessFailedException $exception) {
            $this->logger?->notice($exception->getMessage(), ['exception' => $exception]);
            $this->logger?->info('Checkout revision failed, reset repository and try again');
            $this->resetService->resetHard($revision->getRepository());
        }

        return $this->checkoutService->checkoutRevision($revision);
    }
}
