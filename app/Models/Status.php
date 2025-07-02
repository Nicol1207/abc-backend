<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Status
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|Contenido[] $contenidos
 * @property Collection|Recompensa[] $recompensas
 * @property Collection|Tema[] $temas
 *
 * @package App\Models
 */
class Status extends Model
{
	protected $table = 'statuses';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function contenidos()
	{
		return $this->hasMany(Contenido::class);
	}

	public function recompensas()
	{
		return $this->hasMany(Recompensa::class);
	}

	public function temas()
	{
		return $this->hasMany(Tema::class);
	}
}
