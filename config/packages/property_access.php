<?php
declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->propertyAccess()
        ->magicCall(false)
        ->magicGet(false)
        ->magicSet(false)
        // must be false to allow CollectionType: allow_add.
        ->throwExceptionOnInvalidIndex(false)
        ->throwExceptionOnInvalidPropertyPath(true);
};
