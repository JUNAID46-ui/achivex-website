{{-- Sender signature: logo mark, a green divider, then name, title, company and contact links. --}}
@php
  $sigName = $brand['signatureName'] ?: ($sender?->name ?? $brand['name']);
  $sigTitle = $brand['signatureTitle'];
@endphp
<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-top:1px solid #E3E6EB">
  <tr><td style="padding-top:24px">
    <p style="margin:0 0 14px;font-size:15px;color:#4B5563">Best regards,</p>
    <table role="presentation" cellpadding="0" cellspacing="0">
      <tr>
        <td valign="middle" style="padding-right:18px">
          <img src="{{ $brand['mark'] }}" width="76" alt="AX" style="display:block;width:76px;height:auto;border:0">
        </td>
        <td valign="top" style="border-left:3px solid #B6F500;padding-left:18px;font-family:Arial,Helvetica,sans-serif">
          <p style="margin:0;font-size:17px;font-weight:bold;color:#0B0D12;line-height:1.3">{{ $sigName }}</p>
          @if($sigTitle)<p style="margin:3px 0 0;font-size:13px;font-weight:bold;color:#0F2C9C;line-height:1.4">{{ $sigTitle }}</p>@endif
          <p style="margin:3px 0 10px;font-size:13px;color:#4B5563;line-height:1.4">{{ $brand['name'] }} &middot; {{ $brand['tagline'] }}</p>
          <p style="margin:0;font-size:13px;line-height:1.75;color:#4B5563">
            <span style="color:#0F2C9C;font-weight:bold">P</span>&nbsp; <a href="{{ $brand['phoneHref'] }}" style="color:#0B0D12;text-decoration:none">{{ $brand['phone'] }}</a><br>
            <span style="color:#0F2C9C;font-weight:bold">E</span>&nbsp; <a href="mailto:{{ $brand['email'] }}" style="color:#0B0D12;text-decoration:none">{{ $brand['email'] }}</a><br>
            <span style="color:#0F2C9C;font-weight:bold">W</span>&nbsp; <a href="{{ $brand['site'] }}" style="color:#0B0D12;text-decoration:none">{{ $brand['siteLabel'] }}</a>
          </p>
          @if($brand['social'])
            <p style="margin:10px 0 0;font-size:0;line-height:0">
              @foreach($brand['social'] as $label => $s)<a href="{{ $s['url'] }}" style="display:inline-block;margin-right:6px;text-decoration:none"><img src="{{ $s['icon'] }}" width="24" height="24" alt="{{ $label }}" style="display:block;width:24px;height:24px;border:0"></a>@endforeach
            </p>
          @endif
        </td>
      </tr>
    </table>
  </td></tr>
</table>
