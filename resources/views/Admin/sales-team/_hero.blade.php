<div class="st-hero">
    <div class="st-hero-left">
        <div class="st-hero-icon"><i class="fas {{ $icon ?? 'fa-ranking-star' }}"></i></div>
        <div><h4>{{ $title }}</h4><p>{{ $subtitle ?? '' }}</p></div>
    </div>
    @isset($actions)<div class="st-hero-actions">{!! $actions !!}</div>@endisset
</div>
@if(session('success'))<div class="st-flash ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="st-flash err">{{ session('error') }}</div>@endif
@if($errors->any())<div class="st-flash err">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif
