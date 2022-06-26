<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\EventSubscriber;

use DR\GitCommitNotification\Controller\Auth\AuthenticationController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @see https://symfony.com/doc/current/security/access_denied_handler.html
 */
class AccessDeniedExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private TranslatorInterface $translator)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof AccessDeniedException === false) {
            return;
        }

        $message = $this->translator->trans('redirect.access.denied.session.expired');
        $url     = $this->urlGenerator->generate(AuthenticationController::class, ['error_message' => $message]);

        // redirect to frontend when access is denied
        $event->setResponse(new RedirectResponse($url));
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
