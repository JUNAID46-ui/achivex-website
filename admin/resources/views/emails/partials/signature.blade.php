{{-- Plain closing line. Contact details and social links live in the email footer. --}}
@php
  $sigName = $brand['signatureName'] ?: $brand['name'];
  $sigTitle = $brand['signatureTitle'];
@endphp
<p style="margin:0;font-size:15px;line-height:1.6;color:#4B5563">Best regards,</p>
<p style="margin:2px 0 0;font-size:15px;line-height:1.6;font-weight:bold;color:#0B0D12">{{ $sigName }}</p>
@if($sigTitle)<p style="margin:0;font-size:14px;line-height:1.6;color:#0F2C9C">{{ $sigTitle }}</p>@endif
