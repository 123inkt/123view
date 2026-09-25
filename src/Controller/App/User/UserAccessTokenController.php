<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Form\User\AddAccessTokenFormType;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\User\UserAccessTokenIssuer;
use DR\Review\ViewModel\App\User\UserAccessTokenViewModel;
use DR\Review\ViewModelProvider\UserSettingViewModelProvider;
use Exception;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserAccessTokenController extends AbstractController
{
    public function __construct(
        private readonly UserAccessTokenIssuer $accessTokenIssuer,
        private readonly UserSettingViewModelProvider $viewModelProvider
    ) {
    }

    /**
     * @return array<string, UserAccessTokenViewModel>
     * @throws Exception
     */
    #[Route('/app/user/access-tokens', self::class, methods: ['GET', 'POST'])]
    #[Template('app/user/user.access-token.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): array
    {
        $form = $this->createForm(AddAccessTokenFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            if ($form->isSubmitted()) {
                $this->addFlash('error', 'access.token.creation.failed');
            }

            return ['accessTokenModel' => $this->viewModelProvider->getUserAccessTokenViewModel($form)];
        }

        /** @var array{name: string} $data */
        $data = $form->getData();

        $this->accessTokenIssuer->issue($this->getUser(), $data['name']);

        $this->addFlash('success', 'access.token.creation.success');

        return ['accessTokenModel' => $this->viewModelProvider->getUserAccessTokenViewModel($form)];
    }
}
