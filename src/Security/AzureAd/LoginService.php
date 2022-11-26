<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Security\AzureAd;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use TheNetworg\OAuth2\Client\Provider\Azure;
use TheNetworg\OAuth2\Client\Token\AccessToken;
use Throwable;

class LoginService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private Azure $azureProvider, private TranslatorInterface $translator)
    {
    }

    public function handleLogin(Request $request): LoginSuccess|LoginFailure
    {
        // error handling
        if ($request->query->has('error')) {
            $this->logger?->info("azure-ad-error: error:       " . $request->query->get('error', ''));
            $this->logger?->info("azure-ad-error: subcode:     " . $request->query->get('error_subcode', ''));
            $this->logger?->info("azure-ad-error: description: " . $request->query->get('error_description', ''));
            $this->logger?->info("azure-ad-error: error_codes: " . implode(", ", $request->query->all('error_codes')));
            $this->logger?->info("azure-ad-error: error_uri:   " . $request->query->get('error_uri', ''));

            // registration cancelled
            if ($request->query->get('error_subcode') === 'cancel') {
                return new LoginFailure($this->translator->trans('login.cancelled'));
            }

            return new LoginFailure($this->translator->trans('login.not.successful'));
        }

        if ($request->query->has('code') === false) {
            return new LoginFailure($this->translator->trans('login.invalid.azuread.callback'));
        }

        // exchange authorization code for access token
        try {
            $token = $this->azureProvider->getAccessToken('authorization_code', [
                'scope' => $this->azureProvider->scope,
                'code'  => $request->query->get('code'),
            ]);
        } catch (Throwable $e) {
            $this->logger?->notice($e->getMessage(), ['exception' => $e]);

            return new LoginFailure($this->translator->trans('login.unable.to.validate.login.attempt'));
        }

        $claims = $token instanceof AccessToken ? $token->getIdTokenClaims() : [];
        if (isset($claims['preferred_username']) === false) {
            $this->logger?->notice('Authorization token doesnt contain preferred_username', $claims);

            return new LoginFailure($this->translator->trans("login.authorization.has.no.token"));
        }

        return new LoginSuccess($claims['name'], $claims['preferred_username']);
    }
}
