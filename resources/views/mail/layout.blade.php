{{-- Shared SARIS email layout (07 §4): plain header, each language's content, footer. --}}
<!DOCTYPE html>
<html lang="{{ $locales[0] ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SARIS</title>
</head>
<body style="margin:0;padding:0;background:#f4f5f2;font-family:Arial,Helvetica,sans-serif;color:#1f2a1f;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f2;">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border:1px solid #dfe3da;">
                    <tr>
                        <td style="padding:16px 24px;border-bottom:3px solid #2f6b3a;font-size:18px;font-weight:bold;color:#2f6b3a;">
                            SARIS
                        </td>
                    </tr>
                    @foreach ($sections as $section)
                        @unless ($loop->first)
                            <tr><td style="padding:0 24px;"><hr style="border:0;border-top:1px solid #dfe3da;margin:0;"></td></tr>
                        @endunless
                        <tr>
                            <td style="padding:20px 24px;font-size:15px;line-height:1.5;">
                                {!! $section !!}
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="padding:16px 24px;background:#f9faf7;font-size:12px;line-height:1.5;color:#5b6658;">
                            @foreach ($footers as $footer)
                                <p style="margin:0 0 6px;">{!! $footer !!}</p>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
