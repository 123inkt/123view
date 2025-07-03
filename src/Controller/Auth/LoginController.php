<?php
declare(strict_types=1);

namespace DR\Review\Controller\Auth;

use DR\Review\Controller\AbstractController;
use DR\Review\ViewModel\Authentication\LoginViewModel;
use DR\Review\ViewModelProvider\LoginViewModelProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    public function __construct(private readonly LoginViewModelProvider $viewModelProvider)
    {
    }

    #[Route('/', name: self::class)]
    public function __invoke(Request $request): LoginViewModel
    {
        return $this->viewModelProvider->getLoginViewModel($request);
    }
}
