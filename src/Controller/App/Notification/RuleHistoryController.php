<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Notification;

use Doctrine\DBAL\Exception;
use DR\Review\Controller\AbstractController;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Notification\RuleNotificationViewModel;
use DR\Review\ViewModelProvider\RuleNotificationViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RuleHistoryController extends AbstractController
{
    public function __construct(private readonly RuleNotificationViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @return array<string, RuleNotificationViewModel>
     * @throws Exception
     */
    #[Route('app/rule-history', name: self::class, methods: 'GET')]
    #[Template('app/notification/rule_history.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): array
    {
        $ruleId = $request->query->getInt('ruleId');
        $unread = $request->query->get('filter') === 'unread';

        return ['notificationViewModel' => $this->viewModelProvider->getNotificationsViewModel($ruleId <= 0 ? null : $ruleId, $unread)];
    }
}
