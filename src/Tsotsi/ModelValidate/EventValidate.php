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
        $flag = true;
        $rules = $model::getRules($name);
        if (!empty($rules)) {
            $validator = \Validator::make($model->getAttributes(), $rules,trans('tsotsi::validate.messages'), $model::getAttributesTrans());
            if ($validator->fails())
            \Session::flash('errors',$validator->messages());
            return $validator->passes();
        }
        return $flag;
    }

}
