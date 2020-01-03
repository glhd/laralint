<?php

use Glhd\LaraLint\Presets\LaraLint;

return [
	'preset' => LaraLint::class,
	
	'models' => [
		'App\\Model',
		'Illuminate\\Database\\Eloquent\\Model',
	],
	
	'relationships' => [
		'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
		'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
		'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
		'Illuminate\\Database\\Eloquent\\Relations\\HasManyThrough',
		'Illuminate\\Database\\Eloquent\\Relations\\HasOne',
		'Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany',
		'Illuminate\\Database\\Eloquent\\Relations\\HasOneThrough',
		'Illuminate\\Database\\Eloquent\\Relations\\MorphMany',
		'Illuminate\\Database\\Eloquent\\Relations\\MorphOne',
		'Illuminate\\Database\\Eloquent\\Relations\\MorphOneOrMany',
		'Illuminate\\Database\\Eloquent\\Relations\\MorphPivot',
		'Illuminate\\Database\\Eloquent\\Relations\\MorphTo',
		'Illuminate\\Database\\Eloquent\\Relations\\MorphToMany',
		'Illuminate\\Database\\Eloquent\\Relations\\Pivot',
	],
	
	'relationship_helpers' => [
		'return $this->hasOne(',
		'return $this->hasOneThrough(',
		'return $this->morphOne(',
		'return $this->belongsTo(',
		'return $this->morphTo(',
		'return $this->hasMany(',
		'return $this->hasManyThrough(',
		'return $this->morphMany(',
		'return $this->belongsToMany(',
		'return $this->morphToMany(',
		'return $this->morphedByMany(',
	],
];
