<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

/**
 * Class User
 * 
 * @property int $id
 * @property int $role_id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $status_id
 * 
 * @property Status|null $status
 * @property Collection|ContenidoUsuario[] $contenido_usuarios
 * @property Collection|CourseStudent[] $course_students
 * @property Collection|Course[] $courses
 * @property Collection|RecompensaEstudiante[] $recompensa_estudiantes
 *
 * @package App\Models
 */
class User extends Authenticatable
{
	/** @use HasFactory<\Database\Factories\UserFactory> */
	use HasApiTokens, HasFactory, Notifiable;

	protected $table = 'users';

	protected $casts = [
		'role_id' => 'int',
		'email_verified_at' => 'datetime',
		'status_id' => 'int'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'role_id',
		'name',
		'email',
		'email_verified_at',
		'password',
		'remember_token',
		'status_id'
	];

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function contenido_usuarios()
	{
		return $this->hasMany(ContenidoUsuario::class, 'id_usuario_fk');
	}

	public function course_students()
	{
		return $this->hasMany(CourseStudent::class, 'student_id');
	}

	public function courses()
	{
		return $this->hasMany(Course::class, 'teacher_id');
	}

	public function role()
	{
		return $this->belongsTo(Role::class, 'role_id');
	}

	public function userSessions()
	{
		return $this->hasMany(UserSession::class, 'user_id');
	}

	/**
	 * Devuelve el tiempo total de uso del sistema en segundos.
	 * @return int
	 */
	public function getTiempoTotalUso()
	{
		$timezone = config('app.timezone', 'UTC');
		return $this->userSessions->sum(function ($session) use ($timezone) {
			if ($session->login_at) {
				$inicio = $session->login_at instanceof \Carbon\Carbon ? $session->login_at : \Carbon\Carbon::parse($session->login_at);
				$inicio = $inicio->setTimezone($timezone);
				$fin = $session->logout_at ?
					($session->logout_at instanceof \Carbon\Carbon ? $session->logout_at : \Carbon\Carbon::parse($session->logout_at))
					: \Carbon\Carbon::now($timezone);
				$fin = $fin->setTimezone($timezone);
				$diff = $inicio->diffInSeconds($fin, false);
				Log::info('Sesion usuario', [
					'login_at' => $inicio->toDateTimeString(),
					'logout_at' => $fin->toDateTimeString(),
					'diff_seconds' => $diff
				]);
				return $diff > 0 ? $diff : 0;
			}
			return 0;
		});
	}

	/**
	 * Devuelve el tiempo total de uso del sistema en formato legible.
	 * @return string
	 */
	public function getTiempoTotalUsoLegible()
	{
		$segundos = $this->getTiempoTotalUso();
		$horas = floor($segundos / 3600);
		$minutos = floor(($segundos % 3600) / 60);
		$segundos = $segundos % 60;
		return "$horas h $minutos m $segundos s";
	}
}
