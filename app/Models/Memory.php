<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Memory
 * 
 * @property int $id
 * @property int $activity_id
 * 
 * @property Activity $activity
 * @property Collection|MemoriesWord[] $memories_words
 *
 * @package App\Models
 */
class Memory extends Model
{
	protected $table = 'memories';
	public $timestamps = false;

	protected $casts = [
		'activity_id' => 'int'
	];

	protected $fillable = [
		'activity_id'
	];

	public function activity()
	{
		return $this->belongsTo(Activity::class);
	}

	public function memories_words()
	{
		return $this->hasMany(MemoriesWord::class);
	}
}
