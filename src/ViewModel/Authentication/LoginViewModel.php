<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\Authentication;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use DR\Review\ViewModel\ViewModelInterface;
use DR\Review\ViewModelProvider\LoginViewModelProvider;
use Symfony\Component\Form\FormView;

#[Get(
    '/view-model/login',
    openapi : new OpenApiOperation(tags: ['ViewModel']),
    provider: LoginViewModelProvider::class
)]
class LoginViewModel implements ViewModelInterface
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly FormView $form, public readonly string $azureAdUrl)
    {
    }
}
