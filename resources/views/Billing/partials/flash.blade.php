@if (session('success'))
    <div class="bl-alert success"><i class="fas fa-circle-check mt-1"></i><div>{{ session('success') }}</div></div>
@endif
@if (session('error'))
    <div class="bl-alert error"><i class="fas fa-triangle-exclamation mt-1"></i><div>{{ session('error') }}</div></div>
@endif
@if ($errors->any())
    <div class="bl-alert error"><i class="fas fa-triangle-exclamation mt-1"></i>
        <div>@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div></div>
@endif
