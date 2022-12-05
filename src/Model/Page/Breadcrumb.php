<?php
declare(strict_types=1);

namespace DR\Review\Model\Page;

/**
 * @codeCoverageIgnore
 */
class Breadcrumb
{
    public function __construct(public readonly string $label, public readonly string $url)
    {
    }
}
