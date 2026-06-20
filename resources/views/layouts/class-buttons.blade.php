@php
    use App\Http\Controllers\Helper;
    use App\Helpers\PermissionHelper;
@endphp



<div class="card-header">
    <div class="row w-100 g-2">
        <div class="col-12 col-sm-4 mb-2 mb-sm-0">
            <a href="{{ route('all.my-classes') }}" class="btn btn-white text-dark w-100 rounded">
                <i class="fas fa-chalkboard-teacher me-2"></i> My Classes
            </a>
        </div>
        @if (Helper::isTechSateAdminOrSchoolAdminsOrTechSateSalesRepresentatives())

            @if(PermissionHelper::canFeature('manage_streams'))
                <div class="col-12 col-sm-4 mb-2 mb-sm-0">
                    <a href="{{ route('manage.classes') }}" class="btn btn-white text-dark w-100 rounded">
                        <i class="fas fa-sliders-h me-2"></i> Manage Classes
                    </a>
                </div>
            @endif

            @if(PermissionHelper::canFeature('add_class'))
                <div class="col-12 col-sm-4 mb-2 mb-sm-0">
                    <a href="{{ route('school.create-class') }}" class="btn btn-white text-dark w-100 rounded">
                        <i class="fas fa-plus-circle me-2"></i> Add New Class
                    </a>
                </div>
            @endif
        @endif

    </div>
</div>