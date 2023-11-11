<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use Doctrine\DBAL\Exception;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\ViewModel\App\Notification\RuleNotificationViewModel;
use DR\Utils\Arrays;

class RuleNotificationViewModelProvider
{
    public function __construct(
        private readonly User $user,
        private readonly RuleRepository $ruleRepository,
        private readonly RuleNotificationRepository $notificationRepository
    ) {
    }

    /**
     * @throws Exception
     */
    public function getNotificationsViewModel(?int $ruleId, bool $unread): RuleNotificationViewModel
    {
        $notificationCount = $this->notificationRepository->getUnreadNotificationPerRuleCount($this->user);
        $notifications     = [];

        $rules = $this->ruleRepository->findBy(['user' => $this->user, 'active' => true], ['name' => 'ASC'], 100);
        $rules = Arrays::reindex($rules, static fn(Rule $rule) => $rule->getId());

        $selectedRule = null;
        if (count($rules) > 0) {
            $selectedRule = $rules[$ruleId ?? 0] ?? Arrays::first($rules);
            $filter       = ['rule' => $selectedRule];
            if ($unread) {
                $filter['read'] = 0;
            }

            $notifications = $this->notificationRepository->findBy($filter, ['createTimestamp' => 'DESC'], 100);
        }

        return new RuleNotificationViewModel($selectedRule, $notificationCount, $rules, $notifications, $unread);
    }
}
