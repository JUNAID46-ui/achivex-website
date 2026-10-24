<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light">
  <title>{{ $brand['name'] }}</title>
</head>
{{-- Light layout in the style of edX, with the website colours: #0B0D12 text, #0F2C9C links, #B6F500 buttons, #F1F2F4 panels. --}}
<body style="margin:0;padding:0;background:#FFFFFF;font-family:Arial,Helvetica,sans-serif;color:#0B0D12;-webkit-text-size-adjust:100%">
  <div style="display:none;max-height:0;overflow:hidden;opacity:0">@yield('preheader')</div>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FFFFFF">
    <tr><td align="center" style="padding:20px 10px">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px">
        {{-- Header: logo centred on a light panel --}}
        <tr><td align="center" style="background:#F1F2F4;padding:26px 24px">
          <a href="{{ $brand['site'] }}" style="text-decoration:none">
            <img src="{{ $brand['logo'] }}" width="150" alt="{{ $brand['name'] }}" style="display:block;width:150px;height:auto;border:0;color:#0B0D12;font-size:22px;font-weight:bold">
          </a>
        </td></tr>
        <tr><td style="height:4px;line-height:4px;font-size:0;background:#B6F500">&nbsp;</td></tr>

        {{-- Content --}}
        <tr><td style="padding:36px 36px 12px;font-size:16px;line-height:1.65;color:#0B0D12">
          @yield('body')
        </td></tr>

        @hasSection('signature')
          <tr><td style="padding:12px 36px 36px">@yield('signature')</td></tr>
        @else
          <tr><td style="padding:0 0 28px;font-size:0;line-height:0">&nbsp;</td></tr>
        @endif

        {{-- Footer: logo and social icons, then about, copyright and address --}}
        <tr><td style="background:#F1F2F4;padding:28px 36px 30px">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td valign="middle"><a href="{{ $brand['site'] }}"><img src="{{ $brand['mark'] }}" width="70" alt="{{ $brand['name'] }}" style="display:block;width:70px;height:auto;border:0"></a></td>
              <td valign="middle" align="right">
                @foreach($brand['social'] as $label => $s)<a href="{{ $s['url'] }}" style="display:inline-block;margin-left:8px;text-decoration:none"><img src="{{ $s['icon'] }}" width="30" height="30" alt="{{ $label }}" style="display:block;width:30px;height:30px;border:0"></a>@endforeach
              </td>
            </tr>
          </table>
          <p style="margin:20px 0 0;font-size:14px;line-height:1.6;color:#0B0D12">{{ $brand['name'] }}: {{ $brand['tagline'] }}. {{ $brand['about'] }}</p>
          <p style="margin:14px 0 0;font-size:14px;color:#0B0D12">&copy; {{ date('Y') }} {{ $brand['name'] }}. All rights reserved.</p>
          <p style="margin:14px 0 0;font-size:14px;line-height:1.7">
            <span style="color:#0F2C9C">{{ $brand['location'] }}</span><br>
            <a href="{{ $brand['phoneHref'] }}" style="color:#0F2C9C;text-decoration:none">{{ $brand['phone'] }}</a>
            <span style="color:#9CA3AF">&nbsp;|&nbsp;</span><a href="mailto:{{ $brand['email'] }}" style="color:#0F2C9C;text-decoration:none">{{ $brand['email'] }}</a>
            <span style="color:#9CA3AF">&nbsp;|&nbsp;</span><a href="{{ $brand['site'] }}" style="color:#0F2C9C;text-decoration:none">{{ $brand['siteLabel'] }}</a>
          </p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
