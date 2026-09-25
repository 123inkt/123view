<?php
declare(strict_types=1);

namespace DR\Review\Service\Asset;

use DR\Review\Entity\Asset\Asset;
use DR\Review\Entity\User\User;

class AssetFactory
{
    public function create(User $user, string $mimeType, string $binaryData): Asset
    {
        $asset = new Asset();
        $asset->setUser($user);
        $asset->setMimeType($mimeType);
        $asset->setData($binaryData);
        $asset->setCreateTimestamp(time());

        return $asset;
    }
}
