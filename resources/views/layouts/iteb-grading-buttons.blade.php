<div class="row w-100 g-2">

    <div class="col-12 col-sm-4 mb-2">
        <a href="{{ url('enter-marks') }}" class="btn btn-white text-dark w-100">
            <i class="fa-solid fa-pen-to-square me-2"></i> {{ trans('common.enter_marks') }}
        </a>
    </div>

    <div class="col-12 col-sm-4 mb-2">
        <a href="{{ url('/iteb/grading-summary') }}" class="btn btn-white text-dark w-100">
            <i class="fa-solid fa-chart-column me-2"></i> {{ trans('common.grading') }}
        </a>
    </div>

    <div class="col-12 col-sm-4 mb-2">
        <a href="{{ route('school.passwords.setup') }}" class="btn btn-white text-dark w-100">
            <i class="fa-solid fa-key me-2"></i> {{ trans('common.schools_and_passwords') }}
        </a>
    </div>
</div>
