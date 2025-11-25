<h1>{{ $user->name }}'s QR Code</h1>
<div>
    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)->generate($user->qr_code) !!}
</div>
