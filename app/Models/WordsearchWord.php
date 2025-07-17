<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WordsearchWord
 * 
 * @property int $id
 * @property int $wordsearch_id
 * @property string $english_word
 * @property string $spanish_word
 * @property string $emoji
 * 
 * @property Wordsearch $wordsearch
 *
 * @package App\Models
 */
class WordsearchWord extends Model
{
	protected $table = 'wordsearch_words';
	public $timestamps = false;

	protected $casts = [
		'wordsearch_id' => 'int'
	];

	protected $fillable = [
		'wordsearch_id',
		'english_word',
		'spanish_word',
		'emoji'
	];

	public function wordsearch()
	{
		return $this->belongsTo(Wordsearch::class);
	}
}
