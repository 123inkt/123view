<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Symfony\Component\Serializer\Annotation\SerializedName;

class ExternalLinks
{
    /**
     * @SerializedName("external_link")
     * @var ExternalLink[]
     */
    private array $externalLinks = [];

    /**
     * @return ExternalLink[]
     */
    public function getExternalLinks(): array
    {
        return $this->externalLinks;
    }

    public function addExternalLink(ExternalLink $link): void
    {
        $this->externalLinks[] = $link;
    }

    /**
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function removeExternalLink(ExternalLink $link): void
    {
        // method only required for deserialization
    }
}
