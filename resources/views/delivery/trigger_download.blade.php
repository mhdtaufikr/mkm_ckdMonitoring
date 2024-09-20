<!DOCTYPE html>
<html>
<head>
    <title>Download PDF</title>
</head>
<body>
    <script>
        window.onload = function() {
            var downloadUrl = "{{ route('delivery-note.pdf', ['id' => encrypt($deliveryNote->id)]) }}";
            window.open(downloadUrl, '_blank'); // Open PDF in a new tab or download it directly

            // After the download, redirect based on the authenticated user
            setTimeout(function() {
                @auth
                    @if(auth()->user()->name == 'suparman')
                        window.location.href = "{{ route('delivery-note.index.manual') }}";
                    @else
                        window.location.href = "{{ route('delivery-note.index') }}";
                    @endif
                @endauth
            }, 5000); // Delay the redirect slightly to allow the download to start
        };
    </script>

    <p>Your download will begin shortly. You will be redirected after the download completes.</p>
</body>
</html>
