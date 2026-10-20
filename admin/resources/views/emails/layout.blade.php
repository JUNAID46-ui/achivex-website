<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light">
  <title>{{ $brand['name'] }}</title>
</head>
{{-- Colours follow the website: #0B0D12 black, #0F2C9C blue, #FFFFFF white, #C9CED6 silver, #B6F500 green. --}}
<body style="margin:0;padding:0;background:#0B0D12;font-family:Arial,Helvetica,sans-serif;color:#0B0D12;-webkit-text-size-adjust:100%">
  <div style="display:none;max-height:0;overflow:hidden;opacity:0">@yield('preheader')</div>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0B0D12">
    <tr><td align="center" style="padding:28px 12px">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;border-radius:14px;overflow:hidden;background:#FFFFFF">
        {{-- Header --}}
        <tr><td style="background:#0B0D12;padding:26px 32px 22px;border-bottom:1px solid #1B1F2A">
          <a href="{{ $brand['site'] }}" style="text-decoration:none">
            <img src="{{ $brand['logo'] }}" width="132" alt="{{ $brand['name'] }}" style="display:block;width:132px;height:auto;border:0;color:#FFFFFF;font-size:20px;font-weight:bold">
          </a>
        </td></tr>
        <tr><td style="height:4px;line-height:4px;font-size:0;background:#B6F500">&nbsp;</td></tr>

        {{-- Content --}}
        <tr><td style="padding:34px 32px 8px;font-size:15px;line-height:1.65;color:#0B0D12">
          @yield('body')
        </td></tr>

        @hasSection('signature')
          <tr><td style="padding:8px 32px 30px">@yield('signature')</td></tr>
        @else
          <tr><td style="padding:0 0 26px">&nbsp;</td></tr>
        @endif

        {{-- Footer --}}
        <tr><td style="background:#0F2C9C;padding:24px 32px;color:#C9CED6;font-size:12px;line-height:1.7">
          <strong style="color:#FFFFFF;font-size:13px">{{ $brand['name'] }}</strong>
          <span style="color:#B6F500">&nbsp;&middot;&nbsp;</span>{{ $brand['tagline'] }}<br>
          {{ $brand['location'] }}<br>
          <a href="{{ $brand['phoneHref'] }}" style="color:#FFFFFF;text-decoration:none">{{ $brand['phone'] }}</a>
          <span style="color:#B6F500">&nbsp;&middot;&nbsp;</span><a href="mailto:{{ $brand['email'] }}" style="color:#FFFFFF;text-decoration:none">{{ $brand['email'] }}</a>
          <span style="color:#B6F500">&nbsp;&middot;&nbsp;</span><a href="{{ $brand['site'] }}" style="color:#FFFFFF;text-decoration:none">{{ $brand['siteLabel'] }}</a>
          @if($brand['social'])
            <br>@foreach($brand['social'] as $label => $url)<a href="{{ $url }}" style="color:#B6F500;text-decoration:none;font-weight:bold">{{ $label }}</a>@unless($loop->last)<span style="color:#C9CED6">&nbsp;&nbsp;|&nbsp;&nbsp;</span>@endunless @endforeach
          @endif
        </td></tr>
      </table>
      <p style="margin:16px 0 0;font-size:11px;color:#6B7280;font-family:Arial,Helvetica,sans-serif">&copy; {{ date('Y') }} {{ $brand['name'] }}. All rights reserved.</p>
    </td></tr>
  </table>
</body>
</html>
