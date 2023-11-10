<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Notification;

use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Notification\RuleNotificationViewModel;
use DR\Review\ViewModelProvider\RuleNotificationViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RuleHistoryController
{
    public function __construct(private readonly RuleNotificationViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @return array<string, RuleNotificationViewModel>
     */
    #[Route('app/rule-history', name: self::class, methods: 'GET')]
    #[Template('app/notification/rule_history.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(): array
    {
        return ['notificationViewModel' => $this->viewModelProvider->getNotificationsViewModel()];
    }
}
