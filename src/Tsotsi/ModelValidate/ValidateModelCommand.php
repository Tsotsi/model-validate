<?php

namespace Tsotsi\ModelValidate;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ValidateModelCommand extends Command implements SelfHandling
{
//    protected $signature = 'make:v-model {validate_model}';
    protected $name = 'make:v-model';
    protected $description = 'new a validate model';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
        parent::__construct();
    }

    public function getArguments()
    {
        return [
            [
                'validate_model',
                InputArgument::REQUIRED,
                '输入要生成的模型名称'
            ]
        ];
    }

    public function getOptions()
    {
        return [
            [
                'table',
                't',
                InputOption::VALUE_OPTIONAL,
                '自定义表名',
            ]
        ];
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        //
        $validate_model = $this->argument('validate_model');
        $table = $this->option('table');
        $basename = basename($validate_model);
        $table = empty($table) ? str_plural(camel_case($basename)) : $table;
        $file_path = app_path($validate_model . '.php');
        $dir = dirname($file_path);
        if (!is_dir($dir)) {
            mkdir($dir, 0744, true);
        }
        @unlink($file_path);
        if (!file_exists($file_path)) {
            $content = '';
            $namespace = str_replace(app_path(), '', realpath($dir));
            $spaces = [

            ];
            $base = \App::getNamespace();
            while ($namespace_new = dirname($namespace)) {
                if (($namespace_new == DIRECTORY_SEPARATOR || $namespace_new == '.') && ($namespace == DIRECTORY_SEPARATOR || $namespace == '.')) {
                    break;
                }
                $n = str_replace($namespace_new, '', $namespace);
                array_unshift($spaces, $n);
                $namespace = $namespace_new;

            }
            $namespace = $base . implode($spaces, '\\');

            $res = \DB::select('SHOW FULL COLUMNS FROM `' . \DB::getTablePrefix() . $table . '`');
            $s = var_export($res, true);
            $match_table = [
                'int' => 'integer',
                'char' => 'string',
                'decimal' => 'numeric',
                'boolean' => 'boolean',
                'text' => 'string',
                'blob' => 'string',
                'numeric' => 'numeric',

            ];
            $rules = [];
            $attributes_trans = [];
            $columns = [];
            $parameters = [];
            foreach ($res as $col) {
                $columns[] = $col->Field;
                $rule = [];
//                $type = preg_replace('/\(.*\)$/', '', $col['Type']);

                if ($col->Null == 'NO' && is_null($col->Default)) {
                    $rule[] = 'required';
                }
                preg_match('/^(\w+)(\((?<size>.*)\))?\s*(?<is_unsigned>unsigned)?/', $col->Type, $match);
                $type = Arr::get($match, 1, '');
                $size = Arr::get($match, 'size', false);
                $match_table_key = array_keys($match_table);
                $match_key = array_reduce($match_table_key, function ($c, $i) use ($type) {
                    if (strpos($type, $i) !== false) {
                        return $i;
                    }
                    return $c;
                }, false);
                if ($match_key) {
                    $rule[] = $match_table[$match_key];

                    if ($size) {
                        if ($match_table[$match_key] == 'integer') {
                            $size = str_pad('', $size, '9');
                        } elseif ($match_table[$match_key] == 'numeric') {
                            $size = explode(',', $size);
                            $r = isset($size[1]) ? (int)$size[1] : 0;
                            $l = (int)$size[0] - $r;
                            $size = str_pad('', $l, '9') . ($r ? str_pad('.', $r + 1, '9') : '');
                        }


                        $rule[] = 'max:' . $size;
                    }
                    $is_unsigned = Arr::get($match, 'is_unsigned', false);
                    if ($is_unsigned) {
                        $rule[] = 'min:0';
                    }
                } else {
                    if (strcasecmp($type, 'enum') == 0 or strcasecmp($type, 'set') == 0) {
                        if (!empty($size)) {
                            $sets = explode(',', $size);
                            array_walk($sets, function (&$v) {
                                $v = trim($v, '\'"');
                            });
                            $rule[] = 'in:' . implode(',', $sets);
                        }
                    }
                }

                $rule_str = '\'' . addslashes($col->Field) . '\'=>[[\'rule\' =>\'' . implode('|', $rule) . '\'';
                if ($col->Extra == 'auto_increment') {
                    $rule_str .= ', \'on\'=>\'updating\']]';
                    array_pop($columns);
                } else {
                    $rule_str .= ']]';
                }
                if ($col->Key == 'PRI' && $col->Field != 'id') {
                    $parameters[] = 'protected $primaryKey=\'' . $col->Field . '\';';
                }
                $rules[] = $rule_str;
                //comment
                $attributes_trans[$col->Field] = empty($col->Comment) ? ucfirst(camel_case($col->Field)) : $col->Comment;
            }
            $rules_str = '[' . PHP_EOL . implode(',' . PHP_EOL, $rules) . PHP_EOL . ']';//var_export($rules, true);
            $attributes_trans_str = var_export($attributes_trans, true);
            $columns_str = '[\'' . (implode('\',\'', $columns)) . '\']';
            if (!in_array('updated_at', $columns) || !in_array('created_at', $columns)) {
                $parameters[] = 'public $timestamps=false;';
            }
            $parameters_str = implode(PHP_EOL, $parameters) . PHP_EOL;
            $model = <<<MODEL
<?php

namespace {$namespace};

use Tsotsi\ModelValidate\ValidateModel;
class $basename extends ValidateModel
{
    protected \$table='$table';
    protected \$fillable=$columns_str;
    $parameters_str
    /**
     * ['id'=>['rule'=>'required|integer','on'=>'saving']]
     * @var array
     */
    protected static \$_rules = $rules_str;
    /**
     * @return array
     */
    public static function getAttributesTrans()
    {
        return $attributes_trans_str;
    }
}
MODEL;
            if (false !== file_put_contents($file_path, $model)) {
                $this->info('模型生成成功');
            } else {
                $this->error('模型生成失败');
            }
        } else {
            $this->error('模型已经存在');
        }

    }
}
