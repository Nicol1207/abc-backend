<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Tema
 * 
 * @property string $descripcion
 * @property string $titulo
 * @property string $color
 * @property int|null $numero
 * @property int $id_temas
 * @property int $status_id
 * @property int $course_id
 * 
 * @property Course $course
 * @property Status $status
 * @property Collection|Contenido[] $contenidos
 *
 * @package App\Models
 */
class Tema extends Model
{
	protected $table = 'temas';
	protected $primaryKey = 'id_temas';
	public $timestamps = false;

	protected $casts = [
		'numero' => 'int',
		'status_id' => 'int',
		'course_id' => 'int'
	];

	protected $fillable = [
		'descripcion',
		'titulo',
		'color',
		'numero',
		'status_id',
		'course_id'
	];

	public function course()
	{
		return $this->belongsTo(Course::class);
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function contenidos()
	{
		return $this->hasMany(Contenido::class, 'id_tema_fk');
	}
}
