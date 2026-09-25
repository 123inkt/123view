<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin\Credentials;

use DR\Review\Controller\AbstractController;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Admin\CredentialsViewModel;
use DR\Review\ViewModelProvider\CredentialsViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CredentialsController extends AbstractController
{
    public function __construct(private readonly CredentialsViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @return array<string, CredentialsViewModel>
     */
    #[Route('/app/admin/credentials', self::class, methods: 'GET')]
    #[Template('app/admin/credentials.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(): array
    {
        return ['credentialsViewModel' => $this->viewModelProvider->getCredentialsViewModel()];
    }
}
