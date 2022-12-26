<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\Authentication;

use Symfony\Component\Form\FormView;

class LoginViewModel
{
    /**
     * @param string[] $authenticationMethods
     */
    public function __construct(public readonly FormView $form, private array $authenticationMethods, public readonly string $azureAdUrl)
    {
    }

    public function hasAuthenticationMethod(string $method): bool
    {
        return in_array($method, $this->authenticationMethods, true);
    }
}
