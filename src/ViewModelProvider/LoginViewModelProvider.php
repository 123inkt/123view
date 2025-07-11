<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DR\Review\Controller\Auth\SingleSignOn\AzureAdAuthController;
use DR\Review\Form\User\LoginFormType;
use DR\Review\ViewModel\Authentication\LoginViewModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginViewModelProvider implements ProviderInterface
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly AuthenticationUtils $authenticationUtils
    ) {
    }

    /**
     * @TODO ANGULAR REMOVE
     */
    public function getLoginViewModel(Request $request): LoginViewModel
    {
        $form = $this->formFactory->create(
            LoginFormType::class,
            null,
            ['username' => $this->authenticationUtils->getLastUsername(), 'targetPath' => $request->query->get('next')]
        )->createView();

        return new LoginViewModel(
            $form,
            $this->urlGenerator->generate(
                AzureAdAuthController::class,
                array_filter(['next' => $request->query->get('next', '')], static fn($val) => $val !== '' && $val !== null)
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $form = $this->formFactory->create(LoginFormType::class)->createView();

        return new LoginViewModel($form, $this->urlGenerator->generate(AzureAdAuthController::class));
    }
}
