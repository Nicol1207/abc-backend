<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Wordsearch
 * 
 * @property int $id
 * @property int $activity_id
 * 
 * @property Activity $activity
 * @property Collection|WordsearchWord[] $wordsearch_words
 *
 * @package App\Models
 */
class Wordsearch extends Model
{
	protected $table = 'wordsearch';
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

	public function wordsearch_words()
	{
		return $this->hasMany(WordsearchWord::class);
	}
}
