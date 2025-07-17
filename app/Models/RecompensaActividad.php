<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RecompensaActividad
 * 
 * @property int $id
 * @property int $reward_id
 * @property int $activity_id
 * @property int $student_id
 * @property int $cantidad
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Activity $activity
 * @property Recompensa $recompensa
 * @property User $user
 *
 * @package App\Models
 */
class RecompensaActividad extends Model
{
	protected $table = 'recompensa_actividad';

	protected $casts = [
		'reward_id' => 'int',
		'activity_id' => 'int',
		'student_id' => 'int',
		'cantidad' => 'int'
	];

	protected $fillable = [
		'reward_id',
		'activity_id',
		'student_id',
		'cantidad'
	];

	public function activity()
	{
		return $this->belongsTo(Activity::class);
	}

	public function recompensa()
	{
		return $this->belongsTo(Recompensa::class, 'reward_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'student_id');
	}
}
