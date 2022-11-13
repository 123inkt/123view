<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Asset;

use DR\GitCommitNotification\Entity\Asset\Asset;
use DR\GitCommitNotification\Entity\User\User;

class AssetFactory
{
    public function create(User $user, string $mimeType, string $binaryData): Asset
    {
        // create stream
        /** @var resource $stream */
        $stream = fopen('php://memory', 'rb+');
        fwrite($stream, $binaryData);
        rewind($stream);

        $asset = new Asset();
        $asset->setUser($user);
        $asset->setMimeType($mimeType);
        $asset->setData($stream);
        $asset->setCreateTimestamp(time());

        return $asset;
    }
}
