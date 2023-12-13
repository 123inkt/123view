<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider\Mail;

use DR\Review\Controller\App\Notification\RuleNotificationReadController;
use DR\Review\Doctrine\Type\MailThemeType;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Service\Notification\RuleNotificationTokenGenerator;
use DR\Review\ViewModel\Mail\CommitsViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommitsViewModelProvider
{
    public function __construct(
        private readonly RuleNotificationTokenGenerator $tokenGenerator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param Commit[] $commits
     */
    public function getCommitsViewModel(array $commits, Rule $rule, ?RuleNotification $notification): CommitsViewModel
    {
        $url = null;
        if ($notification !== null) {
            $token = $this->tokenGenerator->generate($notification);
            $url   = $this->urlGenerator->generate(
                RuleNotificationReadController::class,
                ['id' => $notification->getId(), 'token' => $token]
            );
        }

        return new CommitsViewModel($commits, $rule->getRuleOptions()?->getTheme() ?? MailThemeType::UPSOURCE, $url);
    }
}
