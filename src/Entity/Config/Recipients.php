<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Recipients
{
    /**
     * @SerializedName("recipient")
     * @var Recipient[]
     */
    private array $recipients = [];

    /**
     * @return Recipient[]
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function addRecipient(Recipient $recipient): void
    {
        $this->recipients[] = $recipient;
    }

    /**
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function removeRecipient(Recipient $recipient): void
    {
        // method only required for deserialization
    }
}
