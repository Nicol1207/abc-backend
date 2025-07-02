<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Contenido
 * 
 * @property int $id_contenido
 * @property string|null $titulo
 * @property string|null $url
 * @property int|null $id_tipocontenido_fk
 * @property int|null $id_tema_fk
 * @property int $status_id
 * 
 * @property Status $status
 * @property Tema|null $tema
 * @property TipoContenido|null $tipo_contenido
 * @property Collection|ContenidoUsuario[] $contenido_usuarios
 *
 * @package App\Models
 */
class Contenido extends Model
{
	protected $table = 'contenido';
	protected $primaryKey = 'id_contenido';
	public $timestamps = false;

	protected $casts = [
		'id_tipocontenido_fk' => 'int',
		'id_tema_fk' => 'int',
		'status_id' => 'int'
	];

	protected $fillable = [
		'titulo',
		'url',
		'id_tipocontenido_fk',
		'id_tema_fk',
		'status_id'
	];

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function tema()
	{
		return $this->belongsTo(Tema::class, 'id_tema_fk');
	}

	public function tipo_contenido()
	{
		return $this->belongsTo(TipoContenido::class, 'id_tipocontenido_fk');
	}

	public function contenido_usuarios()
	{
		return $this->hasMany(ContenidoUsuario::class, 'id_contenido_fk');
	}
}
