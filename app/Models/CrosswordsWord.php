<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CrosswordsWord
 * 
 * @property int $id
 * @property int $crossword_id
 * @property string $english_word
 * @property string|null $spanish_word
 * @property string $emoji
 * 
 * @property Crossword $crossword
 *
 * @package App\Models
 */
class CrosswordsWord extends Model
{
	protected $table = 'crosswords_words';
	public $timestamps = false;

	protected $casts = [
		'crossword_id' => 'int'
	];

	protected $fillable = [
		'crossword_id',
		'english_word',
		'spanish_word',
		'emoji'
	];

	public function crossword()
	{
		return $this->belongsTo(Crossword::class);
	}
}
