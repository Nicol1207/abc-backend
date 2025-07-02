<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CourseStudent
 * 
 * @property int $id
 * @property int $course_id
 * @property int $student_id
 * 
 * @property Course $course
 * @property User $user
 *
 * @package App\Models
 */
class CourseStudent extends Model
{
	protected $table = 'course_students';
	public $timestamps = false;

	protected $casts = [
		'course_id' => 'int',
		'student_id' => 'int'
	];

	protected $fillable = [
		'course_id',
		'student_id'
	];

	public function course()
	{
		return $this->belongsTo(Course::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'student_id');
	}
}
