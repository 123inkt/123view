<?php
declare(strict_types=1);

namespace DR\Review\EventSubscriber;

use DR\Review\Controller\App\User\UserMandatoryGitlabSyncController;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Entity\User\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener(KernelEvents::REQUEST)]
readonly class MandatoryGitlabSyncSubscriber
{
    public function __construct(
        private bool $gitlabCommentSyncEnabled,
        private bool $gitlabReviewerSyncEnabled,
        private bool $gitlabSyncMandatory,
        private Security $security,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $user = $this->security->getUser();
        // skip if not logged in
        if ($user instanceof User === false) {
            return;
        }

        // skip if already on the mandatory sync page
        $attributes = $event->getRequest()->attributes->all();
        if (isset($attributes['_controller']) && $attributes['_controller'] === UserMandatoryGitlabSyncController::class) {
            return;
        }

        // skip if sync is not configured
        if ($this->gitlabCommentSyncEnabled === false && $this->gitlabReviewerSyncEnabled === false) {
            return;
        }

        // skip if sync is not mandatory
        if ($this->gitlabSyncMandatory === false) {
            return;
        }

        // skip if user already has a gitlab token
        $token = $user->getGitAccessTokens()->findFirst(static fn($key, $token) => $token->getGitType() === RepositoryGitType::GITLAB);
        if ($token !== null) {
            return;
        }

        // redirect to mandatory sync page
        $url = $this->urlGenerator->generate(UserMandatoryGitlabSyncController::class);
        $event->setResponse(new RedirectResponse($url));
    }
}
