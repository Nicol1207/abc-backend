<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Contenido;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TeacherController extends Controller
{
    //
    public function index_students(Request $request)
    {
        $user = Auth::user();
        $course = Course::where('teacher_id', $user->id)
            ->where('status_id', 1) // Assuming 1 means active
            ->first();

        if (!$course) {
            return response()->json([
                'message' => 'No hay cursos activos para este docente.',
                'data' => []
            ], 404);
        }

        $students = CourseStudent::where('course_id', $course->id)
            ->with('user') // Assuming CourseStudent has a 'student' relationship
            ->get()
            ->map(function ($courseStudent) {
                return [
                    'id' => $courseStudent->student_id,
                    'name' => $courseStudent->user->name,
                    'email' => $courseStudent->user->email,
                    'status_id' => $courseStudent->user->status_id,
                    'connection_time' => $courseStudent->user->getTiempoTotalUsoLegible(),
                ];
            });

        return response()->json([
            'message' => 'Lista de estudiantes del curso activo',
            'data' => $students,
        ]);
    }

    public function index_library(Request $request)
    {
        $user = Auth::user();

        $course = Course::where('teacher_id', $user->id)
            ->where('status_id', 1) // Assuming 1 means active
            ->first();

        $themes = Tema::where('course_id', $course->id)->where('status_id', 1)->get();
        $images = Contenido::where('id_tipocontenido_fk', 1)->get();
        $videos = Contenido::where('id_tipocontenido_fk', 2)->get();
        $textos = Contenido::where('id_tipocontenido_fk', 3)->get();

        return response()->json([
            'message' => 'List of library contents for the teacher',
            'data' => [
                'themes' => $themes,
                'images' => $images,
                'videos' => $videos,
                'textos' => $textos
            ]
        ]);
    }

    public function index_themes(Request $request)
    {
        $themes = Tema::where('status_id', 1)->get();

        return response()->json([
            'message' => 'List of themes for the teacher',
            'data' => $themes
        ]);
    }

    public function create_theme(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string',
            'numero' => 'required|integer',
            'color' => 'required|string',
            'descripcion' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Verifique los datos solicitados',
            ]);
        }

        $teacher = Auth::user();

        $course = Course::where('teacher_id', $teacher->id)->where('status_id', 1)->first();

        if (!$course) {
            return response()->json([
                'message' => 'No existe el curso',
            ]);
        }

        try {
            DB::beginTransaction();
            $theme = Tema::create([
                'titulo' => $request->input('titulo'),
                'numero' => $request->input('numero'),
                'color' => $request->input('color'),
                'descripcion' => $request->input('descripcion'),
                'course_id' => $course->id,
                'status_id' => 1, // Assuming 1 means active
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating theme',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Theme created successfully',
            'data' => $theme
        ], 201);
    }

    public function create_student(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'document_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Verifica los valores solicitados',
                'error' => $validator->errors()->getMessages(),
            ], 400);
        }

        $teacher = Auth::user();

        $course = Course::where('status_id', 1)->where('teacher_id', $teacher->id)->first();

        if (!$course) {
            return response()->json([
                'message' => 'Seccion no encontrada',
            ]);
        }

        $student = User::where('email', $request->input('document_number'))->first();

        if ($student) {
            $courseStudent = CourseStudent::where('student_id', $student->id)->where('course_id', $course->id)->first();

            if ($courseStudent) {
                return response()->json([
                    'message' => 'Ya existe ese estiante en el curso'
                ], 400);
            }

            $student->status_id = 1;
            $student->name = $request->input('first_name') . ' ' . $request->input('last_name');
            $student->save();
        } else {
            $student = User::create([
                'name' => $request->input('first_name') . ' ' . $request->input('last_name'),
                'email' => $request->input('document_number'),
                'role_id' => 3, // Assuming 3 is the role for students
                'password' => Hash::make($request->input('document_number')), // Default password is the document
            ]);
        }

        $teacher = Auth::user();

        $course = Course::where('teacher_id', $teacher->id)->where('status_id', 1)->first();

        if (!$course) {
            return response()->json([
                'message' => 'No hay una seccion activa para este profesor',
            ], 404);
        }

        $courseStudent = CourseStudent::create([
            'course_id' => $course->id,
            'student_id' => $student->id,
        ]);

        return response()->json([
            'message' => 'Student created successfully',
            'data' => $student,
        ], 201);
    }

    public function update_student(Request $request, int $id)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'document_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Verifica los valores solicitados',
                'error' => $validator->errors()->getMessages(),
            ], 400);
        }

        $student = User::find($id);

        if (!$student) {
            return response()->json([
                'message' => 'Student not found',
                'data' => null
            ], 404);
        }

        $student->name = $request->input('name');
        $student->email = $request->input('document_number');
        $student->password = Hash::make($request->input('document_number'));
        $student->save();

        return response()->json([
            'message' => 'Student updated successfully',
            'data' => $student,
        ]);
    }

    public function delete_student(Request $request, int $id)
    {
        $student = User::find($id);

        if (!$student) {
            return response()->json([
                'messaege' => 'No existe el estudiante',
            ], 404);
        }

        $teacher = Auth::user();

        $course = Course::where('teacher_id', $teacher->id)->where('status_id', 1)->first();

        if (!$course) {
            return response()->json([
                'messaege' => 'No existe un curso activo para este profesor',
            ], 404);
        }

        $courseStudent = CourseStudent::where('course_id', $course->id)->where('student_id', $student->id)->first();

        $courseStudent->delete();

        $student->status_id = 2;
        $student->save();

        return response()->json([
            'message' => 'Estudiante eliminado con exito',
        ]);
    }

    public function get_course(Request $request, $teacher_id)
    {
        $course = Course::where('teacher_id', $teacher_id)->where('status_id', 1)->first();

        if (!$course) {
            return response()->json([
                'message' => 'No active course found for this teacher.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'Active course found for the teacher',
            'data' => $course
        ]);
    }

    public function create_teacher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string',
            // Otros campos si es necesario
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Verifica los valores solicitados',
                'error' => $validator->errors()->getMessages(),
            ], 400);
        }

        // Verificar si ya existe un usuario con ese email
        $teacher = User::where('email', $request->input('email'))->first();

        if ($teacher) {
            // Si existe, actualiza los datos y cambia status_id a 1 (activo)
            $teacher->name = $request->input('name');
            $teacher->password = Hash::make($request->input('password'));
            $teacher->status_id = 1;
            // Otros campos si es necesario
            $teacher->save();
        } else {
            // Si no existe, crea el usuario profesor
            $teacher = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'role_id' => 2, // Asumiendo que 2 es el rol de profesor
                'status_id' => 1,
                // Otros campos si es necesario
            ]);
        }

        return response()->json([
            'message' => 'Teacher created or updated successfully',
            'data' => $teacher,
        ], 201);
    }

    public function get_contents_by_theme(Request $request, int $themeId)
    {
        // 1. Get the authenticated teacher
        $teacher = Auth::user();

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

    public function create_content(Request $request)
    {
        $tipo = $request->input('id_tipocontenido_fk');
        $mimeRules = [
            1 => 'jpeg,png,jpg,gif', // imágenes
            2 => 'mp4,avi,mov',      // videos
            3 => 'txt',              // textos
            4 => 'doc,docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
        ];

        $subFolder = '';
        if ($tipo == 1) {
            $subFolder = 'images';
        } elseif ($tipo == 2) {
            $subFolder = 'videos';
        } elseif ($tipo == 3) {
            $subFolder = 'texts';
        } elseif ($tipo == 4) {
            $subFolder = 'docs';
        } else {
            return response()->json([
                'message' => "Tipo de contenido no soportado",
            ], 400);
        }

        $mime = isset($mimeRules[$tipo]) ? $mimeRules[$tipo] : '';
        $validator = Validator::make($request->all(), [
            'id_tema_fk' => 'required|string',
            'id_tipocontenido_fk' => 'required|integer',
            'contenido' => 'required|string',
            'file' => 'nullable|file|max:524288' . ($mime ? '|mimes:' . $mime : ''),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => "Verifique los datos solicitados o suba un archivo más pequeño o con formato permitido",
                'errors' => $validator->errors(),
            ], 400);
        }

        if ($request->hasFile('file')) {
            $originalName = $request->file('file')->getClientOriginalName();
            $sanitizedName = str_replace(' ', '_', $originalName);
            $sanitizedName = preg_replace('/[^A-Za-z0-9.\-_]/', '', $sanitizedName);
            $filename = time() . '_' . $sanitizedName;
            $path = $request->file('file')->storeAs('contents/' . $subFolder, $filename, 'public');
        }

        $contenido = Contenido::create([
            'titulo' => $request->input('contenido'),
            'url' => $path,
            'id_tipocontenido_fk' => $tipo,
            'id_tema_fk' => $request->input('id_tema_fk'),
            'status_id' => 1
        ]);

        return response()->json([
            'message' => "Creado correctamente",
        ]);
    }

    public function delete_content(Request $request, int $id)
    {
        $content = Contenido::find($id);

        if (!$content) {
            return response()->json([
                'message' => 'Contenido no encontrado',
            ], 404);
        }

        // Eliminar el archivo del sistema de archivos si existe
        if ($content->url && Storage::disk('public')->exists($content->url)) {
            Storage::disk('public')->delete($content->url);
        }

        $content->delete();

        return response()->json([
            'message' => 'Contenido eliminado correctamente',
        ]);
    }
}
