<?php

use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);
Route::post('register', [\App\Http\Controllers\Auth\AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('user', [\App\Http\Controllers\Auth\AuthController::class, 'user']);
    Route::post('logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout']);
    Route::get('sidebar', [\App\Http\Controllers\Auth\AuthController::class, 'sidebar']);
    Route::get('tiempo-uso', [\App\Http\Controllers\Auth\AuthController::class, 'tiempoTotalUso']);

    Route::get('dashboard', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard']);
    Route::get('courses', [\App\Http\Controllers\Admin\AdminController::class, 'index_courses']);
    Route::post('courses', [\App\Http\Controllers\Admin\AdminController::class, 'create_course']);
    Route::post('course/students', [\App\Http\Controllers\Admin\AdminController::class, 'add_students_to_course']);
    Route::delete('courses/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'delete_course']);
    Route::get('teachers', [\App\Http\Controllers\Admin\AdminController::class, 'index_teachers']);
    Route::post('teachers', [\App\Http\Controllers\Admin\AdminController::class, 'create_teacher']);
    Route::put('teachers', [\App\Http\Controllers\Admin\AdminController::class, 'update_teacher']);
    Route::delete('teachers/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'delete_teacher']);
    Route::get('reports/rewards/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'rewards_by_courses']);
    Route::get('reports/courses/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'courses']);
    Route::get('reports/students/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'students_by_course']);
    Route::post('teacher/{id}/add-student', [\App\Http\Controllers\Admin\AdminController::class, 'add_student_by_teacher']);

    Route::get('teacher/{id}/students', [\App\Http\Controllers\Teacher\TeacherController::class, 'index_students']);
    Route::get('library', [\App\Http\Controllers\Teacher\TeacherController::class, 'index_library']);
    Route::get('themes', [\App\Http\Controllers\Teacher\TeacherController::class, 'index_themes']);
    Route::post('themes', [\App\Http\Controllers\Teacher\TeacherController::class, 'create_theme']);
    Route::post('students', [\App\Http\Controllers\Teacher\TeacherController::class, 'create_student']);
    Route::put('students/{id}', [\App\Http\Controllers\Teacher\TeacherController::class, 'update_student']);
    Route::delete('students/{id}', [\App\Http\Controllers\Teacher\TeacherController::class, 'delete_student']);
    Route::get('teacher/{id}/course', [\App\Http\Controllers\Teacher\TeacherController::class, 'get_course']);
    Route::get('contents/{id}', [\App\Http\Controllers\Teacher\TeacherController::class, 'get_contents_by_theme']);
    Route::post('contents', [\App\Http\Controllers\Teacher\TeacherController::class, 'create_content']);
    Route::delete('contents/{id}', [\App\Http\Controllers\Teacher\TeacherController::class, 'delete_content']);
    Route::post('teacher/asignar_recompensa/{id}', [\App\Http\Controllers\Teacher\TeacherController::class, 'asignar_recompensa']);
    Route::get('teacher/activities', [\App\Http\Controllers\Teacher\TeacherController::class, 'index_activities']);
    Route::post('teacher/activities', [\App\Http\Controllers\Teacher\TeacherController::class, 'create_activity']);
    Route::delete('teacher/themes/{id}', [\App\Http\Controllers\Teacher\TeacherController::class, 'delete_theme']);

    Route::get('student', [\App\Http\Controllers\Student\StudentController::class, 'index_student']);
    Route::get('student/contents/{id}', [\App\Http\Controllers\Student\StudentController::class, 'get_contents_by_theme']);
    Route::get('student/teacher', [\App\Http\Controllers\Student\StudentController::class, 'get_teacher']);
    Route::get('student/themes', [\App\Http\Controllers\Student\StudentController::class, 'index_themes']);
    Route::get('student/themes/{theme_id}/images', [\App\Http\Controllers\Student\StudentController::class, 'get_images_by_theme']);
    Route::get('student/themes/{theme_id}/videos', [\App\Http\Controllers\Student\StudentController::class, 'get_videos_by_theme']);
    Route::get('student/themes/{theme_id}/texts', [\App\Http\Controllers\Student\StudentController::class, 'get_texts_by_theme']);
    Route::get('student/content/view/{id}', [\App\Http\Controllers\Student\StudentController::class, 'view_content']);
    Route::post('student/get/reward/{id}', [\App\Http\Controllers\Student\StudentController::class, 'get_reward']);

    Route::get('activities/wordsearch/{id}', [\App\Http\Controllers\Student\StudentController::class, 'get_wordsearch']);
    Route::get('activities/crossword/{id}', [\App\Http\Controllers\Student\StudentController::class, 'get_crossword']);
    Route::get('activities/memory/{id}', [\App\Http\Controllers\Student\StudentController::class, 'get_memory']);
});
