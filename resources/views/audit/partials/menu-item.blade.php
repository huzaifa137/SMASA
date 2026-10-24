@if (\App\Audit\AuditAccess::canView())
    <li class="slide">
        <a class="side-menu__item" href="{{ route('audit.index') }}">
            <i class="fas fa-clipboard-list fa-2x mr-3"></i>Audit Trail
        </a>
    </li>
@endif
