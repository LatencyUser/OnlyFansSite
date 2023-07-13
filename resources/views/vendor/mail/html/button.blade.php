<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
    <a href="{{ $url }}" class="button button-{{ $color ?? 'primary' }}" target="_blank" style="
        background-color:#{{getSetting('colors.theme_color_code') ? getSetting('colors.theme_color_code') : 'cb0c9f'}};
        border-top: 10px solid #{{getSetting('colors.theme_color_code') ? getSetting('colors.theme_color_code') : 'cb0c9f'}};
        border-right: 18px solid #{{getSetting('colors.theme_color_code') ? getSetting('colors.theme_color_code') : 'cb0c9f'}};
        border-bottom: 10px solid #{{getSetting('colors.theme_color_code') ? getSetting('colors.theme_color_code') : 'cb0c9f'}};
        border-left: 18px solid #{{getSetting('colors.theme_color_code') ? getSetting('colors.theme_color_code') : 'cb0c9f'}};
        ">{{ $slot }}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
