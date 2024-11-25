<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

class AuthenticationType extends AbstractEnumType
{
    public const BASIC_AUTH = 'basic-auth';

    public const string TYPE   = 'enum_authentication_type';
    protected const array VALUES = [self::BASIC_AUTH];
}
