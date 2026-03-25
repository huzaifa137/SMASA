<div class="row w-100 g-2">
    <div class="col-12 col-sm-4 mb-2 mb-sm-0">
        <a href="{{ route('all.users.roles') }}" class="btn btn-info w-100">
            <i class="fas fa-chalkboard-teacher me-2"></i>{{ trans('common.user_roles') }}  
        </a>
    </div>
    <div class="col-12 col-sm-4 mb-2 mb-sm-0">
        <a href="{{ route('all.users.permissions') }}" class="btn btn-info w-100">
            <i class="fas fa-sliders-h me-2"></i> {{ trans('common.user_access_configuration') }} 
        </a>
    </div>
    <div class="col-12 col-sm-4 mb-2 mb-sm-0">
        <a href="{{ route('assign.users.permissions') }}" class="btn btn-info w-100">
            <i class="fas fa-plus-circle me-2"></i> {{ trans('common.assign_permissions') }} 
        </a>
    </div>
</div>