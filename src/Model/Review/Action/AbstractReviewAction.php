<?php
declare(strict_types=1);

namespace DR\Review\Model\Review\Action;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractReviewAction
{
    public const ACTION_ADD_COMMENT  = 'add-comment';
    public const ACTION_EDIT_COMMENT = 'edit-comment';
    public const ACTION_ADD_REPLY    = 'add-reply';
    public const ACTION_EDIT_REPLY   = 'edit-reply';

    public function __construct(public readonly string $action)
    {
    }
}
