<?php
use App\Http\Controllers\Helper; 
?>
@extends('layouts-side-bar.master')
@section('content')
    <div class="side-app">

        <div class="row">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card bg-primary">
                    <div class="card-header">
                        @include('layouts.subjects-buttons')
                    </div>
                </div>
            </div>
        </div>

<div class="row">
    <div class="col-lg-12">
        <!-- Results -->
        <div class="card mt-4" id="resultsCard">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">All Students</h5>
            </div>
            <div class="card-body bg-white" id="searchResults">

                @if ($groupedStudents->isEmpty())
                    <p>No students found.</p>
                @else
                    @foreach ($groupedStudents as $senior => $streams)
                        <div class="senior-group">

                            <h4 class="text-primary">Class : <span class="text-dark fw-bold">{{  Helper::item_md_name($senior) }}</span></h4>
                            @foreach ($streams as $stream => $students)
                                <div class="stream-group">
                                    <!-- Stream Group Title -->
                                    <h5 class="text-secondary">Stream: {{ Helper::item_md_name($stream) }}</h5>

                                    <!-- Stream Table -->
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Admission Number</th>
                                                <th>Gender</th>
                                                <th>Phone</th>
                                                <th>UCE Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($students as $key => $student)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td> <!-- Row number -->
                                                    <td>{{ $student->firstname }} {{ $student->lastname }}</td>
                                                    <td>{{ $student->admission_number }}</td>
                                                    <td>{{ $student->gender }}</td>
                                                    <td>{{ $student->primary_contact }}</td>
                                                    <td>{{ $student->uce_score }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>




    </div>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

@endsection

@section('js')

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>

    <script>

    </script>
@endsection