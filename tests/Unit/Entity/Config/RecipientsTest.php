<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DR\GitCommitNotification\Entity\Config\Recipient;
use DR\GitCommitNotification\Entity\Config\Recipients;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Config\Recipients
 */
class RecipientsTest extends AbstractTest
{
    /**
     * @covers ::getRecipients
     * @covers ::addRecipient
     */
    public function testGetRecipients(): void
    {
        $recipients = new Recipients();
        $recipient  = new Recipient();

        static::assertEmpty($recipients->getRecipients());

        $recipients->addRecipient($recipient);
        static::assertSame([$recipient], $recipients->getRecipients());
    }
}
