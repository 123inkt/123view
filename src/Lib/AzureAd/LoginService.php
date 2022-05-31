<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Lib\AzureAd;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use TheNetworg\OAuth2\Client\Provider\Azure;
use TheNetworg\OAuth2\Client\Token\AccessToken;
use Throwable;

class LoginService
{
    public function __construct(
        private LoggerInterface $logger,
        private Azure $azureProvider,
        private TranslatorInterface $translator,
        private ManagerRegistry $doctrine
    ) {
    }

    public function handleLogin(Request $request): LoginSuccess|LoginFailure
    {
        // error handling
        if ($request->query->has('error')) {
            $this->logger->info("azure-ad-error: error:       " . $request->query->get('error', ''));
            $this->logger->info("azure-ad-error: subcode:     " . $request->query->get('error_subcode', ''));
            $this->logger->info("azure-ad-error: description: " . $request->query->get('error_description', ''));
            $this->logger->info("azure-ad-error: error_codes: " . implode(", ", $request->query->all('error_codes')));
            $this->logger->info("azure-ad-error: error_uri:   " . $request->query->get('error_description', ''));

            // registration cancelled
            if ($request->query->get('error_subcode') === 'cancel') {
                return new LoginFailure($this->translator->trans('The log in was cancelled'));
            }

            return new LoginFailure($this->translator->trans('The log in was not successful'));
        }

        if ($request->query->has('code') === false) {
            return new LoginFailure($this->translator->trans('Invalid AzureAd callback. The `code` argument is missing.'));
        }

        // exchange authorization code for access token
        try {
            $token = $this->azureProvider->getAccessToken('authorization_code', [
                'scope' => $this->azureProvider->scope,
                'code'  => $request->query->get('code'),
            ]);
        } catch (Throwable $e) {
            $this->logger->notice($e->getMessage(), ['exception' => $e]);

            return new LoginFailure($this->translator->trans('Unable to validate the login attempt. Please retry'));
        }

        $claims = $token instanceof AccessToken ? $token->getIdTokenClaims() : [];
        if (isset($claims['preferred_username']) === false) {
            $this->logger->notice('Authorization token doesnt contain preferred_username', $claims);

            return new LoginFailure($this->translator->trans("The authorization token doesn't contain a username. Unable to login"));
        }

        return new LoginSuccess($claims['name'], $claims['preferred_username']);
    }
}
