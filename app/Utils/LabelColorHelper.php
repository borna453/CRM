<?php

namespace App\Utils;

use App\Enums\PrimaryColor;
use App\Models\Label;
use Spatie\Color\Rgb;

class LabelColorHelper
{
    public static function getLabelColors()
    {
        return Label::all()->mapWithKeys(function ($label) {
            $colorEnum = PrimaryColor::tryFrom($label->color);

            if ($colorEnum !== null) {
                $colorRgb = $colorEnum->getColor()[400];
                $colorHex = Rgb::fromString("rgb({$colorRgb})")->toHex();
            } else {
                $colorHex = '#1E232E';
            }

            return [
                $label->id => $colorHex,
            ];
        })->toArray();
    }

    public static function hexToRgba($hex, $alpha = 1)
    {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        return "rgba($r, $g, $b, $alpha)";
    }

    public static function getLabelColorById($statusId, $defaultColor = '#1E232E')
    {
        $labelColors = self::getLabelColors();
        return $labelColors[$statusId] ?? $defaultColor;
    }
}

