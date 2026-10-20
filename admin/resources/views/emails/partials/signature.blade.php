{{-- Sender signature: logo mark, a green divider, then name, title, company and contact links. --}}
@php
  $sigName = $brand['signatureName'] ?: ($sender?->name ?? $brand['name']);
  $sigTitle = $brand['signatureTitle'];
@endphp
<table role="presentation" cellpadding="0" cellspacing="0" style="border-top:1px solid #E3E6EB;padding-top:22px;width:100%">
  <tr><td style="padding-top:22px">
    <table role="presentation" cellpadding="0" cellspacing="0">
      <tr>
        <td valign="top" style="padding-right:16px">
          <img src="{{ $brand['mark'] }}" width="64" alt="AX" style="display:block;width:64px;height:auto;border:0">
        </td>
        <td valign="top" style="border-left:3px solid #B6F500;padding-left:16px;font-family:Arial,Helvetica,sans-serif">
          <p style="margin:0;font-size:16px;font-weight:bold;color:#0B0D12;line-height:1.3">{{ $sigName }}</p>
          @if($sigTitle)<p style="margin:2px 0 0;font-size:13px;font-weight:bold;color:#0F2C9C;line-height:1.4">{{ $sigTitle }}</p>@endif
          <p style="margin:2px 0 10px;font-size:13px;color:#4B5563;line-height:1.4">{{ $brand['name'] }} &middot; {{ $brand['tagline'] }}</p>
          <p style="margin:0;font-size:13px;line-height:1.7;color:#4B5563">
            <span style="color:#0F2C9C;font-weight:bold">P</span>&nbsp; <a href="{{ $brand['phoneHref'] }}" style="color:#0B0D12;text-decoration:none">{{ $brand['phone'] }}</a><br>
            <span style="color:#0F2C9C;font-weight:bold">E</span>&nbsp; <a href="mailto:{{ $brand['email'] }}" style="color:#0B0D12;text-decoration:none">{{ $brand['email'] }}</a><br>
            <span style="color:#0F2C9C;font-weight:bold">W</span>&nbsp; <a href="{{ $brand['site'] }}" style="color:#0B0D12;text-decoration:none">{{ $brand['siteLabel'] }}</a>
          </p>
          @if($brand['social'])
            <p style="margin:10px 0 0;font-size:12px">
              @foreach($brand['social'] as $label => $url)<a href="{{ $url }}" style="display:inline-block;margin:0 6px 4px 0;padding:4px 10px;border-radius:999px;background:#0B0D12;color:#B6F500;text-decoration:none;font-weight:bold">{{ $label }}</a>@endforeach
            </p>
          @endif
        </td>
      </tr>
    </table>
  </td></tr>
</table>
