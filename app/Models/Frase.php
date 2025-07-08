<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Frase
 * 
 * @property int $id
 * @property string $frase_en
 * @property string $frase_es
 *
 * @package App\Models
 */
class Frase extends Model
{
	protected $table = 'frases';
	public $timestamps = false;

	protected $fillable = [
		'frase_en',
		'frase_es'
	];
}
