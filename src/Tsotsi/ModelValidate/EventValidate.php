<?php
/**
 * Created by PhpStorm.
 * User: Tsotsi
 * Date: 15/11/17
 * Time: 下午3:45
 */

namespace Tsotsi\ModelValidate;


class EventValidate
{
    public static function creating($model)
    {
        return static::validate($model, 'creating');
    }

    public static function created($model)
    {
        return static::validate($model, 'created');
    }

    public static function updating($model)
    {
        return static::validate($model, 'updating');
    }

    public static function updated($model)
    {
        return static::validate($model, 'updated');
    }

    public static function deleting($model)
    {
        return static::validate($model, 'deleting');
    }

    public static function deleted($model)
    {
        return static::validate($model, 'deleted');
    }

    public static function saving($model)
    {
        return static::validate($model, 'saving');
    }

    public static function saved($model)
    {
        return static::validate($model, 'saved');
    }

    public static function restoring($model)
    {
        return static::validate($model, 'restoring');
    }

    public static function restored($model)
    {
        return static::validate($model, 'restored');
    }


    public static function validate($model, $name)
    {
        $flag = true;
        $rules = $model::getRules($name);
        if (!empty($rules)) {
            $validator = \Validator::make($model->getAttributes(), $rules, [], $model::getAttributesTrans());
            if ($validator->fails())
            \Session::flash('errors',$validator->messages());
            return $validator->passes();
        }
        return $flag;
    }

}
