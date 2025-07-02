<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    //
    public function index_courses(Request $request)
    {
        $courses = Course::where('status_id', 1)->get();

        return response()->json([
            'data' => $courses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'teacher' => User::find($course->teacher_id),
                    'section' => $course->section,
                    'status' => $course->status->description,
                    'students' => CourseStudent::where('course_id', $course->id)
                        ->get()->map(function ($courseStudent) {
                            return [
                                'id' => User::find($courseStudent->student_id)->id,
                                'name' => User::find($courseStudent->student_id)->name,
                                'email' => User::find($courseStudent->student_id)->email,
                            ];
                        }),
                ];
            }),
        ]);
    }

    public function create_course(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'teacher' => 'required|integer',
            'section' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Verifique los datos ingresados',
                'errors' => $validator->errors(),
            ]);
        }

        // Validar que la sección no exista ya en la tabla courses (status_id = 1)
        $exists = \App\Models\Course::where('section', $request->section)
            ->where('status_id', 1)
            ->exists();
        if ($exists) {
            return response()->json([
                'message' => 'Ya existe un curso con esa sección.',
                'errors' => ['section' => ['La sección ya está registrada.']],
            ], 409);
        }

        try {
            DB::beginTransaction();

            $course = Course::create([
                'teacher_id' => $request->teacher,
                'section' => $request->section,
                'status_id' => 1,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Curso creado correctamente',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear el curso',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function add_student_to_course(Request $request, int $course)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'document' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Verifique los datos ingresados',
                'errors' => $validator->errors(),
            ]);
        }

        try {
            DB::beginTransaction();

            // Verificar si el usuario ya existe
            $user = User::where('email', $request->document)->first();
            if ($user) {
                return response()->json([
                    'message' => 'Ya existe un usuario con ese documento',
                ], 409);
            }

            // Crear el usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->document,
                'password' => Hash::make($request->document),
            ]);

            $courseStudent = CourseStudent::create([
                'course_id' => $course,
                'student_id' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Estudiante agregado al curso correctamente',
                'data' => $courseStudent,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al agregar el estudiante al curso',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete_course(Request $request, int $id)
    {
        try {
            DB::beginTransaction();

            $courseToDelete = Course::findOrFail($id);
            $courseToDelete->status_id = 2; // Assuming 2 is the status for deleted courses
            $courseToDelete->save();

            DB::commit();

            return response()->json([
                'message' => 'Curso eliminado correctamente',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al eliminar el curso',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index_teachers(Request $request)
    {
        $teachers = User::where('role_id', 2)->where('status_id', 1)->get();

        return response()->json([
            'data' => $teachers
        ]);
    }

    public function create_teacher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string',
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
        $teacher = \App\Models\User::where('email', $request->input('email'))->first();

        if ($teacher) {
            // Si existe, actualiza los datos y cambia status_id a 1 (activo)
            $teacher->name = $request->input('name');
            $teacher->password = Hash::make($request->input('password'));
            $teacher->status_id = 1;
            // Otros campos si es necesario
            $teacher->save();
        } else {
            // Si no existe, crea el usuario profesor
            $teacher = \App\Models\User::create([
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

    public function update_teacher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'name' => 'required|string',
            'email' => 'required|string',
            'password' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Verifique los datos ingresados',
                'errors' => $validator->errors(),
            ], 422);
        }

        $teacher = $request->id;

        try {
            DB::beginTransaction();

            $teacherToUpdate = User::findOrFail($teacher);
            $teacherToUpdate->name = $request->name;
            $teacherToUpdate->email = $request->email;
            if ($request->has('password') && !empty($request->password)) {
                $teacherToUpdate->password = Hash::make($request->password);
            }
            $teacherToUpdate->save();

            DB::commit();

            return response()->json([
                'message' => 'Profesor actualizado correctamente',
                'data' => $teacherToUpdate,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar el profesor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete_teacher(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Verifique los datos ingresados',
                'errors' => $validator->errors(),
            ]);
        }

        try {
            DB::beginTransaction();

            $teacherToDelete = User::findOrFail($id);
            $teacherToDelete->status_id = 2;
            $teacherToDelete->save();

            DB::commit();

            return response()->json([
                'message' => 'Profesor eliminado correctamente',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al eliminar el profesor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
