<?php
/**
 * Created by PhpStorm.
 * User: Tsotsi
 * Date: 15/11/17
 * Time: 下午3:11
 */

namespace Tsotsi\ModelValidate;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ValidateModel extends Model
{

    /**
     * ['id'=>['rule'=>'required|integer','on'=>'saving']]
     * @var array
     */
    protected static $_rules = [];

    protected static $guard_validate = [
    ];

    protected static function boot()
    {
        static::registerMyListener();
        parent::boot();
    }

    protected static function registerMyListener($priority = 0)
    {
        $events = [
            'creating', 'updating',
            'deleting', 'saving',
        ];
        $last_events = array_diff($events, self::$guard_validate);
        if(empty($last_events)){
            return;
        }
        foreach ($last_events as $event) {
            if (method_exists(EventValidate::class, $event)) {
                static::registerModelEvent($event, EventValidate::class . '@' . $event, $priority);
            }
        }
    }

    /**
     * @return array
     */

    public static function getRules($scene = 'saving')
    {
        $rules = [];
        array_walk(static::$_rules, function ($v, $k) use (&$rules, $scene) {
            $rules[$k]=[];
            array_walk($v,function($vv,$kk)use (&$rules, $scene,$k){
                if (isset($v['on'])) {
                    $scenes = explode(',', $vv['on']);
                    $rule = Arr::get($vv, 'rule', false);
                    if ($rule && in_array($scene, $scenes)) {
                        $rules[$k][] = $rule;
                    }
                } else {
                    if ($scene == 'saving') {
                        $rule = Arr::get($vv, 'rule', false);
                        if ($rule) {
                            $rules[$k][] = $rule;
                        }
                    }
                }
            });
            if(empty($rules[$k])){
                unset($rules[$k]);
            }else{
                $rules[$k]=implode('|',$rules[$k]);
            }
        });

        return $rules;
    }

    /**
     * @return array
     */
    public static function getAttributesTrans()
    {
        return [

        ];
    }

}
