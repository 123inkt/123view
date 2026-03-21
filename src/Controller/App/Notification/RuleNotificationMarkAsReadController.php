<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Repository\Config\RuleNotificationRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RuleNotificationMarkAsReadController extends AbstractController
{
    public function __construct(private readonly RuleNotificationRepository $notificationRepository)
    {
    }

    #[Route('/app/rules/{id}/notification/mark-as-read', self::class, methods: 'GET')]
    public function __invoke(#[MapEntity] Rule $rule): Response
    {
        $this->notificationRepository->markAsRead($rule);

        return $this->refererRedirect(RuleHistoryController::class);
    }
}
