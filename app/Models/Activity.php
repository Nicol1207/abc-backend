<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Activity
 * 
 * @property int $id
 * @property int $activity_type_id
 * @property int $teacher_id
 * @property string $title
 * @property string $description
 * @property int|null $points
 * 
 * @property ActivityType $activity_type
 * @property User $user
 * @property Collection|Crossword[] $crosswords
 * @property Collection|Memory[] $memories
 * @property Collection|Wordsearch[] $wordsearches
 *
 * @package App\Models
 */
class Activity extends Model
{
	protected $table = 'activities';
	public $timestamps = false;

	protected $casts = [
		'activity_type_id' => 'int',
		'teacher_id' => 'int',
		'points' => 'int'
	];

	protected $fillable = [
		'activity_type_id',
		'teacher_id',
		'title',
		'description',
		'points'
	];

	public function activity_type()
	{
		return $this->belongsTo(ActivityType::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'teacher_id');
	}

	public function crosswords()
	{
		return $this->hasMany(Crossword::class);
	}

	public function memories()
	{
		return $this->hasMany(Memory::class);
	}

	public function wordsearches()
	{
		return $this->hasMany(Wordsearch::class);
	}
}
