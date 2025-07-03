<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RecompensaEstudiante
 * 
 * @property int $id_recompensaestudiante
 * @property int|null $id_recompensa_fk
 * @property int|null $id_usuario_fk
 * @property int $cantidad
 * 
 * @property Recompensa|null $recompensa
 * @property User|null $user
 *
 * @package App\Models
 */
class RecompensaEstudiante extends Model
{
	protected $table = 'recompensa_estudiante';
	protected $primaryKey = 'id_recompensaestudiante';
	public $timestamps = false;

	protected $casts = [
		'id_recompensa_fk' => 'int',
		'id_usuario_fk' => 'int',
		'cantidad' => 'int'
	];

	protected $fillable = [
		'id_recompensa_fk',
		'id_usuario_fk',
		'cantidad'
	];

	public function recompensa()
	{
		return $this->belongsTo(Recompensa::class, 'id_recompensa_fk');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'id_usuario_fk');
	}
}
