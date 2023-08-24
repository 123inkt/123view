<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin\Credentials;

use DR\Review\Controller\AbstractController;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Admin\WebhooksViewModel;
use DR\Review\ViewModelProvider\WebhooksViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CredentialsController extends AbstractController
{
    public function __construct(private readonly WebhooksViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @return array<string, WebhooksViewModel>
     */
    #[Route('/app/admin/credentials', self::class, methods: 'GET')]
    #[Template('app/admin/webhooks.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(): array
    {
        return ['webhooksViewModel' => $this->viewModelProvider->getWebhooksViewModel()];
    }
}
