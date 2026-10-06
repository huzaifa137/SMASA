@extends('layouts-side-bar.master')

@section('content')
    <div class="side-app">
        <style>
            .preview-card {
                background: #fff;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
            }

            .revert-table td,
            .revert-table th {
                font-size: 14px;
                padding: 6px 10px;
            }
        </style>

        <div class="row">
            <div class="col-12">
                <div class="card bg-primary">
                    @include('layouts.class-buttons')
                    <div class="card-body bg-light">

                        <h4 class="mb-3">Switch Back to the Default Subject List</h4>

                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <p>
                            Your classes will go back to the shared, system-wide subject list.
                            <strong>Nothing is deleted:</strong> your own subject list stays saved, and you can switch
                            to it again later exactly as you left it. Teachers, marks, exam settings and pass slips
                            are not affected.
                        </p>

                        <div class="preview-card">
                            <h6 class="text-uppercase text-muted mb-2">Will switch cleanly</h6>
                            <p class="mb-0">
                                <strong>{{ $keepCount }}</strong> class subject(s) came from the default list and go
                                straight back to it.
                            </p>
                        </div>

                        @if ($renamed->isNotEmpty())
                            <div class="preview-card">
                                <h6 class="text-uppercase text-muted mb-2">Names that will change back</h6>
                                <p class="text-muted">These subjects were renamed in your own list. After switching they show
                                    their default name.</p>
                                <table class="table table-sm revert-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Class</th>
                                            <th>Your name</th>
                                            <th>Default name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($renamed as $r)
                                            <tr>
                                                <td>{{ $r['class'] }}</td>
                                                <td>{{ $r['custom_name'] }}</td>
                                                <td>{{ $r['default_name'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if ($matched->isNotEmpty())
                            <div class="preview-card">
                                <h6 class="text-uppercase text-muted mb-2">Subjects you added that match a default subject</h6>
                                <p class="text-muted">Their marks are moved over to the matching default subject automatically.
                                </p>
                                <table class="table table-sm revert-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Class</th>
                                            <th>Your subject</th>
                                            <th>Becomes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($matched as $r)
                                            <tr>
                                                <td>{{ $r['class'] }}</td>
                                                <td>{{ $r['custom_name'] }}</td>
                                                <td>{{ $r['default_name'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <form action="{{ route('school.custom-subjects.revert.confirm') }}" method="POST" id="revertForm">
                            @csrf

                            @if ($unmatched->isNotEmpty())
                                <div class="preview-card" style="border-left: 4px solid #f59e0b;">
                                    <h6 class="text-uppercase text-warning mb-2">No default equivalent</h6>
                                    <p>
                                        These subjects exist only in your own list, so the default list has nothing to
                                        replace them with. Remove them from their classes first, or tick the box below to
                                        drop them from these classes while switching. Marks already entered for them stay in
                                        the database but will no longer appear on screens or pass slips.
                                    </p>
                                    <table class="table table-sm revert-table">
                                        <thead>
                                            <tr>
                                                <th>Class</th>
                                                <th>Your subject</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($unmatched as $r)
                                                <tr>
                                                    <td>{{ $r['class'] }}</td>
                                                    <td>{{ $r['custom_name'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="discard_unmatched" value="1"
                                            id="discardUnmatched">
                                        <label class="form-check-label" for="discardUnmatched">
                                            Drop these subjects from their classes and switch anyway
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary" id="revertSubmitBtn">
                               <i class="fas fa-arrow-right-arrow-left me-1"></i> Switch back to default subjects
                            </button>
                            <a href="{{ route('school.custom-subjects.manage') }}"
                                class="btn btn-outline-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
@endsection


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('revertForm');
        const submitBtn = document.getElementById('revertSubmitBtn');

        if (form && submitBtn) {
            submitBtn.addEventListener('click', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Switch back to default subjects?',
                    text: 'Your classes will go back to the shared, system-wide subject list. Your own subject list stays saved.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, switch back',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }
    });
</script>