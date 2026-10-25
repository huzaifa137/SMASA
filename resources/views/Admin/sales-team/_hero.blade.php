{{-- $title, $subtitle, $icon, optional $chip, $actions (html), $features = [[icon,color,title,desc],...] --}}
<div class="st-hero mt-5">
    <div class="st-hero-main">
        <div class="st-hero-left">
            <div class="st-hero-icon"><i class="fas {{ $icon ?? 'fa-ranking-star' }}"></i></div>
            <div>
                <h4>{{ $title }} @if(!empty($chip))<span class="st-hero-chip">{{ $chip }}</span>@endif</h4>
                <p>{{ $subtitle ?? '' }}</p>
            </div>
        </div>
        @isset($actions)<div class="st-hero-actions">{!! $actions !!}</div>@endisset
    </div>
    @if(!empty($features))
        <div class="st-hero-features">
            @foreach($features as $f)
                <div class="st-hero-feature">
                    <div class="st-hero-feature-icon" style="background:{{ $f[1] }}26;"><i class="fas {{ $f[0] }}" style="color:{{ $f[1] }};"></i></div>
                    <div><div class="st-hero-feature-title">{{ $f[2] }}</div><div class="st-hero-feature-desc">{{ $f[3] }}</div></div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@if(session('success'))<div class="st-flash ok"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>@endif
@if(session('error'))<div class="st-flash err"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>@endif
@if($errors->any())<div class="st-flash err"><i class="fas fa-circle-exclamation"></i> <div>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div></div>@endif
