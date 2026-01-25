<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Notification;

use Doctrine\DBAL\Exception;
use DR\Review\Controller\AbstractController;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModelProvider\RuleNotificationViewModelProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RuleHistoryController extends AbstractController
{
    public function __construct(private readonly RuleNotificationViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @throws Exception
     */
    #[Route('app/rule-history', name: self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): Response
    {
        $ruleId = $request->query->getInt('ruleId');
        $unread = $request->query->get('filter') === 'unread';

        $params   = ['notificationViewModel' => $this->viewModelProvider->getNotificationsViewModel($ruleId <= 0 ? null : $ruleId, $unread)];
        $response = $this->render('app/notification/rule_history.html.twig', $params);

        // set no cache headers
        $response->headers->set('cache-control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('expires', '0');

        return $response;
    }
}
