<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->secret('%env(APP_SECRET)%');
    $framework->httpMethodOverride(true);
    $framework->phpErrors()->log()->throw(true);
    $framework->handleAllThrowables(true);
    $framework->annotations()->enabled(false);
    $framework->propertyInfo()->withConstructorExtractor(true);
};
