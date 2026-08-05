<?php

use App\Http\Controllers\ClassandSubjectController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\GradingController;
use App\Http\Controllers\ItebController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SchoolsController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExaminationController;
use App\Http\Controllers\ExaminationReportController;
use App\Http\Controllers\GradingSchemeController;
use App\Http\Controllers\AssessmentScaleController;
use App\Http\Controllers\UserRightsAndPreviledges;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\TeacherPasswordResetController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\StudentIdCardController;
use App\Http\Controllers\TeacherIdCardController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\CardScanController;
use App\Http\Controllers\UserRightsController;
use App\Http\Controllers\NotificationController;


Route::get('/logout', function () {
    session()->flush();

    return redirect('/');
})->name('logout');

Route::get('/language/{language}', [LanguageController::class, 'switchLanguage'])->name('language.switch');
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localized']], function () {

    Route::controller(UserController::class)->group(function () {
        Route::group(['middleware' => ['AdminAuth']], function () {
            Route::get('/', 'homePage')->name('home.page');
            Route::get('/copy-houses-to-schools', 'copyHousesToSchools');
        });

        Route::group(['prefix' => '/users'], function () {

            Route::get('/user-logout', 'adminLogout')->name('user-logout');
            Route::post('/admin-logout', 'adminLogout')->name('admin-logout');
            Route::get('/student-logout', 'studentLogout')->name('student-logout');

            Route::group(['middleware' => ['AdminAuth']], function () {
                Route::get('/forgot-password', [TeacherPasswordResetController::class, 'showForgotPasswordForm'])->name('forgot-password');
                Route::get('/login', 'login')->name('users.login');
                Route::post('auth-user-check', 'checkUser')->name('auth-user-check');
                Route::get('/users-profile', 'userProfile')->name('users-profile');
                Route::get('/users-register', 'userRegister');
                Route::get('/users-information', 'userInformation')->name('users.user-information');
                Route::get('user-account-information/{id}', [UserController::class, 'userAccountInformation'])->name('users.account-information');
                Route::get('/home-page', 'homePage')->name('home.page');
                Route::get('/privacy-policy', 'privacyPolicy')->name('privacy-policy');
                Route::get('/public-portal', 'publicPortal')->name('public.portal');
                Route::get('/register', 'register')->name('users.register');
                Route::get('/edit-user-information', 'editUserInformation');
                Route::get('/edit-specific-user/{userid}', 'editSpecificUser')->name('users.edit-specific-user');
                Route::get('/terms-and-conditions', 'user_terms_and_conditions')->name('users.terms-and-conditions');
                Route::get('/users/delete-user/{id}', 'deleteUserAccount')->name('users.delete-user');
            });

            Route::get('/teacher-password-reset-form', [TeacherPasswordResetController::class, 'showResetPasswordForm'])->name('teacher.password.reset.form');
            Route::post('/teacher-send-reset-link', [TeacherPasswordResetController::class, 'sendResetLink'])->name('teacher.send.reset.link');
            Route::post('/teacher-password-reset', [TeacherPasswordResetController::class, 'resetPassword'])->name('teacher.password.reset');
            Route::post('auth-user-selected-school', 'authUserSelectedSchool')->name('auth-user-selected-school');
            Route::post('store-internal-user', 'storeInternalUser')->name('store-internal-user');
            Route::post('update-internal-user', 'storeUpdatedInternalUser')->name('update.internal-user');
            Route::post('save-role', 'saveUserRole')->name('save-role');
            Route::post('store-role-update', 'storeRoleUpdate')->name('store-role-update');
            Route::post('store-updated-information', 'storeUpdatedInformation')->name('store-updated-information');
        });

        Route::get('password/reset/{id}', 'createNewPassword')->name('password/reset');
        Route::get('password/set-password/{id}', 'createFirstPassword')->name('password.FirstPassword');
        Route::post('auth.save', 'save')->name('auth.save');
        Route::post('regenerate-otp', 'regenerateOTP')->name('regenerate-otp');
        Route::post('user-generate-forgot-password-link', 'generateForgotPasswordLink')->name('user-generate-forgot-password-link');
        Route::post('user-store-new-password', 'store_new_password')->name('user-store-new-password');
        Route::post('user-store-first-password', 'store_first_password')->name('user-store-first-password');
        Route::post('supplier-user-otp-verification', 'supplierOtpVerification')->name('supplier-user-otp-verification');
        Route::get('reload-captcha', 'reload_captcha')->name('reload-captcha');
    });

    Route::controller(MasterDataController::class)
        ->middleware(['module:master_data'])
        ->middleware(['AdminAuth'])
        ->group(function () {
            Route::group(['prefix' => 'master-data'], function () {
                Route::get('master-code-to-data', 'masterCodeToData')->name('master-code-to-data');

                Route::get('/load-data', 'loadData')->name('load.data');
                Route::get('master-table', 'master_table')->name('master-table');
                Route::get('master-code', 'master_code')->name('master-code');
                Route::get('requisition-documents', 'requisitionDocuments');
                Route::get('travel-requisition-documents', 'travelRequisitionDocuments');
                Route::get('supplier-prequalification-criteria', 'supplierPrequalificationEvaluationCriteria');
                Route::post('store-prequalification-criteria', 'storePrequalificationCriteria')->name('store-prequalification-criteria');

                Route::get('edit-record/{id}', 'editRecord');
                Route::get('add-record', 'addRecord')->name('add-record');
                Route::get('add-code', 'addMasterCode')->name('add-code');
                Route::get('edit-code/{id}', 'editMasterCode');
                Route::get('master-code-list/{id}', 'masterCodeList')->name('master-code-list');
                Route::get('master-code-list', 'masterCodeList');
                Route::get('edit-supplier-document/{id}', 'editSupplierDocument');
                Route::post('/store-requisition-document', 'storeRequisitionDocument')->name('master-data/store-requisition-document');
            });

            Route::post('store-travel-requisition-document', 'storeTravelRequisitionDocument')->name('store-travel-requisition-document');
            Route::post('update-supplier-document', 'updateSupplierDocument')->name('update-supplier-document');
            Route::post('update-master-record', 'updateMasterrecord')->name('update-master-record');
            Route::post('update-master-code', 'updateMasterCode')->name('update-master-code');
            Route::post('send-master-code', 'sendMasterCode')->name('send-master-code');
            Route::post('add-new-record', 'addNewRecord')->name('add-new-record');

            Route::get('delete-supplier-document/{id}', 'deleteSupplierDocument');
            Route::get('delete-record/{id}', 'deleteRecord');
            Route::get('delete-code/{id}', 'deleteCode');
        });

    Route::controller(StudentController::class)->group(function () {
        Route::group(['prefix' => '/users'], function () {
            Route::group(['middleware' => ['AdminAuth']], function () {
                Route::get('/register', 'register')->name('users.register');
                Route::get('/terms-and-conditions', 'user_terms_and_conditions')->name('users.terms-and-conditions');
                Route::get('/user-otp', function () {
                    $userId = session('userId');
                    $userEmail = session('userEmail');
                    $userPassword = session('userPassword');

                    if (!$userId || !$userEmail) {
                        return redirect()->route('users.login')->with('fail', 'You must be logged in');
                    }

                    return view('users.otp', compact(['userId', 'userEmail', 'userPassword']));
                });
            });

            Route::post('user-account-creation', 'userAccountCreation')->name('user-account-creation');
            Route::post('contact-message-information', 'contactMessageInformation')->name('contact-message-information');
        });
        Route::get('/clear-session', 'flushSession');
    });

    Route::controller(StudentController::class)->group(function () {
        Route::group(['middleware' => ['AdminAuth']], function () {
            Route::group(['prefix' => 'admin'], function () {
                Route::get('/dashboard', 'adminDashboard')->name('student.dashboard');
                Route::get('/profile', 'studentProfile')->name('student.profile');
                Route::get('/edit-student-profile', 'editStudentProfile');
            });
        });
        
        Route::get('/students/streams', 'getStreamsForClass')->name('students.streams.ajax');
        Route::get('/select-current-school', 'selectCurrentSchool')->name('select.current.school');
    });

    Route::controller(SchoolController::class)
        // ->middleware(['module:school_management'])
        ->group(function () {
            Route::get('create-school', 'createSchool')->name('school.create-school');
            Route::get('term-dates/{schoolId}', 'termDates')->name('school.term-dates');
            Route::get('all-schools', 'allSchools')->name('school.allSchools');
            Route::get('/edit-school/{id}/', 'editSchool')->name('edit.school');
            Route::get('/school-profile', 'schoolProfile')->name('profile.school');
            Route::get('/school-individual-profile/{id}', 'schoolIndividualProfile')->name('profile.individual.school');
            Route::get('/school-options/{id}/', 'schoolOptions')->name('school.options');

            Route::delete('/school/{schoolId}', 'deleteSchool')->name('school.delete');

            Route::post('/create/new/schools/', 'createNewSchool')->name('create.new-school');
            Route::post('/update-school', 'updateSchool')->name('update.school');
            Route::post('/store-school-profile', 'storeSchoolProfile')->name('schools.store.profile');
            Route::post('/school/configure', 'configureSchoolOptions')->name('school.configure');
            Route::post('/schools/{id}/change-status', 'changeStatus');
            Route::post('/schools/{id}/toggle-custom-subjects', 'toggleCustomSubjects')->name('school.toggle-custom-subjects');

            Route::get('admin-user', 'adminUser')->name('admin.user');
            Route::get('student-user', 'studentUser')->name('student.user');

            Route::get('/add-academic-year', 'addAcademicYear')->name('add-academic-year');
            Route::post('/academic-years', 'storeYear')->name('academic-years.store');

            Route::patch('/academic-years/{id}/activate', 'activate')->name('academic-years.activate');
            Route::patch('/academic-years/{id}/deactivate', 'deactivate')->name('academic-years.deactivate');

            Route::delete('/academic-years/{id}', 'destroy')->name('academic-years.destroy');
            Route::put('/academic-years/{id}', 'updateYear')->name('academic-years.update');

            Route::delete('/academic-years-terms/{id}', 'destroyTerm')->name('academic-years.term.destroy');
            Route::post('/store-term-dates', 'storeTermDate')->name('term-dates.store');
            Route::post('/select-school', 'selectSchool')->name('school.select');
            Route::post('/school/clear', 'clearSchool')->name('school.clear');
            Route::post('/term-dates/toggle-active', 'toggleActive')->name('term-dates.toggle-active');

        });

    // Routes with module:classes middleware
    Route::controller(TeacherController::class)
        ->middleware(['module:classes'])
        ->group(function () {
            Route::get('add-teachers', 'addTeachers')->name('school.add-teachers');
            Route::get('/teachers', 'allTeachers')->name('teachers.all');
            Route::get('/school-teachers', 'schoolTeachers')->name('school.teachers');
            Route::post('/teacher/update-role/{id}', 'updateTeacherRole')->name('teacher.update.role');
            Route::get('/individual-school-teachers/{id}', 'individualSchoolTeachers')->name('individual.school.teachers');
            Route::get('/teacher-profile/{id}', 'teacherProfile')->name('teacher.profile');
            Route::get('/update-teacher-profile/{id}', 'updateteacherProfile')->name('update.teacher.profile');
            Route::get('/teacher/profile/{id}/data', 'getTeacherData')->name('teacher.data');
            Route::post('/store-teachers', 'storeTeacher')->name('teachers.store');
            Route::post('/teachers/update/{teacher}', 'storeUpdatedTeacherProfile')->name('teachers.update');
            Route::delete('/teachers/{id}', 'destroyTeacher')->name('teachers.destroy');

            // Teacher Bulk Import
            Route::get('/teachers/bulk-import', 'bulkImportTeacherForm')->name('teachers.bulk.import.form');
            Route::post('/teachers/bulk-import', 'bulkImportTeachers')->name('teachers.bulk.import');
            Route::get('/teachers/download-template', 'downloadTeacherTemplate')->name('teachers.download.template');

            // Teacher Account Status
            Route::post('/teacher/{id}/status', 'updateTeacherStatus')->name('teacher.update.status');
            Route::get('/teacher/{id}/status', 'getTeacherStatus')->name('teacher.get.status');
        });

    // Password update route without module:classes middleware
    Route::controller(TeacherController::class)
        ->group(function () {
            Route::post('/teacher/update-password', 'updatePassword')->name('teacher.update-password');
        });

    Route::controller(\App\Http\Controllers\CustomSubjectController::class)->middleware(['module:classes'])->group(function () {
        Route::get('/custom-subjects/switch', 'showSwitchPrompt')->name('school.custom-subjects.switch');
        Route::post('/custom-subjects/switch', 'confirmSwitch')->name('school.custom-subjects.confirm');
        Route::get('/custom-subjects', 'manage')->name('school.custom-subjects.manage');
        Route::post('/custom-subjects', 'store')->name('school.custom-subjects.store');
        Route::put('/custom-subjects/{subject}', 'update')->name('school.custom-subjects.update');
        Route::delete('/custom-subjects/{subject}', 'destroy')->name('school.custom-subjects.destroy');
    });

    Route::controller(ClassandSubjectController::class)->middleware(['module:classes'])->group(function () {

        Route::get('create-class', 'createClass')->name('school.create-class');
        Route::get('all-my-classes', 'allMyClasses')->name('all.my-classes');
        Route::get('manage-classes', 'manageClasses')->name('manage.classes');
        Route::get('assign-teachers', 'teacherAssignments')->name('school.assign-teachers');
        Route::get('/subjects-by-class/{classId}', 'getSubjectsByClass');
        Route::get('manage-class-streams/{id}', 'manageClassStreams')->name('manage.class.streams');
        Route::get('/class-stream-subjects/{classId}/{streamId}', 'attachedStreamSubjects')->name('class.stream.subjects');
        Route::get('edit-class-subjects/{classId}/{streamId}', 'editClassSubjects')->name('school.edit-class-subject');
        Route::get('/get-streams/{senior}', 'getStreams');

        Route::post('/schools/class/store', 'storeClass')->name('schools.class-store');
        Route::post('/assign-class-supervisor', 'assignSupervisor')->name('class.assignSupervisor');
        Route::post('/remove-class-supervisor', 'removeSupervisor')->name('class.removeSupervisor');
        Route::post('/remove-class-teacher', 'removeClassTeacher')->name('class.removeClassTeacher');
        Route::post('/assign-class-teacher', 'assignClassTeacher')->name('class.assignClassTeacher');

        Route::delete('/streams/{stream}', 'deleteStream')->name('streams.delete');
        Route::delete('/classes/{id}', 'destroyClass')->name('classes.destroy');

        Route::put('/assign-subjects/{assignmentId}', 'updateClassSubjects')->name('assign.subjects.update');

        Route::post('/assign-class-subject-teacher-one', 'assignSubjectTeacher1')->name('class.assignSubjectTeacher1');
        Route::post('/remove-class-subject-teacher-one', 'removeSubjectTeacher1')->name('class.removeSubjectTeacher1');

        Route::post('/assign-class-subject-teacher-two', 'assignSubjectTeacher2')->name('class.assignSubjectTeacher2');
        Route::post('/remove-class-subject-teacher-two', 'removeSubjectTeacher2')->name('class.removeSubjectTeacher2');

    });

    Route::controller(UserRightsAndPreviledges::class)
        ->group(function () {
            Route::post('/update-user-information', 'updateUserInformation')->name('users.update.information');
            Route::group(['middleware' => ['AdminAuth']], function () {
                Route::group(['prefix' => '/user-rights-and-previledges'], function () {
                    Route::get('/setup', 'setup')->name('all.roles.setup');
                    Route::get('/all-roles', 'allRoles')->name('all.users.roles');
                    Route::get('/all-permissions', 'allPermissions')->name('all.users.permissions');
                    Route::get('/assign-permissions', 'assignPermissions')->name('assign.users.permissions');

                    Route::get('add-users', 'addUsers')->name('add-users');
                });

                // routes/web.php
    
                Route::post('/roles/add-user', 'addUserToRole')->name('roles.add-user');
                Route::post('/roles/remove-user', 'deleteUserFromRole')->name('roles.removeUser');

                Route::get('/users/{id}/details', 'getUserDetails');
                Route::get('/roles/{id}', 'editRole');
                Route::put('/roles/{id}', 'updateRole');

                Route::post('/store-role', 'storeRole')->name('store.role');
                Route::post('/store-permission-role', 'storePermissionRole')->name('store.permission.role');
                Route::post('/permissions/store-multiple', 'storeMultiplePermissions')->name('store.multiple.permissions');

                Route::delete('/roles/{id}', 'deleteRole');
                Route::delete('/permissions/delete', 'destroyGroup')->name('permissions.delete');
                Route::delete('/user/{userId}', 'deleteUser')->name('user.delete');

                Route::post('/assign-permissions/{roleId}', 'storeRolePermissions')->name('storeRolePermissions');
                Route::post('/remove-permissions/{roleId}/remove', 'removePermission');
                Route::post('/assign-user-to-role', 'assignUserToRole')->name('assignUserToRole');
                Route::post('/remove-user-from-role', 'removeUserFromRole')->name('removeUserFromRole');

                Route::post('/store-new-user', 'storeNewUser')->name('users.store.new.user');

                Route::post('/users/{id}/change-status', 'changeStatus');
            });

            Route::controller(StudentController::class)
                ->prefix('students')
                ->middleware(['module:students'])
                ->group(function () {
                    Route::group(['middleware' => ['AdminAuth']], function () {
                        Route::get('students-dashboard', 'studentPortal')->name('all.students.dashboard');
                        Route::get('update-profile', action: 'updateProfiles')->name('students.update.profile');
                        Route::get('/search', 'searchStudent')->name('students.individual.search');
                        Route::get('/all-students', 'allStudents')->name('students.all.students');
                        Route::get('/search/ajax', 'searchAjax')->name('students.search.ajax');

                        Route::get('/export/{schoolId}/{type}', 'exportStudents')->name('students.export');

                        Route::get('/students/{student}/edit', 'edit')->name('students.edit');

                        Route::get('/Information/{id}', 'showStudentInformation');

                        Route::get('/add-new-student', 'addNewStudent')->name('students.add.new.student');

                        Route::post('/students/store', 'storeStudent')->name('students.store');

                        Route::get('/transfer-form', 'moveStudentForm')->name('students.transfer');

                        Route::get('/streams/by-class', 'getStreamsByClass')->name('streams.by.class');
                        Route::get('/students/search', 'searchStudentsByClassStream')->name('students.search');
                        Route::get('/students/search', 'searchStudentInformation')->name('students.search');
                        Route::post('/students/move', 'moveStudent')->name('students.move');

                        Route::get('students/generate-id', 'generateStudentID')->name('students.generate-id');
                        Route::get('/view/{id}', 'viewStudent')->name('students.view');
                        Route::post('/update/{id}', 'updateStudent');

                        Route::delete('/delete/{student}', 'destroyStudent')->name('students.destroy');

                        // Student Bulk Import
                        Route::get('/bulk-import', 'bulkImportStudentForm')->name('students.bulk.import.form');
                        Route::post('/bulk-import', 'bulkImportStudents')->name('students.bulk.import');
                        Route::get('/download-template', 'downloadStudentTemplate')->name('students.download.template');

                    });
                });
        });

    // ── Student ID Cards ──────────────────────────────────────────────────
    Route::controller(StudentIdCardController::class)
        ->prefix('student-id-cards')
        ->group(function () {
            Route::group(['middleware' => ['AdminAuth']], function () {
                Route::get('/', 'index')->name('id-cards.index');
                Route::get('/create', 'create')->name('id-cards.create');
                Route::post('/generate', 'generate')->name('id-cards.generate');
                Route::post('/generate-single', 'generateSingle')->name('id-cards.generate.single');
                Route::get('/preview/{cardId}', 'preview')->name('id-cards.preview');
                Route::get('/print/{cardId}', 'printCard')->name('id-cards.print');
                Route::get('/print-bulk', 'printBulk')->name('id-cards.print.bulk');
                Route::patch('/revoke/{cardId}', 'revoke')->name('id-cards.revoke');

                // ADD THIS MISSING ROUTE:
                Route::patch('/reactivate/{cardId}', 'reactivate')->name('id-cards.reactivate');

                Route::get('/scanner', 'scannerPage')->name('id-cards.scanner');
                Route::get('/stats', 'stats')->name('id-cards.stats');
                Route::get('/streams-by-senior', 'getStreamsBySenior')->name('id-cards.streams.by.senior');
                Route::get('/generate-preview', 'getStudentsPreview')->name('id-cards.generate.preview');
                Route::get('/search-students', 'searchStudents')->name('id-cards.search.students');
            });
            // Public verify endpoint (no auth – for QR scanning)
            Route::get('/verify/{cardNumber}', 'verify')->name('id-cards.verify');
        });
    // ─────────────────────────────────────────────────────────────────────


    // ── Teacher ID Cards ──────────────────────────────────────────────────
    Route::controller(TeacherIdCardController::class)
        ->prefix('teacher-id-cards')
        ->group(function () {
            Route::group(['middleware' => ['AdminAuth']], function () {
                Route::get('/', 'index')->name('teacher-id-cards.index');
                Route::get('/create', 'create')->name('teacher-id-cards.create');
                Route::post('/generate', 'generate')->name('teacher-id-cards.generate');
                Route::post('/generate-single', 'generateSingle')->name('teacher-id-cards.generate.single');
                Route::get('/preview/{cardId}', 'preview')->name('teacher-id-cards.preview');
                Route::get('/print/{cardId}', 'printCard')->name('teacher-id-cards.print');
                Route::get('/print-bulk', 'printBulk')->name('teacher-id-cards.print.bulk');
                Route::patch('/revoke/{cardId}', 'revoke')->name('teacher-id-cards.revoke');
                Route::patch('/reactivate/{cardId}', 'reactivate')->name('teacher-id-cards.reactivate');
                Route::get('/scanner', 'scannerPage')->name('teacher-id-cards.scanner');
                Route::get('/stats', 'stats')->name('teacher-id-cards.stats');
                Route::get('/search-teachers', 'searchTeachers')->name('teacher-id-cards.search.teachers');
            });
            Route::get('/verify/{cardNumber}', 'verify')->name('teacher-id-cards.verify');
        });
    // ─────────────────────────────────────────────────────────────────────

    Route::controller(ExamController::class)->group(function () {
        Route::group(['middleware' => ['AdminAuth']], function () {
            Route::get('/specific-school-students', 'schoolStudents')->name('all.specific.students');
            Route::get('/manage-exams', 'manageExams')->name('manage.exams');
            Route::get('/edit-exams', 'editExams')->name('edit.exams');
            Route::get('/upload-exams', 'uploadExams')->name('upload.exams');
            Route::get('/exams/{exam}/class/{class}/download', 'downloadClassList')->name('exams.download.classlist');
            Route::get('/generate-exams-results', 'calculateExamResults')->name('generate.exams.results');
            Route::get('/exams/{exam}/{class}/ranking', 'downloadRankedResults')->name('exams.download.ranked');
            Route::get('/exams/download/reportcard/{exam}/{class}', 'downloadReportCard')->name('exams.download.reportcard');

            Route::post('/store-created-exam', 'storeCreatedExam');
            Route::post('/exams/upload-results', 'uploadResults')->name('exams.upload.results');
            Route::post('/exams/compute-results', 'computeResults')->name('exams.compute.results');
        });
    });

    Route::controller(GradingController::class)->group(function () {
        Route::group(['middleware' => ['AdminAuth']], function () {
            Route::post('/store-created-examination', 'storeCreatedExamination');
            Route::get('/import-marks', 'importMarks')->name('import.marks');
            Route::get('/create-examination', 'createExamination')->name('create.examination');

            Route::get('/exam-years', 'getExamYears');
            Route::get('/exams-by-year/{year}', 'getExamsByYear');
            Route::get('/active-exams', 'getActiveExams');

            Route::get('/import-marks', 'importMarks')->name('import.marks');
            Route::get('/exam/results/{examId}', 'showExamResults')->name('exam.results');

            Route::get('/grading/dashboard', 'gradingDashboard')->name('grading.dashboard');

            Route::post('/toggle-exam-active', 'toggleExamActive')->name('toggle.exam.active');
            Route::post('/import/thanawi-results', 'importThanawiResults')->name('import.thanawi');
            Route::post('/import/idaad-results', 'importIdaadResults')->name('import.idaad');
        });
    });

    Route::controller(ItebController::class)->group(function () {

        Route::group(['middleware' => ['AdminAuth']], function () {

            Route::get('/search-iteb-students', 'searchItebStudents')->name('search.iteb.students');
            Route::get('/enter-marks', 'enterMarks')->name('enter.marks');

            Route::get('/class-allocation', 'enterMarks');
            Route::get('/class-allocation/filter', 'filter')->name('class.allocation.filter');

            Route::post('/iteb/save-marks', 'saveMarks')->name('iteb.save.marks');
            Route::post('/iteb/get-marks', 'getMarksForSubject')->name('iteb.get.marks');
            Route::post('iteb/get-subject-marks', 'getSubjectMarks')->name('iteb.get.subject.marks');

            Route::get('/iteb/grading-summary', 'gradingSummary')->name('iteb.grading.summary');
            Route::post('/iteb/process-grading', 'processGrading')->name('iteb.process.grading');
            Route::post('/iteb/save-grading-results', 'saveGradingResults')->name('iteb.save.grading');
            Route::get('/iteb/export-grading', 'exportGrading')->name('iteb.export.grading');

            Route::get('/iteb/analytics/dashboard', 'analyticsDashboard')->name('iteb.analytics.dashboard');
            Route::post('/iteb/analytics/school-ranking', 'getSchoolRanking')->name('iteb.analytics.school.ranking');
            Route::post('/iteb/analytics/student-ranking', 'getStudentRanking')->name('iteb.analytics.student.ranking');
            Route::post('/iteb/analytics/subject-analysis', 'getSubjectAnalysis')->name('iteb.analytics.subject.analysis');
            Route::post('/iteb/analytics/year-comparison', 'getYearComparison')->name('iteb.analytics.year.comparison');
            Route::post('/iteb/analytics/export-report', 'exportAnalyticsReport')->name('iteb.analytics.export');
            Route::get('/iteb/analytics/download/{format}', 'downloadReport')->name('iteb.analytics.download');

            Route::match(['get', 'post'], '/iteb/exam-statistics', 'examStatistics')->name('iteb.exam.statistics');

        });

        Route::get('/about', 'about')->name('about.us');
        Route::get('/contact', 'contact')->name('contact.us');

        Route::post('iteb/exam-statistics/download', 'downloadExamStatistics')->name('iteb.exam.statistics.download');
        Route::post('iteb/exam-statistics/download-excel', 'downloadExamStatisticsExcel')->name('iteb.exam.statistics.download.excel');
        Route::post('iteb/exam-statistics/download-pdf', 'downloadExamStatisticsPdf')->name('iteb.exam.statistics.download.pdf');
        Route::post('exam-statistics/download/students', 'downloadStudentsReport')->name('iteb.exam.statistics.download.students');
        Route::post('exam-statistics/download/schools', 'downloadSchoolsReport')->name('iteb.exam.statistics.download.schools');

    });

    Route::group(['middleware' => ['AdminAuth']], function () {

        Route::prefix('school-passwords')
            ->controller(ItebController::class)
            ->group(function () {

                Route::get('/setup', 'schoolPasswordsSetup')->name('school.passwords.setup');
                Route::post('/fetch', 'fetchPassword')->name('school.passwords.fetch');
                Route::post('/generate', 'generatePassword')->name('school.passwords.generate');
                Route::post('/save', 'savePassword')->name('school.passwords.save');

            });
    });

    Route::controller(SchoolsController::class)->group(function () {
        Route::group(['middleware' => ['SchoolAuth']], function () {

            Route::group(['prefix' => '/school'], function () {

                Route::get('/dashboard', 'schoolDashboard')->name('school.dashboard');
                Route::get('/grading-summary', 'schoolGradingSummary')->name('school.grading.summary');
                Route::post('/process-grading', 'processGrading')->name('school.process.grading');
                Route::post('iteb/grading/results/pdf', 'generateResultsPDF')->name('iteb.grading.results.pdf');
            });
        });

        Route::post('/school-passwords/export-all-pdf', 'exportAllPasswordsPDF')->name('school.passwords.export-all-pdf');
    });


    Route::prefix('examinations')
        ->name('examination.')
        ->controller(ExaminationController::class)
        ->middleware(['module:examinations'])
        ->middleware(['SchoolAuth'])
        ->group(function () {

            // Dashboard and CRUD
            Route::get('/', 'dashboard')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::post('/{id}/status', 'updateStatus')->name('updateStatus');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::get('/marks-entry-portal', 'marksEntryPortal')->name('marks-entry-portal');

            // Marks Entry
            Route::get('/{examId}/marks', 'marksEntry')->name('marks.entry');
            Route::get('/{examId}/marks/{classSubjectId}/students', 'marksEntrySubject')->name('marks.subject');
            Route::post('/{examId}/marks/save', 'saveMarks')->name('marks.save');
            Route::post('/{examId}/classes/{examClassId}/release', 'releaseClassResults')->name('classes.release');

            // Pass Slips
            Route::get('/{id}/passslips', 'passslipIndex')->name('passslips.index');
            Route::get('/{id}/passslips/class', 'passslipClass')->name('passslips.class');
            Route::get('/{id}/passslips/student/{studentId}', 'passslipStudent')->name('passslips.student');
            Route::get('/{id}/passslips/all', 'passslipAll')->name('passslips.all');
            Route::get('/{id}/passslips/settings', 'getPassslipSettings')->name('passslips.settings.get');
            Route::post('/{id}/passslips/settings', 'savePassslipSettings')->name('passslips.settings.save');

            // Examination Details and Status
            Route::get('/{exam}/details', 'getDetails')->name('details');
            Route::get('/{examination}/status', 'getStatus')->name('status');
            Route::post('/{examination}/update-status', 'updateExaminationStatus')->name('update-status');

            // Edit and Update Details
            Route::get('/{examination}/edit-details', 'editDetails')->name('edit-details');
            Route::post('/{examination}/update-details', 'updateDetails')->name('update-details');

        });

    // Grading Schemes (per-school customizable grade bands + scale)
    Route::prefix('examinations/grading-schemes')
        ->name('examination.grading-schemes.')
        ->controller(GradingSchemeController::class)
        ->middleware(['module:examinations'])
        ->middleware(['SchoolAuth'])
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::post('/{id}/update', 'update')->name('update');
            Route::post('/{id}/toggle-active', 'toggleActive')->name('toggle-active');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

    // Reports & Summaries (subject x student matrices, single-subject deep
    // dives, and grade-distribution / performance analysis across an exam)
    Route::prefix('examinations/reports')
        ->name('examination.reports.')
        ->controller(ExaminationReportController::class)
        ->middleware(['module:examinations'])
        ->middleware(['SchoolAuth'])
        ->group(function () {
            Route::get('/', 'index')->name('index');

            Route::get('/{examId}/class-summary', 'classSummary')->name('class-summary');
            Route::get('/{examId}/class-summary/pdf', 'classSummaryPdf')->name('class-summary.pdf');

            Route::get('/{examId}/subject-report', 'subjectReport')->name('subject-report');
            Route::get('/{examId}/subject-report/pdf', 'subjectReportPdf')->name('subject-report.pdf');

            Route::get('/{examId}/grade-analysis', 'gradeAnalysis')->name('grade-analysis');
        });

    // Assessment Scales (per-school customizable comment/mark scales for
    // subjects graded outside the normal numeric-marks system, e.g.
    // Nursery's "Early Years 1-3" scale — generalises what used to be
    // hardcoded in config('constants.early_years'))
    Route::prefix('examinations/assessment-scales')
        ->name('examination.assessment-scales.')
        ->controller(AssessmentScaleController::class)
        ->middleware(['module:examinations'])
        ->middleware(['SchoolAuth'])
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::post('/{id}/update', 'update')->name('update');
            Route::post('/{id}/toggle-active', 'toggleActive')->name('toggle-active');
            Route::delete('/{id}', 'destroy')->name('destroy');

            Route::get('/class-subjects/{classId}/{streamId}', 'classSubjects')->name('class-subjects');
            Route::post('/assign', 'assignToClassSubject')->name('assign');

            Route::get('/{id}/assign', 'assignPage')->name('assign-page');
            Route::post('/{id}/assign-bulk', 'assignBulk')->name('assign-bulk');
        });
});


// ─── TIMETABLE ────────────────────────────────────────────────────────
Route::prefix('timetable')
    ->name('timetable.')
    ->controller(TimetableController::class)
    ->middleware(['module:timetable'])
    ->middleware(['SchoolAuth'])
    ->group(function () {

        // Dashboard
        Route::get('/', 'dashboard')->name('dashboard');

        // Periods Management
        Route::prefix('periods')->name('periods.')->group(function () {
            Route::get('/', 'periods')->name('index');
            Route::post('/', 'storePeriod')->name('store');
            Route::put('/{id}', 'updatePeriod')->name('update');
            Route::delete('/{id}', 'destroyPeriod')->name('destroy');
        });

        // Timetable CRUD
        Route::prefix('manage')->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::get('/{id}/view', 'view')->name('view');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::post('/{id}/duplicate', 'duplicate')->name('duplicate');
            Route::patch('/{id}/status', 'updateStatus')->name('status');
        });

        // Slot Management (AJAX)
        Route::prefix('slot')->name('slot.')->group(function () {
            Route::post('/save', 'saveSlot')->name('save');
            Route::delete('/clear', 'clearSlot')->name('clear');
            Route::get('/get', 'getSlot')->name('get');
        });

        // Teacher Personal Timetable
        Route::get('/my-schedule', 'teacherTimetable')->name('teacher');
    });

// ─── ATTENDANCE ──────────────────────────────────────────────────────
Route::prefix('attendance')
    ->name('attendance.')
    ->controller(AttendanceController::class)
    ->middleware(['module:attendance'])
    ->middleware(['SchoolAuth'])
    ->group(function () {

        // Dashboard
        Route::get('/', 'dashboard')->name('dashboard');

        // Student Attendance
        Route::get('/students', 'studentAttendancePortal')->name('students');
        Route::get('/students/{classId}/{streamId}/take', 'takeStudentAttendance')->name('take');
        Route::post('/students/save', 'saveStudentAttendance')->name('students.save');
        Route::get('/students/report', 'studentAttendanceReport')->name('students.report');

        // Teacher Attendance
        Route::get('/teachers', 'teacherAttendancePage')->name('teachers');
        Route::post('/teachers/save', 'saveTeacherAttendance')->name('teachers.save');
        Route::post('/teachers/save-bulk', 'saveTeacherAttendanceBulk')->name('teachers.save.bulk');
        Route::get('/teachers/report', 'teacherAttendanceReport')->name('teachers.report');

        // AJAX helpers
        Route::get('/ajax/streams/{classId}', 'getStreamsByClass')->name('ajax.streams');
        Route::get('/ajax/class-summary', 'classAttendanceSummary')->name('ajax.summary');
    });


// ─── FINANCE MODULE ─────────────────────────────────────────────────────────

Route::prefix('finance')
    ->name('finance.')
    ->controller(FinanceController::class)
    ->middleware(['module:finance'])
    ->middleware(['SchoolAuth'])
    ->group(function () {

        // Dashboard
        Route::get('/', 'dashboard')->name('dashboard');

        Route::get('/streams-by-class', 'getStreamsByClass')->name('streams-by-class');
        Route::get('/students-by-stream', 'getStudentsByStream')->name('students-by-stream');

        // ── Fee Structures ──────────────────────────────────────────────────
        Route::prefix('fee-structures')->name('fee-structures.')->group(function () {
            Route::get('/', 'feeStructures')->name('index');
            Route::get('/create', 'createFeeStructure')->name('create');
            Route::post('/', 'storeFeeStructure')->name('store');
            Route::get('/{id}/edit', 'editFeeStructure')->name('edit');
            Route::put('/{id}', 'updateFeeStructure')->name('update');
            Route::delete('/{id}', 'deleteFeeStructure')->name('destroy');
        });

        // ── Fee Allocations ─────────────────────────────────────────────────
        Route::get('/fee-allocations', 'feeAllocations')->name('fee-allocations');
        Route::post('/allocate-fees', 'allocateFees')->name('allocate-fees');
        Route::get('/student-allocations', 'getStudentAllocations')->name('student-allocations');
        Route::get('/fee-allocation/{id}/data', 'getFeeAllocationData')->name('fee-allocation.data');
        Route::put('/fee-allocation/{id}', 'updateFeeAllocation')->name('fee-allocation.update');
        Route::delete('/fee-allocation/{id}', 'deleteFeeAllocation')->name('fee-allocation.delete');

        // ── Payments ────────────────────────────────────────────────────────
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', 'payments')->name('index');
            Route::get('/create', 'createPayment')->name('create');
            Route::post('/', 'storePayment')->name('store');
            Route::get('/{id}/receipt', 'receiptPdf')->name('receipt');
            Route::post('/{id}/reverse', 'reversePayment')->name('reverse');
        });

        // ── Expenses ────────────────────────────────────────────────────────
        Route::prefix('expenses')->name('expenses.')->group(function () {
            Route::get('/', 'expenses')->name('index');
            Route::get('/create', 'createExpense')->name('create');
            Route::post('/', 'storeExpense')->name('store');
            Route::get('/{id}/edit', 'editExpense')->name('edit');
            Route::put('/{id}', 'updateExpense')->name('update');
            Route::delete('/{id}', 'deleteExpense')->name('destroy');
        });

        // ── Expense Categories ──────────────────────────────────────────────
        Route::prefix('expense-categories')->name('expense-categories.')->group(function () {
            Route::get('/', 'expenseCategories')->name('index');
            Route::post('/', 'storeExpenseCategory')->name('store');
            Route::put('/{id}', 'updateExpenseCategory')->name('update');  // Add this line
            Route::delete('/{id}', 'deleteExpenseCategory')->name('destroy');
        });

        // ── Payroll ─────────────────────────────────────────────────────────
        Route::prefix('payroll')->name('payroll.')->group(function () {
            Route::get('/', 'payroll')->name('index');
            Route::get('/create', 'createPayrollPeriod')->name('create');
            Route::post('/', 'storePayrollPeriod')->name('store');
            Route::get('/{id}', 'showPayrollPeriod')->name('show');
            Route::post('/{id}/approve', 'approvePayrollPeriod')->name('approve');
            Route::post('/{id}/mark-paid', 'markPayrollPaid')->name('mark-paid');
            Route::get('/payslip/{id}', 'viewPayslip')->name('payslip');
        });

        // ── Salary Structures ───────────────────────────────────────────────
        Route::get('/salary-structures', 'salaryStructures')->name('salary-structures');
        Route::post('/salary-structures', 'storeSalaryStructure')->name('salary-structures.store');

        // ── Budgets ─────────────────────────────────────────────────────────
        Route::prefix('budgets')->name('budgets.')->group(function () {
            Route::get('/', 'budgets')->name('index');
            Route::get('/create', 'createBudget')->name('create');        // ← specific first
            Route::post('/', 'storeBudget')->name('store');
            Route::get('/{id}/edit', 'editBudget')->name('edit');         // ← /{id}/x before /{id}
            Route::put('/{id}', 'updateBudget')->name('update');
            Route::post('/{id}/approve', 'approveBudget')->name('approve');
            Route::get('/{id}', 'showBudget')->name('show');              // ← wildcard last
        });
        // ── Reports ─────────────────────────────────────────────────────────
        Route::get('/reports', 'reports')->name('reports');
        Route::get('/outstanding-fees', 'outstandingFees')->name('outstanding-fees');
    });

// ─── FINANCE MODULE: LEDGERS ──────────────────────────────────────────────

Route::prefix('finance/ledger')
    ->name('finance.ledger.')
    ->controller(LedgerController::class)
    ->middleware(['module:finance'])
    ->middleware(['SchoolAuth'])
    ->group(function () {

        // ── Chart of Accounts ─────────────────────────────────────────────
        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/', 'chartOfAccounts')->name('index');
            Route::post('/', 'storeAccount')->name('store');
            Route::put('/{id}', 'updateAccount')->name('update');
            Route::delete('/{id}', 'destroyAccount')->name('destroy');
            Route::post('/seed-defaults', 'seedDefaultAccounts')->name('seed-defaults');
        });

        // ── General Ledger ──────────────────────────────────────────────────
        Route::get('/general', 'generalLedger')->name('general');

        // ── Student Fee Ledger ──────────────────────────────────────────────
        Route::get('/student-fees', 'studentFeeLedgerSearch')->name('student-fees');
        Route::get('/student-fees/{studentId}', 'studentFeeLedgerDetail')->name('student-fees.detail');

        // ── Trial Balance / Income & Expenditure ────────────────────────────
        Route::get('/trial-balance', 'trialBalance')->name('trial-balance');
    });



// ═══════════════════════════════════════════════════════════════════════════
// LIBRARY MODULE ROUTES
// ═══════════════════════════════════════════════════════════════════════════

Route::controller(LibraryController::class)
    ->prefix('library')
    ->name('library.')
    ->middleware(['AdminAuth'])
    ->group(function () {

        Route::get('/dashboard', 'dashboard')->name('dashboard');

        // Books
        Route::get('/books', 'books')->name('books');
        Route::get('/books/create', 'createBook')->name('books.create');
        Route::post('/books', 'storeBook')->name('books.store');
        Route::get('/books/{id}', 'showBook')->name('books.show');
        Route::get('/books/{id}/edit', 'editBook')->name('books.edit');
        Route::put('/books/{id}', 'updateBook')->name('books.update');
        Route::delete('/books/{id}', 'deleteBook')->name('books.destroy');
        Route::post('/books/import', 'importBooks')->name('books.import');
        Route::get('/books/export', 'exportBooks')->name('books.export');
        Route::get('/books/{id}/ebook', 'downloadEbook')->name('books.ebook');

        // Categories
        Route::get('/categories', 'categories')->name('categories');
        Route::post('/categories', 'storeCategory')->name('categories.store');
        Route::put('/categories/{id}', 'updateCategory')->name('categories.update');
        Route::delete('/categories/{id}', 'deleteCategory')->name('categories.destroy');

        // Authors
        Route::get('/authors', 'authors')->name('authors');
        Route::post('/authors', 'storeAuthor')->name('authors.store');
        Route::put('/authors/{id}', 'updateAuthor')->name('authors.update');
        Route::delete('/authors/{id}', 'deleteAuthor')->name('authors.destroy');

        // Subjects
        Route::get('/subjects', 'subjects')->name('subjects');
        Route::post('/subjects', 'storeSubject')->name('subjects.store');
        Route::put('/subjects/{id}', 'updateSubject')->name('subjects.update');
        Route::delete('/subjects/{id}', 'deleteSubject')->name('subjects.destroy');

        // Members
        Route::get('/members', 'members')->name('members');
        Route::post('/members', 'storeMember')->name('members.store');
        Route::put('/members/{id}', 'updateMember')->name('members.update');
        Route::delete('/members/{id}', 'deleteMember')->name('members.destroy');

        // Borrowings
        Route::get('/borrowings', 'borrowings')->name('borrowings');
        Route::post('/borrowings', 'borrowBook')->name('borrowings.store');
        Route::post('/borrowings/{id}/return', 'returnBook')->name('borrowings.return');
        Route::post('/borrowings/{id}/renew', 'renewBorrowing')->name('borrowings.renew');
        Route::post('/borrowings/{id}/lost', 'markLost')->name('borrowings.lost');

        // Reservations
        Route::get('/reservations', 'reservations')->name('reservations');
        Route::post('/reservations', 'storeReservation')->name('reservations.store');
        Route::put('/reservations/{id}/status', 'updateReservationStatus')->name('reservations.status');

        // Fines
        Route::get('/fines', 'fines')->name('fines');
        Route::post('/fines/{id}/pay', 'payFine')->name('fines.pay');
        Route::post('/fines/{id}/waive', 'waiveFine')->name('fines.waive');

        // Book Requests
        Route::get('/book-requests', 'bookRequests')->name('book-requests');
        Route::post('/book-requests', 'storeBookRequest')->name('book-requests.store');
        Route::put('/book-requests/{id}/review', 'reviewBookRequest')->name('book-requests.review');

        // Catalogue (Student/Teacher accessible)
        Route::get('/catalogue', 'catalogue')->name('catalogue');
        Route::get('/my-borrowings', 'myBorrowings')->name('my-borrowings');

        // Reports
        Route::get('/reports', 'reports')->name('reports');

        // Settings
        Route::get('/settings', 'settings')->name('settings');
        Route::post('/settings', 'updateSettings')->name('settings.update');
    });

// ═══════════════════════════════════════════════════════════════════════════
// CARD SCAN HUB ROUTES
// ═══════════════════════════════════════════════════════════════════════════

Route::controller(CardScanController::class)
    ->prefix('card-scan')
    ->name('card-scan.')
    ->middleware(['SchoolAuth'])
    ->group(function () {
        Route::get('/', 'hub')->name('hub');
        Route::post('/scan', 'scan')->name('scan');
        Route::get('/logs', 'scanLogs')->name('logs');
        Route::get('/arrival', 'arrivalAttendancePage')->name('arrival');
        Route::post('/arrival/save', 'saveArrivalAttendance')->name('arrival.save');
        Route::post('/arrival/save-bulk', 'saveBulkArrivalAttendance')->name('arrival.save.bulk');
        Route::get('/arrival/report', 'arrivalReport')->name('arrival.report');
    });

// ═══════════════════════════════════════════════════════════
//  USER RIGHTS & PRIVILEGES — NEW SYSTEM
// ═══════════════════════════════════════════════════════════

Route::group([
    'prefix' => 'user-rights',
    'middleware' => ['AdminAuth', 'localized'],
    'as' => 'urp.',
    // 'middleware' => 'module:user_rights',
], function () {

    // Dashboard
    Route::get('/dashboard', [UserRightsController::class, 'dashboard'])->name('dashboard');

    // Roles
    Route::get('/roles', [UserRightsController::class, 'rolesIndex'])->name('roles.index');
    Route::post('/roles', [UserRightsController::class, 'storeRole'])->name('roles.store');
    Route::get('/roles/{id}', [UserRightsController::class, 'getRole'])->name('roles.get');
    Route::put('/roles/{id}', [UserRightsController::class, 'updateRole'])->name('roles.update');
    Route::delete('/roles/{id}', [UserRightsController::class, 'deleteRole'])->name('roles.delete');

    // Permissions Matrix
    Route::get('/permissions', [UserRightsController::class, 'permissionsIndex'])->name('permissions.index');
    Route::post('/permissions/{roleId}/save', [UserRightsController::class, 'saveRolePermissions'])->name('permissions.save');
    Route::get('/permissions/{roleId}/get', [UserRightsController::class, 'getRolePermissions'])->name('permissions.get');
    Route::post('/permissions/toggle-module', [UserRightsController::class, 'toggleModuleAccess'])->name('permissions.toggle.module');
    Route::post('/permissions/toggle-feature', [UserRightsController::class, 'toggleFeatureAccess'])->name('permissions.toggle.feature');

    // Assign Roles to Teachers
    Route::get('/assign-roles', [UserRightsController::class, 'assignRolesIndex'])->name('assign.index');
    Route::post('/assign-roles/assign', [UserRightsController::class, 'assignRoleToTeacher'])->name('assign.store');
    Route::post('/assign-roles/remove', [UserRightsController::class, 'removeRoleFromTeacher'])->name('assign.remove');
});

// ═══════════════════════════════════════════════════════════
//  USER RIGHTS & PRIVILEGES — SYSTEM ADMIN: ALL SCHOOLS & ROLES
//
//  System-admin-only screens (LoggedAdmin, no LoggedSchool) that let
//  an admin view every school's roles/permissions and fix a teacher
//  lockout (wrong/missing role, role missing user_rights access)
//  without touching the database directly.
// ═══════════════════════════════════════════════════════════

Route::group([
    'prefix' => 'user-rights/admin-schools',
    'middleware' => ['AdminAuth', 'localized'],
    'as' => 'urp.admin.',
], function () {

    Route::get('/', [UserRightsController::class, 'adminSchoolsIndex'])->name('index');

    Route::put('/roles/{id}', [UserRightsController::class, 'adminUpdateRole'])->name('roles.update');
    Route::delete('/roles/{id}', [UserRightsController::class, 'adminDeleteRole'])->name('roles.delete');
    Route::get('/roles/{id}/permissions', [UserRightsController::class, 'adminGetRolePermissions'])->name('roles.permissions.get');
    Route::post('/roles/{id}/permissions', [UserRightsController::class, 'adminSaveRolePermissions'])->name('roles.permissions.save');

    Route::post('/teachers/assign-role', [UserRightsController::class, 'adminAssignTeacherRole'])->name('teachers.assign-role');

    // Wildcard school routes LAST
    Route::get('/{school}', [UserRightsController::class, 'adminSchoolRoles'])->name('school.roles');
    Route::post('/{school}/roles', [UserRightsController::class, 'adminStoreRole'])->name('roles.store');
    Route::post('/{school}/teachers', [UserRightsController::class, 'adminCreateTeacher'])->name('teachers.store');
});


// ============================================================
// ADD THESE ROUTES TO YOUR routes/web.php
// Inside your existing AdminAuth middleware group
// ============================================================

Route::middleware(['AdminAuth'])->group(function () {

    // ── Push subscription + AJAX endpoints ──────────────────────────
    // These must be reachable by ALL logged-in users (admin or teacher),
    // regardless of whether their role has the 'notifications' module
    // enabled. Putting them inside module:notifications would 403 anyone
    // without that module, leaving push_subscriptions forever empty.
    Route::prefix('notifications')
        ->name('notifications.')
        ->controller(NotificationController::class)
        ->group(function () {
            Route::post('/push/subscribe', 'savePushSubscription')->name('push.subscribe');
            Route::post('/push/unsubscribe', 'deletePushSubscription')->name('push.unsubscribe');
            Route::get('/ajax/unread-count', 'unreadCount')->name('ajax.unread');
            Route::get('/ajax/dropdown', 'dropdown')->name('ajax.dropdown');
            Route::post('/read-all', 'markAllRead')->name('read-all');
            Route::post('/{id}/read', 'markRead')->name('mark-read');
        });

    // ── Module-gated admin routes ────────────────────────────────────
    Route::prefix('notifications')
        ->name('notifications.')
        ->controller(NotificationController::class)
        ->middleware(['module:notifications'])
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/my', 'myNotifications')->name('my');
            Route::get('/{id}', 'show')->name('show');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

});

// ── Temporary push diagnostics — REMOVE after confirming push works ──────────
Route::middleware(['AdminAuth'])->get('/push-diag', function () {
    $adminId   = session('LoggedAdmin');
    $teacherId = session('LoggedTeacher');
    $schoolId  = session('LoggedSchool');

    $subscriber = null;
    $subscriberType = null;
    if ($adminId) {
        $subscriber = \App\Models\User::find($adminId);
        $subscriberType = 'User (admin)';
    } elseif ($teacherId) {
        $subscriber = \App\Models\Teacher::find($teacherId);
        $subscriberType = 'Teacher';
    }

    $hasTrait = $subscriber
        ? in_array(\NotificationChannels\WebPush\HasPushSubscriptions::class, class_uses_recursive($subscriber))
        : false;

    $subCount = ($subscriber && $hasTrait)
        ? $subscriber->pushSubscriptions()->count()
        : 0;

    $allSubs = \Illuminate\Support\Facades\DB::table('push_subscriptions')->get();

    return response()->json([
        'session'               => [
            'LoggedAdmin'   => $adminId,
            'LoggedTeacher' => $teacherId,
            'LoggedSchool'  => $schoolId,
        ],
        'subscriber_type'       => $subscriberType,
        'subscriber_id'         => $subscriber?->id ?? null,
        'subscriber_found'      => !is_null($subscriber),
        'has_push_trait'        => $hasTrait,
        'this_user_subs'        => $subCount,
        'all_push_subs_total'   => $allSubs->count(),
        'all_push_subs'         => $allSubs,
        'vapid_public_key_set'  => !empty(config('webpush.vapid.public_key')),
        'vapid_private_key_set' => !empty(config('webpush.vapid.private_key')),
        'vapid_subject'         => config('webpush.vapid.subject'),
    ], 200, [], JSON_PRETTY_PRINT);
})->name('push.diag');

Route::middleware(['AdminAuth'])->get('/push-test', function () {
    return view('push_test');
})->name('push.test');

// ── Temporary push send test — REMOVE after confirming push works ───────────
Route::middleware(['AdminAuth'])->post('/push-send-test', function (\Illuminate\Http\Request $request) {
    $adminId   = session('LoggedAdmin');
    $teacherId = session('LoggedTeacher');

    $subscriber = null;
    if ($adminId) {
        $subscriber = \App\Models\User::find($adminId);
    } elseif ($teacherId) {
        $subscriber = \App\Models\Teacher::find($teacherId);
    }

    if (!$subscriber) {
        return response()->json([
            'success' => false,
            'message' => 'No logged-in admin/teacher found in session.',
        ], 422);
    }

    $subCount = $subscriber->pushSubscriptions()->count();
    if ($subCount === 0) {
        return response()->json([
            'success' => false,
            'message' => 'This user has no push subscriptions yet. Click "Request Permission & Subscribe" first.',
        ], 422);
    }

    try {
        $subscriber->notify(new \App\Notifications\SmasaPushNotification(
            'SMASA Test Notification',
            'If you can see this, push delivery is fully working! 🎉',
            url('/dashboard')
        ));
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Notify call threw an exception: ' . $e->getMessage(),
        ], 500);
    }

    return response()->json([
        'success' => true,
        'message' => 'Test push dispatched to ' . $subCount . ' subscription(s). Check your device.',
    ]);
})->name('push.send-test');