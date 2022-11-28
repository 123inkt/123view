<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\Auth;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ProjectsController;
use DR\GitCommitNotification\Controller\App\User\UserApprovalPendingController;
use DR\GitCommitNotification\Controller\Auth\SingleSignOn\AzureAdAuthController;
use DR\GitCommitNotification\Security\Role\Roles;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticationController extends AbstractController
{
    public function __construct(private TranslatorInterface $translator, private Security $security)
    {
    }

    /**
     * @return array<string, string|null>|Response
     */
    #[Route('/', self::class, methods: 'GET')]
    #[Template('authentication/single-sign-on.html.twig')]
    public function __invoke(Request $request): array|Response
    {
        if ($this->security->getUser() !== null) {
            if (in_array(Roles::ROLE_USER, $this->security->getUser()->getRoles(), true) === false) {
                return $this->redirectToRoute(UserApprovalPendingController::class);
            }

            return $this->redirectToRoute(ProjectsController::class);
        }

        $params = [];
        if ($request->query->has('next')) {
            $params['next'] = $request->query->get('next', '');
        }

        return [
            'page_title'   => $this->translator->trans('page.title.single.sign.on'),
            'azure_ad_url' => $this->generateUrl(AzureAdAuthController::class, $params)
        ];
    }
}
