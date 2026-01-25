<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Setting;

use DR\Review\Controller\AbstractController;
use DR\Review\Repository\User\UserReviewSettingRepository;
use DR\Review\Request\Review\Setting\DiffComparisonPolicyRequest;
use DR\Review\Security\Role\Roles;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DiffComparisonPolicyController extends AbstractController
{
    public function __construct(private readonly UserReviewSettingRepository $repository)
    {
    }

    #[Route('app/reviews/setting/diff-comparison-policy', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(DiffComparisonPolicyRequest $request): RedirectResponse
    {
        $reviewSetting = $this->getUser()->getReviewSetting();

        $reviewSetting->setDiffComparisonPolicy($request->getComparisonPolicy());
        $this->repository->save($reviewSetting, true);

        return $this->refererRedirect('/');
    }
}
