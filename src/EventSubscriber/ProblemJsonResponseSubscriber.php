<?php
declare(strict_types=1);

namespace DR\Review\EventSubscriber;

use DR\Review\Response\ProblemJsonResponseFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ProblemJsonResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(private ProblemJsonResponseFactory $responseFactory)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (str_starts_with($request->getPathInfo(), '/api/') === false) {
            return;
        }

        $event->setResponse($this->responseFactory->createFromThrowable($event->getThrowable()));
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
