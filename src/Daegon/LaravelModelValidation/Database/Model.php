<?php namespace Daegon\LaravelModelValidation\Database;

use Daegon\LaravelModelValidation\Exceptions\ModelValidationFailException;
use Illuminate\Validation\Validator;

abstract class Model extends \Eloquent
{
    public $skipValidation = false;
    protected $throwValidationException = false;
    protected static $rules = [];
    protected static $messages = [];
    public $validation;
    public $validator;

    public function __construct(array $attributes = array(), Validator $validator = null)
    {
        parent::__construct($attributes);

        $this->validator = $validator ?: \App::make('validator');
    }

    public function save_(array $options = []){
        $this->throwValidationException = true;
        $callResult = parent::save($options);
        $this->throwValidationException = false;
        return $callResult;
    }

    public function update_(array $attributes = []){
        $this->throwValidationException = true;
        $callResult = parent::update($attributes);
        $this->throwValidationException = false;
        return $callResult;
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function(Model $model) {
            if($model->skipValidation) {
                return true;
            }
            if(!$model->isValid()) {
                if($model->throwValidationException) {
                    throw new ModelValidationFailException('Validation failed', $model->validation->errors());
                }
                return false;
            };
            return true;
        });
    }

    public function isValid() {
        $this->validation = $this->validator->make($this->attributes, static::$rules, static::$messages);
        return $this->validation->passes();
    }
}
