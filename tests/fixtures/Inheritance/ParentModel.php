<?php

namespace Glhd\LaraLint\Tests\fixtures\Inheritance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentModel extends Model
{
	public function relatedItems(): HasMany
	{
		return $this->hasMany(Model::class);
	}
}
