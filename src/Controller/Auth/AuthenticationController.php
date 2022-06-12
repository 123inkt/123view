<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\Auth;

use DR\GitCommitNotification\Controller\Auth\SingleSignOn\AzureAdAuthController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticationController extends AbstractController
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    /**
     * @return array<string, string|null>
     */
    #[Route('/', self::class, methods: 'GET')]
    #[Template('authentication/single-sign-on.html.twig')]
    public function __invoke(Request $request): array
    {
        return [
            'page_title'    => $this->translator->trans('page.title.single.sign.on'),
            'error_message' => $request->query->get('error_message'),
            'azure_ad_url'  => $this->generateUrl(AzureAdAuthController::class)
        ];
    }
}
