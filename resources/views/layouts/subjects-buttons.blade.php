<style>
    /* These buttons must never change color on hover/focus/active — only
       move slightly. Some environments (dark-mode browser extensions,
       WebView "force dark" color inversion, default focus rings) can
       otherwise repaint a white button as black with a red/blue border.
       Every state below is pinned explicitly with !important so nothing
       else can win, and forced-color-adjust/outline are switched off so
       the browser can't substitute its own colors here. */
    .subjects-nav-btn,
    .subjects-nav-btn:hover,
    .subjects-nav-btn:focus,
    .subjects-nav-btn:focus-visible,
    .subjects-nav-btn:active,
    .subjects-nav-btn:visited {
        background-color: #ffffff !important;
        background-image: none !important;
        color: #424e79 !important;
        border: 1px solid #e7e9f1 !important;
        box-shadow: none !important;
        outline: none !important;
        forced-color-adjust: none;
        -webkit-tap-highlight-color: transparent;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .subjects-nav-btn:hover,
    .subjects-nav-btn:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(20, 20, 43, 0.08) !important;
    }

    .subjects-nav-btn:active {
        transform: translateY(0);
    }
</style>

<div class="row w-100 g-2">
    <div class="col-12 col-sm-3 mb-2">
        <a href="{{ route('students.individual.search') }}"
            class="btn btn-white text-dark w-100 rounded subjects-nav-btn">
            <i class="fas fa-search me-2"></i>Search Student
        </a>
    </div>

    <div class="col-12 col-sm-3 mb-2">
        <a href="{{ route('students.add.new.student') }}"
            class="btn btn-white text-dark w-100 rounded subjects-nav-btn">
            <i class="fas fa-user-plus me-2"></i>Add Students
        </a>
    </div>

    <div class="col-12 col-sm-3 mb-2">
        <a href="{{ route('students.all.students') }}"
            class="btn btn-white text-dark w-100 rounded subjects-nav-btn">
            <i class="fas fa-users me-2"></i>All Students
        </a>
    </div>

    <div class="col-12 col-sm-3 mb-2">
        <a href="{{ route('students.consolidation') }}"
            class="btn btn-white text-dark w-100 rounded subjects-nav-btn">
            <i class="fas fa-code-branch me-2"></i>Consolidate Students
        </a>
    </div>

    {{-- <div class="col-12 col-sm-3 mb-2">
        <a href="{{ route('students.all.students') }}" class="btn btn-white text-dark w-100 rounded">
            <i class="fas fa-upload me-2"></i>Upload Images
        </a>
    </div> --}}
</div>