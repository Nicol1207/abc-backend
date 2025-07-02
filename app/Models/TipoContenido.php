<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TipoContenido
 * 
 * @property int $id_tipocontenido
 * @property string|null $descripcion
 * 
 * @property Collection|Contenido[] $contenidos
 *
 * @package App\Models
 */
class TipoContenido extends Model
{
	protected $table = 'tipo_contenido';
	protected $primaryKey = 'id_tipocontenido';
	public $timestamps = false;

	protected $fillable = [
		'descripcion'
	];

	public function contenidos()
	{
		return $this->hasMany(Contenido::class, 'id_tipocontenido_fk');
	}
}
