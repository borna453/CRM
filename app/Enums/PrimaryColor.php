<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Spatie\Color\Rgb;
use UnexpectedValueException;

enum PrimaryColor: string implements HasColor, HasLabel
{
    case Slate = 'slate';
    case Gray = 'gray';
    case Neutral = 'neutral';
    case Red = 'red';
    case Orange = 'orange';
    case Amber = 'amber';
    case Yellow = 'yellow';
    case Lime = 'lime';
    case Green = 'green';
    case Blue = 'blue';
    case Purple = 'purple';
    case Pink = 'pink';

    public const DEFAULT = self::Green->value;

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Slate => Color::Slate,
            self::Gray => Color::Gray,
            self::Neutral => Color::Neutral,
            self::Red => Color::Red,
            self::Orange => Color::Orange,
            self::Amber => Color::Amber,
            self::Yellow => Color::Yellow,
            self::Lime => Color::Lime,
            self::Green => Color::Green,
            self::Blue => Color::Blue,
            self::Purple => Color::Purple,
            self::Pink => Color::Pink,
        };
    }

    public function translate(): string
    {
        return match ($this) {
            self::Slate => __('portal.colors.slate'),
            self::Gray => __('portal.colors.gray'),
            self::Neutral => __('portal.colors.neutral'),
            self::Red => __('portal.colors.red'),
            self::Orange => __('portal.colors.orange'),
            self::Amber => __('portal.colors.amber'),
            self::Yellow => __('portal.colors.yellow'),
            self::Lime => __('portal.colors.lime'),
            self::Green => __('portal.colors.green'),
            self::Blue => __('portal.colors.blue'),
            self::Purple => __('portal.colors.purple'),
            self::Pink => __('portal.colors.pink'),
        };
    }

    public function getLabel(): ?string
    {
        return $this->translate();
    }

    public function getHexColor(): string
    {
        $colorArray = $this->getColor();

        if ($colorArray !== null && isset($colorArray[600])) {
            $rgbToString = $colorArray[600];

            return Rgb::fromString("rgb({$rgbToString})")->toHex();
        }

        throw new UnexpectedValueException("The color {$this->value} does not have a hex code.");
    }
}
