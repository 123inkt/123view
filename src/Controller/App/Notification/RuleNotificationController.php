<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Notification;

use DateTime;
use DateTimeImmutable;
use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Notification\Frequency;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\Voter\RuleVoter;
use DR\Review\Service\RuleProcessor;
use DR\Review\ViewModelProvider\Mail\CommitsViewModelProvider;
use DR\Utils\Assert;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class RuleNotificationController extends AbstractController
{
    public function __construct(
        private readonly RuleProcessor $ruleProcessor,
        private readonly RuleNotificationRepository $notificationRepository,
        private readonly CommitsViewModelProvider $viewModelProvider
    ) {
    }

    /**
     * @throws Throwable
     */
    #[Route('app/rules/notification/{id<\d+>}', name: self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(#[MapEntity] RuleNotification $notification): Response
    {
        $rule = $notification->getRule();
        $this->denyAccessUnlessGranted(RuleVoter::EDIT, $rule);

        $options     = Assert::notNull($rule->getRuleOptions());
        $frequency   = Assert::notNull($options->getFrequency());
        $currentTime = DateTimeImmutable::createFromMutable((new DateTime())->setTimestamp($notification->getNotifyTimestamp()));

        // gather commits
        $commits = $this->ruleProcessor->processRule(new RuleConfiguration(Frequency::getPeriod($currentTime, $frequency), $rule));

        if (count($commits) === 0) {
            $response = new Response('No (more) revisions found for this notification rule', headers: ['Content-Type' => 'text/plain']);
        } else {
            // render mail
            $viewModel = $this->viewModelProvider->getCommitsViewModel($commits, $rule, $notification);
            $response  = $this->render('mail/mail.commits.html.twig', ['viewModel' => $viewModel]);
            $response->headers->set('Content-Security-Policy', "");
        }

        // mark notification as read
        $this->notificationRepository->save($notification->setRead(true), true);

        return $response;
    }
}
