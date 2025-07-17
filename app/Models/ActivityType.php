<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ActivityType
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|Activity[] $activities
 *
 * @package App\Models
 */
class ActivityType extends Model
{
	protected $table = 'activity_types';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int'
	];

	protected $fillable = [
		'description'
	];

	public function activities()
	{
		return $this->hasMany(Activity::class);
	}
}
