<?php
declare(strict_types=1);

#if (${NAMESPACE})
namespace ${NAMESPACE};
#end

use ${TESTED_NAMESPACE}\\${TESTED_NAME};
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#parse("PHPUnit Class Doc Comment.php")
#[CoversClass(GarbageCollectGitCommand::class)]
class ${NAME} extends AbstractTestCase
{
}
