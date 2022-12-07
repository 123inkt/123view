<?php
declare(strict_types=1);

namespace DR\Review\Controller\Mail;

use DateTimeImmutable;
use DR\Review\Entity\Notification\Frequency;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\RuleProcessor;
use DR\Review\Utility\Assert;
use DR\Review\ViewModel\Mail\CommitsViewModel;
use Symfony\Bridge\Twig\Attribute\Entity;
use Symfony\Bridge\Twig\Attribute\IsGranted;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class NotificationRuleMailController
{
    public function __construct(private readonly RuleProcessor $ruleProcessor)
    {
    }

    /**
     * @return array<string, CommitsViewModel>
     * @throws Throwable
     */
    #[Route('app/mail/rule/{id<\d+>}', name: self::class, methods: 'GET', condition: "env('APP_ENV') === 'dev'")]
    #[Template('mail/mail.commits.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Entity('rule')]
    public function __invoke(Request $request, Rule $rule): array
    {
        $frequency = Assert::notNull($rule->getRuleOptions()?->getFrequency());
        $startDate = new DateTimeImmutable(date('Y-m-d H:i:00', $request->query->getInt('timestamp', time())));

        $commits   = $this->ruleProcessor->processRule(new RuleConfiguration(Frequency::getPeriod($startDate, $frequency), $rule));
        $viewModel = new CommitsViewModel($commits, $rule->getRuleOptions()?->getTheme() ?? 'upsource');

        return ['viewModel' => $viewModel];
    }
}
