<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

class AuthenticationType extends AbstractEnumType
{
    public const BASIC_AUTH = 'basic-auth';

    public const TYPE   = 'enum_authentication_type';
    public const VALUES = [self::BASIC_AUTH];
}
