<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MemoriesWord
 * 
 * @property int $id
 * @property int $memory_id
 * @property string $english_word
 * @property string $spanish_word
 * @property string $emoji
 * 
 * @property Memory $memory
 *
 * @package App\Models
 */
class MemoriesWord extends Model
{
	protected $table = 'memories_words';
	public $timestamps = false;

	protected $casts = [
		'memory_id' => 'int'
	];

	protected $fillable = [
		'memory_id',
		'english_word',
		'spanish_word',
		'emoji'
	];

	public function memory()
	{
		return $this->belongsTo(Memory::class);
	}
}
