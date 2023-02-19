<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Form\User\AddAccessTokenFormType;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\User\UserAccessTokenIssuer;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AddUserAccessTokenController extends AbstractController
{
    public function __construct(private readonly UserAccessTokenIssuer $accessTokenIssuer)
    {
    }

    /**
     * @throws Exception
     */
    #[Route('/app/user/access-token', self::class, methods: ['POST'])]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): array|Response
    {
        $form = $this->createForm(AddAccessTokenFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            $this->addFlash('error', 'access.token.creation.failed');

            return $this->redirectToRoute(UserSettingController::class);
        }

        $this->accessTokenIssuer->issue($this->getUser(), (string)$form->getData()['name']);

        $this->addFlash('success', 'access.token.creation.success');

        return $this->redirectToRoute(UserSettingController::class);
    }
}
