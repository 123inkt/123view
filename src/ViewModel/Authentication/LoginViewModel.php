<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\Authentication;

use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LoginViewModel
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly FormView $form, public readonly ?AuthenticationException $error, public readonly string $azureAdUrl)
    {
    }
}
