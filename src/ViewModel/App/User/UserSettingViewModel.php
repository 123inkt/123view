<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\User;

use DR\Review\Entity\User\UserAccessToken;
use Symfony\Component\Form\FormView;

/**
 * @codeCoverageIgnore
 */
class UserSettingViewModel
{
    /**
     * @param UserAccessToken[] $accessTokens
     */
    public function __construct(public readonly array $accessTokens, public readonly FormView $addTokenForm, public readonly FormView $settingForm)
    {
    }
}
