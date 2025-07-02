<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ContenidoUsuario
 * 
 * @property int $id_contenidousuario
 * @property int|null $id_usuario_fk
 * @property int|null $id_contenido_fk
 * 
 * @property User|null $user
 * @property Contenido|null $contenido
 *
 * @package App\Models
 */
class ContenidoUsuario extends Model
{
	protected $table = 'contenido_usuario';
	protected $primaryKey = 'id_contenidousuario';
	public $timestamps = false;

	protected $casts = [
		'id_usuario_fk' => 'int',
		'id_contenido_fk' => 'int'
	];

	protected $fillable = [
		'id_usuario_fk',
		'id_contenido_fk'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'id_usuario_fk');
	}

	public function contenido()
	{
		return $this->belongsTo(Contenido::class, 'id_contenido_fk');
	}
}
