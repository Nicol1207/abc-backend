<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Course
 * 
 * @property int $id
 * @property int $teacher_id
 * @property string $section
 * @property int $status_id
 * 
 * @property Status $status
 * @property User $user
 * @property Collection|Contenido[] $contenidos
 * @property Collection|CourseStudent[] $course_students
 *
 * @package App\Models
 */
class Course extends Model
{
	protected $table = 'courses';
	public $timestamps = false;

	protected $casts = [
		'teacher_id' => 'int',
		'status_id' => 'int'
	];

	protected $fillable = [
		'teacher_id',
		'section',
		'status_id'
	];

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'teacher_id');
	}

	public function contenidos()
	{
		return $this->hasMany(Contenido::class);
	}

	public function course_students()
	{
		return $this->hasMany(CourseStudent::class);
	}
}
