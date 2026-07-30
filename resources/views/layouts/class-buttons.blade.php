@php
    use App\Http\Controllers\Helper;
    use App\Helpers\PermissionHelper;
    use App\Models\School;

    $customSubjectsSchool = Session('LoggedSchool') ? School::find(Session('LoggedSchool')) : null;
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

            @if ($customSubjectsSchool && $customSubjectsSchool->custom_subjects_enabled)
                <div class="col-12 col-sm-4 mb-2 mb-sm-0">
                    @if ($customSubjectsSchool->custom_subjects_active)
                                    <a href="{{ route('school.custom-subjects.manage') }}"
                                        class="w-100 mt-2 d-flex align-items-center justify-content-center" style="
                                display: flex;
                                padding: 12px 20px;
                                text-decoration: none;
                                background: linear-gradient(135deg, #f59e0b, #f97316);
                                color: #fff;
                                border-radius: 12px;
                                font-weight: 600;
                                font-size: 15px;
                                box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35);
                                transition: all .3s ease;
                                border: 1px solid rgba(255,255,255,.15);
                           " onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 28px rgba(16,185,129,.45)';"
                                        onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 8px 20px rgba(16,185,129,.35)';">
                                        <i class="fas fa-book me-2"></i>&nbsp;
                                        <span>Manage My Subjects</span>
                                    </a>
                    @else
                        <a href="{{ route('school.custom-subjects.switch') }}"
                            class="w-100 mt-2 d-flex align-items-center justify-content-center" style="
                            display: flex;
                            padding: 12px 20px;
                            text-decoration: none;
                            background: linear-gradient(135deg, #f59e0b, #f97316);
                            color: #fff;
                            border-radius: 12px;
                            font-weight: 600;
                            font-size: 15px;
                            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.35);
                            transition: all .3s ease;
                            border: 1px solid rgba(255,255,255,.15);
                        "
                            onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 28px rgba(249,115,22,.45)';"
                            onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 8px 20px rgba(249,115,22,.35)';">
                            <i class="fas fa-exchange-alt me-2"></i> &nbsp;
                            <span>Switch to Custom Subjects</span>
                        </a>
                    @endif
                </div>
            @endif
        @endif

    </div>
</div>