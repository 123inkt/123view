<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\Service\Notification\RuleNotificationTokenGenerator;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class RuleNotificationReadController extends AbstractController
{
    public function __construct(
        private readonly RuleNotificationTokenGenerator $tokenGenerator,
        private readonly RuleNotificationRepository $notificationRepository
    ) {
    }

    #[Route('/app/rules/rule/{id<\d+>?}/{token}', self::class, methods: 'GET')]
    public function __invoke(#[MapEntity] RuleNotification $notification, int $userId, string $token): Response
    {
        $generatedToken = $this->tokenGenerator->generate($notification);
        if (hash_equals($generatedToken, $token)) {
            throw new BadRequestHttpException('Invalid token');
        }

        $notification->setRead(true);
        $this->notificationRepository->save($notification, true);

        return new Response('', Response::HTTP_OK, ['Content-Type' => 'image/png']);
    }
}
