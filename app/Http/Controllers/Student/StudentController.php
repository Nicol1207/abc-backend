<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Contenido;
use App\Models\ContenidoUsuario;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\RecompensaEstudiante;
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
        $recompensas = RecompensaEstudiante::where('id_usuario_fk', $user->id)->sum('cantidad');

        return response()->json([
            'message' => 'Student information retrieved successfully',
            'data' => [
                'user' => $user,
                'course' => $course,
                'teacher' => $teacher,
                'recompensas' => $recompensas
            ]
        ]);
    }

    public function get_teacher(Request $request)
    {
        $student = Auth::user();
        $courseStudent = CourseStudent::where('student_id', $student->id)->first();

        if (!$courseStudent) {
            return response()->json(['message' => 'Course not found for this student'], 404);
        }

        $course = Course::find($courseStudent->course_id);
        $teacher = User::find($course->teacher_id);

        return response()->json([
            'message' => 'Teacher information retrieved successfully',
            'data' => $teacher
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

    public function get_contents_by_theme(Request $request, int $themeId)
    {

        $student = Auth::user();

        $courseStudent = CourseStudent::where('student_id', $student->id)->first();

        $course = Course::find($courseStudent->course_id);

        // 1. Get the authenticated teacher
        $teacher = User::find($course->teacher_id);

        // if (!$teacher || $teacher->role_id !== 2) { // Assuming role_id 2 is for teachers
        //     return response()->json([
        //         'message' => 'Acceso denegado. Solo los profesores pueden acceder a este recurso.',
        //     ], 403);
        // }

        // 2. Find the theme
        $theme = Tema::where('id_temas', $themeId)
            ->where('status_id', 1) // Ensure theme is active
            ->first();

        if (!$theme) {
            return response()->json([
                'message' => 'El tema especificado no existe o no está activo.',
            ], 404);
        }

        // 3. Verify if the theme belongs to a course taught by the authenticated teacher
        $isTeacherOfCourse = Course::where('id', $theme->course_id)
            ->where('teacher_id', $teacher->id)
            ->exists();

        // if (!$isTeacherOfCourse) {
        //     return response()->json([
        //         'message' => 'No tiene permiso para ver los contenidos de este tema.',
        //     ], 403);
        // }

        try {
            // Retrieve all contents for the given theme, using the relationship
            $contents = $theme->contenidos()
                ->where('status_id', 1) // Ensure content is active
                ->get();

            // Categorize contents as expected by temas.tsx
            $images = [];
            $videos = [];
            $documents = []; // Placeholder for documents or other types

            foreach ($contents as $content) {
                // Assuming id_tipocontenido_fk: 1 for images, 2 for videos, 3 for documents
                if ($content->id_tipocontenido_fk == 1) {
                    $images[] = $content;
                } elseif ($content->id_tipocontenido_fk == 2) {
                    $videos[] = $content;
                } elseif ($content->id_tipocontenido_fk == 3) {
                    $documents[] = $content;
                }
                // Add more conditions for other content types if needed
            }

            return response()->json([
                'message' => 'Contenidos del tema obtenidos correctamente.',
                'data' => [
                    'images' => $images,
                    'videos' => $videos,
                    'documents' => $documents,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los contenidos del tema.',
                'error' => $e->getMessage(),
            ], 500);
        }
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

    public function view_content(Request $request, $id)
    {
        $student = Auth::user();

        $content = Contenido::find($id);

        if (!$content) {
            return response()->json([
                'message' => 'Content not found.',
            ], 404);
        }

        $contenidoUsuario = ContenidoUsuario::where('id_usuario_fk', $student->id)->where('id_contenido_fk', $content->id_contenido)->first();

        if (!$contenidoUsuario) {
            // If the content has not been viewed by the student, create a new record
            $contenidoUsuario = new ContenidoUsuario();
            $contenidoUsuario->id_usuario_fk = $student->id;
            $contenidoUsuario->id_contenido_fk = $content->id_contenido;
            $contenidoUsuario->created_at = now();
            $contenidoUsuario->updated_at = now();
            $contenidoUsuario->save();

            $recompensaEstudiante = new RecompensaEstudiante();
            $recompensaEstudiante->id_usuario_fk = $student->id;
            $recompensaEstudiante->id_recompensa_fk = 1;
            $recompensaEstudiante->cantidad = 1; // Assuming a fixed reward of 1 point for viewing content
            $recompensaEstudiante->save();
        } else {
            // If it has been viewed, you might want to update the timestamp or other fields
            // For example, updating the last viewed timestamp
            $contenidoUsuario->updated_at = now();
            $contenidoUsuario->save();
        }

        // Assuming you want to return the content details
        return response()->json([
            'message' => 'Content retrieved successfully.',
            'data' => $content
        ]);
    }
}
