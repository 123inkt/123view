<?php
declare(strict_types=1);

namespace DR\Review\Controller\Auth\SingleSignOn;

use DR\Review\Controller\AbstractController;
use JsonException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use TheNetworg\OAuth2\Client\Provider\Azure;

/**
 * Azure AD single sign on starting point. Any requests parameters will be forwarded to the auth callback url
 * @see https://portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/RegisteredApps
 */
class AzureAdAuthController extends AbstractController
{
    public function __construct(private Azure $provider)
    {
    }

    /**
     * @throws JsonException
     */
    #[Route('/single-sign-on/azure-ad', self::class, methods: 'GET', condition: 'env("bool:APP_AUTH_AZURE_AD")')]
    public function __invoke(Request $request): RedirectResponse
    {
        // forward all requests parameters as state
        $state       = json_encode(array_filter($request->query->all()), JSON_THROW_ON_ERROR);
        $callbackUrl = $this->generateUrl(AzureAdCallbackController::class, [], UrlGeneratorInterface::ABSOLUTE_URL);
        $options     = ['scope' => $this->provider->scope, 'redirectUri' => $callbackUrl, 'state' => $state];

        return new RedirectResponse($this->provider->getAuthorizationUrl($options));
    }
}
