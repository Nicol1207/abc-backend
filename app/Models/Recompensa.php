<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Recompensa
 * 
 * @property int $id_recompensa
 * @property string $descripcion
 * @property string $titulo
 * @property int $status_id
 * 
 * @property Status $status
 * @property Collection|RecompensaEstudiante[] $recompensa_estudiantes
 *
 * @package App\Models
 */
class Recompensa extends Model
{
	protected $table = 'recompensa';
	protected $primaryKey = 'id_recompensa';
	public $timestamps = false;

	protected $casts = [
		'status_id' => 'int'
	];

	protected $fillable = [
		'descripcion',
		'titulo',
		'status_id'
	];

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function recompensa_estudiantes()
	{
		return $this->hasMany(RecompensaEstudiante::class, 'id_recompensa_fk');
	}
}
