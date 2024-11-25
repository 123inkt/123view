<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

class ColorThemeType extends AbstractEnumType
{
    public const THEME_AUTO  = 'auto';
    public const THEME_LIGHT = 'light';
    public const THEME_DARK  = 'dark';

    public const string TYPE   = 'enum_color_theme';
    protected const array VALUES = [self::THEME_AUTO, self::THEME_LIGHT, self::THEME_DARK];
}
