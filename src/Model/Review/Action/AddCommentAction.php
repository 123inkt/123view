<?php
declare(strict_types=1);

namespace DR\Review\Model\Review\Action;

use DR\Review\Entity\Review\LineReference;

/**
 * @codeCoverageIgnore
 */
class AddCommentAction extends AbstractReviewAction
{
    public function __construct(public readonly LineReference $lineReference)
    {
        parent::__construct(self::ACTION_ADD_COMMENT);
    }
}
