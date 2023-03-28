<?php
declare(strict_types=1);

namespace DR\Review\Controller\Mail;

use DateTimeImmutable;
use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Notification\Frequency;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\RuleProcessor;
use DR\Review\Utility\Assert;
use DR\Review\ViewModel\Mail\CommitsViewModel;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class NotificationRuleMailController extends AbstractController
{
    public function __construct(private readonly RuleProcessor $ruleProcessor)
    {
    }

    /**
     * @throws Throwable
     */
    #[Route('app/mail/rule/{id<\d+>}', name: self::class, methods: 'GET', condition: "env('APP_ENV') === 'dev'")]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] Rule $rule): Response
    {
        $frequency = Assert::notNull($rule->getRuleOptions()?->getFrequency());
        $startDate = new DateTimeImmutable(date('Y-m-d H:i:00', $request->query->getInt('timestamp', time())));

        $commits   = $this->ruleProcessor->processRule(new RuleConfiguration(Frequency::getPeriod($startDate, $frequency), $rule));
        $viewModel = new CommitsViewModel($commits, $rule->getRuleOptions()?->getTheme() ?? 'upsource');

        $response = $this->render('mail/mail.commits.html.twig', ['viewModel' => $viewModel]);
        $response->headers->set('Content-Security-Policy', "");

        return $response;
    }
}
