<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Crossword
 * 
 * @property int $id
 * @property int $activity_id
 * 
 * @property Activity $activity
 * @property Collection|CrosswordsWord[] $crosswords_words
 *
 * @package App\Models
 */
class Crossword extends Model
{
	protected $table = 'crosswords';
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

	public function crosswords_words()
	{
		return $this->hasMany(CrosswordsWord::class);
	}
}
