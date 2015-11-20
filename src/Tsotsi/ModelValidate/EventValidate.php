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

    public static function __callStatic($name,$parameters)
    {
        return static::validate($parameters[0],$name);
    }
    public function __call($name,$parameters)
    {
        return static::validate($parameters[0],$name);
    }


    public static function validate($model, $name)
    {
        return $model->isValid($name);
    }

}
