<div class="row w-100 g-2">
    <div class="col-12 col-sm-6 mb-2 mb-sm-0">
        <a href="{{ route('add-users') }}" class="btn btn-info w-100">
            <i class="fas fa-chalkboard-teacher me-2"></i> {{ trans('common.add_users') }} 
        </a>
    </div>

    <div class="col-12 col-sm-6 mb-2 mb-sm-0">
        <a href="{{ route('assign.users.permissions') }}" class="btn btn-info w-100">
            <i class="fas fa-sliders-h me-2"></i>{{ trans('common.manage_user_permissions') }}  
        </a>
    </div>
</div>