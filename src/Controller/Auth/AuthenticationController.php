<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\Auth;

use DR\GitCommitNotification\Controller\Auth\SingleSignOn\AzureAdAuthController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AuthenticationController extends AbstractController
{
    #[Route('/', self::class)]
    #[Template('authentication/single-sign-on.html.twig')]
    public function __invoke(): array
    {
        return [
            'page_title' => 'Single Sign On',
            'azure_ad_url' => $this->generateUrl(AzureAdAuthController::class)
        ];
    }
}
