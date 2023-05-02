<?php
declare(strict_types=1);

namespace DR\Review\EventSubscriber;

use DR\Review\Response\ProblemJsonResponseFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ProblemJsonResponseSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private ProblemJsonResponseFactory $responseFactory)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (str_starts_with($request->getPathInfo(), '/api/') === false) {
            return;
        }

        $throwable = $event->getThrowable();

        $this->logger?->info("ProblemJsonResponseSubscriber: " . $throwable->getMessage(), ['exception' => $throwable]);

        $event->setResponse($this->responseFactory->createFromThrowable($throwable));
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // the priority must be greater than the Security HTTP
            // ExceptionListener, to make sure it's called before
            // the default exception listener
            KernelEvents::EXCEPTION => ['onKernelException', 2],
        ];
    }
}
