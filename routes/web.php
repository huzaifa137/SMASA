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
use App\Http\Controllers\Helper;
use App\Http\Controllers\UserRightsAndPreviledges;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::get('/show-sessions', function () {
    $allSessions = Session::all();
    dd($allSessions);
})->name('show.sessions');

Route::get('/demo', function () {
    dd(Helper::activeTerm());
});

Route::get('/set-admin-session', function () {
    // session(['LoggedAdminMaster' => 1]);
    session(['LoggedAdmin' => 1]);

    return redirect('/');
});

Route::get('/set-student-session', function () {
    session(['LoggedStudent' => 1]);

    return redirect('/');
});

Route::get('/set-school', function () {
    session(['LoggedSchool' => 2]);

    return redirect('/');
});

Route::get('/logout', function () {
    session()->flush();

    return redirect('/');
})->name('logout');

Route::get('/coming-soon', function () {
    return view('coming-soon');
})->name('coming.soon');

// Route::group(['middleware' => ['AdminAuth']], function () {
//     Route::get('/', 'dashboardMasterDataDashboard')->name('dashboard.masterdata');
// });

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
                Route::get('/forgot-password', 'forgotPassword')->name('forgot-password');
                Route::get('/login', 'login')->name('users.login');
                Route::post('auth-user-check', 'checkUser')->name('auth-user-check');
                Route::get('/users-profile', 'userProfile')->name('users-profile');
                Route::get('/users-register', 'userRegister');
                Route::get('/users-information', 'userInformation')->name('users.user-information');
                Route::get('user-account-information/{id}', [UserController::class, 'userAccountInformation'])->name('users.account-information');
                Route::get('/home-page', 'homePage')->name('home.page');
                Route::get('/public-portal', 'publicPortal')->name('public.portal');
                Route::get('/register', 'register')->name('users.register');
                Route::get('/edit-user-information', 'editUserInformation');
                Route::get('/edit-specific-user/{userid}', 'editSpecificUser')->name('users.edit-specific-user');
                Route::get('/terms-and-conditions', 'user_terms_and_conditions')->name('users.terms-and-conditions');
                Route::get('/users/delete-user/{id}', 'deleteUserAccount')->name('users.delete-user');
            });

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

    Route::controller(MasterDataController::class)->group(function () {
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

                    if (! $userId || ! $userEmail) {
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

        Route::get('/select-current-school', 'selectCurrentSchool')->name('select.current.school');
    });

    Route::controller(SchoolController::class)->group(function () {
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

    Route::controller(TeacherController::class)->group(function () {
        Route::get('add-teachers', 'addTeachers')->name('school.add-teachers');
        Route::get('/teachers', 'allTeachers')->name('teachers.all');
        Route::get('/school-teachers', 'schoolTeachers')->name('school.teachers');
        Route::get('/individual-school-teachers/{id}', 'individualSchoolTeachers')->name('individual.school.teachers');
        Route::get('/teacher-profile/{id}', 'teacherProfile')->name('teacher.profile');
        Route::get('/update-teacher-profile/{id}', 'updateteacherProfile')->name('update.teacher.profile');
        Route::get('/teacher/profile/{id}/data', 'getTeacherData')->name('teacher.data');
        Route::post('/store-teachers', 'storeTeacher')->name('teachers.store');
        Route::post('/teachers/update/{teacher}', 'storeUpdatedTeacherProfile')->name('teachers.update');

        // Add this route for updating teacher password
        Route::post('/teacher/update-password', 'updatePassword')->name('teacher.update-password');

        Route::delete('/teachers/{id}', 'destroyTeacher')->name('teachers.destroy');
    });

    Route::controller(ClassandSubjectController::class)->group(function () {

        Route::get('create-class', 'createClass')->name('school.create-class');
        Route::get('all-my-classes', 'allMyClasses')->name('all.my-classes');
        Route::get('manage-classes', 'manageClasses')->name('manage.classes');
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

    Route::controller(UserRightsAndPreviledges::class)->group(function () {
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

                    // Route::put('/update/{id}', 'updateStudentInformation');
                    Route::post('/students/store', 'storeStudent')->name('students.store');

                    Route::get('/transfer-form', 'moveStudentForm')->name('students.transfer');

                    Route::get('/streams/by-class', 'getStreamsByClass')->name('streams.by.class');
                    Route::get('/students/search', 'searchStudentsByClassStream')->name('students.search');
                    Route::post('/students/move', 'moveStudent')->name('students.move');

                    Route::get('students/generate-id', 'generateStudentID')->name('students.generate-id');
                    Route::get('/view/{id}', 'viewStudent')->name('students.view');
                    Route::post('/update/{id}', 'updateStudent');

                    Route::delete('/delete/{student}', 'destroyStudent')->name('students.destroy');

                });
            });
    });

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

}); // end localized routes group
