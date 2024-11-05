<?php

namespace App\Utils\Notifications;

use Illuminate\Support\HtmlString;

class NotificationButtonHelper
{
    public static function generateButtonHtml(string $label,string $url = '#', string $color = 'primary', string $align = 'center'): HtmlString
    {
        $buttonHtml =
            '<table class="action" align="' . $align . '" width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
            <td align="' . $align . '">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
            <td align="' . $align . '">
            <table border="0" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
            <td>
            <a href="' . $url . '" class="button button-' . $color . '" target="_blank" rel="noopener">' . $label . '</a>
            </td>
            </tr>
            </table>
            </td>
            </tr>
            </table>
            </td>
            </tr>
            </table>';
        return new HtmlString($buttonHtml);
    }
}
