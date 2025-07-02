<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Contenido;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    //
    public function index_student(Request $request)
    {
        $user = Auth::user();
        $course_student = CourseStudent::where('student_id', $user->id)->first();
        $course = Course::find($course_student->course_id);
        $teacher = User::find($course->teacher_id);

        return response()->json([
            'message' => 'Student information retrieved successfully',
            'data' => [
                'user' => $user,
                'course' => $course,
                'teacher' => $teacher
            ]
        ]);
    }

    public function index_themes(Request $request)
    {
        $student = Auth::user();

        $courseStudent = CourseStudent::where('student_id', $student->id)->first();

        $themes = Tema::where('course_id', $courseStudent->course_id)->where('status_id', 1)->get();

        return response()->json([
            'themes' => $themes
        ]);
    }

    public function get_images_by_theme(Request $request, $theme_id)
    {
        $images = Contenido::where('id_tipocontenido_fk', 1)
            ->where('tema_id', $theme_id)
            ->where('status_id', 1)
            ->get();

        return response()->json([
            'message' => 'List of images for the theme',
            'data' => $images
        ]);
    }

    public function get_videos_by_theme(Request $request, $theme_id)
    {
        $videos = Contenido::where('id_tipocontenido_fk', 2)
            ->where('tema_id', $theme_id)
            ->where('status_id', 1)
            ->get();

        return response()->json([
            'message' => 'List of videos for the theme',
            'data' => $videos
        ]);
    }

    public function get_texts_by_theme(Request $request, $theme_id)
    {
        $texts = Contenido::where('id_tipocontenido_fk', 3)
            ->where('tema_id', $theme_id)
            ->where('status_id', 1)
            ->get();

        return response()->json([
            'message' => 'List of texts for the theme',
            'data' => $texts
        ]);
    }
}
