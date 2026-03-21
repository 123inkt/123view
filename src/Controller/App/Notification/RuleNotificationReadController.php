<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\Service\Notification\RuleNotificationTokenGenerator;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class RuleNotificationReadController extends AbstractController
{
    public function __construct(
        private readonly RuleNotificationTokenGenerator $tokenGenerator,
        private readonly RuleNotificationRepository $notificationRepository
    ) {
    }

    #[Route('/public/rule/notification/read/{id<\d+>}/{token}', self::class, methods: 'GET')]
    public function __invoke(#[MapEntity] RuleNotification $notification, string $token): Response
    {
        $generatedToken = $this->tokenGenerator->generate($notification);
        if (hash_equals($generatedToken, $token) === false) {
            throw new BadRequestHttpException('Invalid token');
        }

        if ($notification->isRead() === false) {
            $notification->setRead(true);
            $this->notificationRepository->save($notification, true);
        }

        return new BinaryFileResponse(dirname(__DIR__, 4) . '/assets/images/1x1.png', headers: ['Cache-Control' => 'no-cache, no-store']);
    }
}
