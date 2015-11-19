# model-validate
laravel5 validate model

app.php

      'providers'=>[
        Tsotsi\ModelValidate\ValidateServiceProvider::class,
      ]

then

     php artisan vendor:publish
      
console
      //自定义表明
     php artisan make:v-model Models/T -t "t"
     //强制替换
     php artisan make:v-model Models/T -t "t" -f   
