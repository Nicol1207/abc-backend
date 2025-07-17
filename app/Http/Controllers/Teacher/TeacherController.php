<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Contenido;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Crossword;
use App\Models\CrosswordsWord;
use App\Models\MemoriesWord;
use App\Models\Memory;
use App\Models\RecompensaEstudiante;
use App\Models\Tema;
use App\Models\User;
use App\Models\Wordsearch;
use App\Models\WordsearchWord;
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
                    'rewards' => RecompensaEstudiante::where('id_usuario_fk', $courseStudent->student_id)
                        ->sum('cantidad'), // Assuming 'cantidad' is the field for rewards
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
                'status_id' => 1, // Assuming 1 means active
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
                // Concatenar host si es necesario
                $url = $content->url;
                if ($url && !preg_match('/^https?:\/\//i', $url)) {
                    $url = 'http://localhost:3000/storage/' . ltrim($url, '/');
                }
                $item = $content->toArray();
                $item['url'] = $url;
                if ($content->id_tipocontenido_fk == 1) {
                    $images[] = $item;
                } elseif ($content->id_tipocontenido_fk == 2) {
                    $videos[] = $item;
                } elseif ($content->id_tipocontenido_fk == 3) {
                    $documents[] = $item;
                }
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
        $tipoCarga = $request->input('tipoCarga');
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
        $rules = [
            'id_tema_fk' => 'required|string',
            'id_tipocontenido_fk' => 'required|integer',
            'contenido' => 'required|string',
        ];
        if ($tipoCarga === 'archivo') {
            $rules['file'] = 'required|file|max:524288' . ($mime ? '|mimes:' . $mime : '');
        } elseif ($tipoCarga === 'link') {
            $rules['link'] = 'required|url';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => "Verifique los datos solicitados o suba un archivo más pequeño o con formato permitido",
                'errors' => $validator->errors(),
            ], 400);
        }

        $path = null;
        if ($tipoCarga === 'archivo' && $request->hasFile('file')) {
            $originalName = $request->file('file')->getClientOriginalName();
            $sanitizedName = str_replace(' ', '_', $originalName);
            $sanitizedName = preg_replace('/[^A-Za-z0-9.\-_]/', '', $sanitizedName);
            $filename = time() . '_' . $sanitizedName;
            $path = $request->file('file')->storeAs('contents/' . $subFolder, $filename, 'public');
        } elseif ($tipoCarga === 'link') {
            $path = $request->input('link');
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

    public function asignar_recompensa(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer',
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
                'message' => 'Estudiante no encontrado',
            ], 404);
        }

        $recompensaEstudiante = new RecompensaEstudiante();
        $recompensaEstudiante->id_usuario_fk = $student->id;
        $recompensaEstudiante->id_recompensa_fk = 1; // Assuming 1 is the ID for the reward type
        $recompensaEstudiante->cantidad = $request->input('cantidad');
        $recompensaEstudiante->updated_at = now();
        $recompensaEstudiante->save();

        return response()->json([
            'message' => 'Recompensa asignada correctamente',
            'data' => $student,
        ]);
    }

    public function index_activities(Request $request)
    {
        $teacher = Auth::user();
        $teacher = User::where('id', $teacher->id)->first();

        if (!$teacher) {
            return response()->json([
                'message' => 'Profesor no encontrado',
            ], 404);
        }

        $activities = Activity::where('teacher_id', $teacher->id)->get();

        if ($activities->isEmpty()) {
            return response()->json([
                'message' => 'No hay actividades registradas para este profesor.',
                'data' => []
            ], 200);
        }

        // Mapear actividades con información relevante y tipo
        $mapped = $activities->map(function ($activity) {
            // Obtener el tipo de actividad
            $type = null;
            if ($activity->activity_type) {
                $type = $activity->activity_type->description
                    ?? null;
            } else if ($activity->activity_type_i) {
                // Si no hay relación, usar el id
                switch ($activity->activity_type_i) {
                    case 1:
                        $type = 'Sopa de letras';
                        break;
                    case 2:
                        $type = 'Crucigrama';
                        break;
                    case 3:
                        $type = 'Memoria';
                        break;
                    default:
                        $type = 'Desconocido';
                }
            }
            return [
                'id' => $activity->id,
                'titulo' => $activity->title,
                'descripcion' => $activity->description,
                'tipo' => $type,
            ];
        });

        return response()->json([
            'message' => 'Actividades obtenidas correctamente.',
            'data' => $mapped,
        ], 200);
    }

    public function create_activity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activityType' => 'required|integer|in:1,2,3', // 1: Wordsearch, 2: Crossword, 3: Memory
            'title' => 'required|string',
            'description' => 'required|string',
            'points' => 'required|integer',
            'words' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Verifica los valores solicitados',
                'data' => $request->all(),
                'error' => $validator->errors()->getMessages(),
            ], 400);
        }

        $teacher = Auth::user();

        try {
            DB::beginTransaction();
            $activity = Activity::create([
                'activity_type_id' => $request->input('activityType'),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'points' => $request->input('points'),
                'teacher_id' => $teacher->id,
            ]);

            if ($request->input('activityType') === 1) {
                // Sopa de letras
                $wordsearch = Wordsearch::create([
                    'activity_id' => $activity->id,
                ]);
                foreach ($request->input('words') as $word) {
                    $wordSearchWords = WordsearchWord::create([
                        'wordsearch_id' => $wordsearch->id,
                        'english_word' => $word['english'],
                        'spanish_word' => $word['spanish'],
                        'emoji' => $word['emoji'],
                    ]);
                }
            } elseif ($request->input('activityType') === 2) {
                // Crucigrama
                $crossword = Crossword::create([
                    'activity_id' => $activity->id,
                ]);
                foreach ($request->input('words') as $word) {
                    $wordCrosswords = CrosswordsWord::create([
                        'crossword_id' => $crossword->id,
                        'english_word' => $word['english'],
                        'spanish_word' => $word['spanish'],
                        'emoji' => $word['emoji'],
                    ]);
                }
            } elseif ($request->input('activityType') === 3) {
                // Memoria
                $memory = Memory::create([
                    'activity_id' => $activity->id,
                ]);
                foreach ($request->input('words') as $word) {
                    $memoryWords = MemoriesWord::create([
                        'memory_id' => $memory->id,
                        'english_word' => $word['english'],
                        'spanish_word' => $word['spanish'],
                        'emoji' => $word['emoji'],
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'Tipo de actividad no soportado',
                ], 400);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al iniciar la transacción',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => "Actividad creada correctamente",
            'data' => $activity,
        ]);
    }
}
