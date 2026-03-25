<!-- Language Switcher -->
<div class="dropdown language-dropdown" style="margin-right: 15px;margin-top:1rem;">
    <a href="#" class="nav-link nav-link-lg d-flex align-items-center" data-toggle="dropdown" 
       title="{{ trans('common.language') }}">
       
        <i class="fas fa-globe fa-lg mr-2"></i>

        <span class="ml-2" style="font-size: 0.9rem;">
            {{ \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getCurrentLocaleNative() }}
        </span>
    </a>

    <div class="dropdown-menu dropdown-menu-right">
        @foreach(\Mcamara\LaravelLocalization\Facades\LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
            <a class="dropdown-item @if(app()->getLocale() === $localeCode) active @endif" 
               href="{{ \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL($localeCode) }}">
                {{ $properties['native'] }}
            </a>
        @endforeach
    </div>
</div>

<style>
    .language-dropdown .dropdown-menu {
        min-width: 200px;
    }
    .language-dropdown .dropdown-item {
        padding: 0.75rem 1.5rem;
    }
    .language-dropdown .dropdown-item.active {
        background-color: #f3f4f6;
        color: #1f2937;
        font-weight: 600;
    }
    .language-dropdown .dropdown-item:hover {
        background-color: #e5e7eb;
    }
</style>
