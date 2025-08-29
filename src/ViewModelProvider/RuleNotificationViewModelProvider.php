<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use Doctrine\DBAL\Exception;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\Service\User\UserService;
use DR\Review\ViewModel\App\Notification\RuleNotificationViewModel;
use DR\Utils\Arrays;

class RuleNotificationViewModelProvider
{
    public function __construct(
        private readonly UserService $userService,
        private readonly RuleRepository $ruleRepository,
        private readonly RuleNotificationRepository $notificationRepository
    ) {
    }

    /**
     * @throws Exception
     */
    public function getNotificationsViewModel(?int $ruleId, bool $unread): RuleNotificationViewModel
    {
        $user              = $this->userService->getCurrentUser();
        $notificationCount = $this->notificationRepository->getUnreadNotificationPerRuleCount($user);
;
        $rules = $this->ruleRepository->findBy(['user' => $user, 'active' => true], ['name' => 'ASC'], 100);
        $rules = Arrays::reindex($rules, static fn(Rule $rule) => $rule->getId());

        $selectedRule  = null;
        $notifications = [];
        if (count($rules) > 0) {
            $selectedRule  = $rules[$ruleId ?? 0] ?? Arrays::first($rules);
            $filter        = ['rule' => $selectedRule] + ($unread ? ['read' => 0] : []);
            $notifications = $this->notificationRepository->findBy($filter, ['createTimestamp' => 'DESC'], 100);
        }

        return new RuleNotificationViewModel($selectedRule, $notificationCount, $rules, $notifications, $unread);
    }
}
