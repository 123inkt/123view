<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\Mail;

use DateTimeImmutable;
use DR\GitCommitNotification\Entity\Config\Frequency;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\RuleConfiguration;
use DR\GitCommitNotification\Service\RuleProcessor;
use DR\GitCommitNotification\Utility\Assert;
use DR\GitCommitNotification\ViewModel\Mail\CommitsViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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
