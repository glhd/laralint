<?php

use Glhd\LaraLint\Presets\LaraLint;

return [
	/*
	|--------------------------------------------------------------------------
	| LaraLint Preset
	|--------------------------------------------------------------------------
	|
	| This is the preset that you'd like to use. If one of the provided presets
	| does not suit your needs, simply write your own that implements the
	| Glhd\LaraLint\Contracts\Preset interface.
	|
	*/
	'preset' => LaraLint::class,
	
	/*
	|--------------------------------------------------------------------------
	| Excluded Directories
	|--------------------------------------------------------------------------
	|
	| Unless overridden via the command line, LaraLint will not lint files in
	| these excluded directories.
	|
	*/
	'excluded_directories' => [
		'bootstrap',
		'vendor',
		'public',
		'storage',
		'node_modules',
	],
	
	/*
	|--------------------------------------------------------------------------
	| Excluded Files
	|--------------------------------------------------------------------------
	|
	| Unless overridden via the command line, LaraLint will not lint files 
	| with these excluded names.
	|
	*/
	'excluded_files' => [
		'_ide_helper.php',
	],
	
	/*
	|--------------------------------------------------------------------------
	| Maximum Allowed Non-RESTful Methods Inside a RESTful Controller
	|--------------------------------------------------------------------------
	|
	| The PreferFullyRestfulControllers linter will flag controllers that
	| combine both RESTful and non-RESTful methods. Set this to the number of
	| non-RESTful methods you will allow inside a controller that has at least
	| one RESTful method (index/create/store/show/etc).
	|
	*/
	'max_non_restful_methods' => 1,
	
	/*
	|--------------------------------------------------------------------------
	| Model Class Names
	|--------------------------------------------------------------------------
	|
	| Some linters should only run on models. If you have additional classes
	| that should be considered models, add them here.
	|
	*/
	'models' => [
		'App\\Model',
		'Illuminate\\Database\\Eloquent\\Model',
	],
	
	/*
	|--------------------------------------------------------------------------
	| Custom Implementations
	|--------------------------------------------------------------------------
	|
	| If you have custom implementations of framework classes that you want to
	| enforce the usage of, do so here.
	|
	*/
	'custom_implementations' => [
		'Illuminate\\Foundation\\Http\\FormRequest' => 'App\\Http\\Requests\\Request',
		'Illuminate\\Routing\\Controller' => 'App\\Http\\Controllers\\Controller',
	],
	
	/*
	|--------------------------------------------------------------------------
	| Relationship Class Names
	|--------------------------------------------------------------------------
	|
	| Some linters determine whether a model method is a relationship by
	| checking its return type. If you have custom relationship classes, add
	| them here.
	|
	*/
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
	
	/*
	|--------------------------------------------------------------------------
	| Relationship Heuristics
	|--------------------------------------------------------------------------
	|
	| Some linters determine whether a model method is a relationship by
	| looking for certain strings inside the method's body. If you have custom
	| helper methods or would like to add additional heuristics, do so here. 
	|
	*/
	'relationship_heuristics' => [
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
