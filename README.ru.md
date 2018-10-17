# Позволяет индексировать логи из yii2 в Elasticsearch
Работает в паре с Mirocow/yii2-elasticsearch](https://github.com/Mirocow/yii2-elasticsearch)

[![Maintainability](https://api.codeclimate.com/v1/badges/fdb8ceb634a97a184f90/maintainability)](https://codeclimate.com/github/Mirocow/yii2-elasticsearch-log/maintainability)

* Вывод удобно проссмаривать и администрировать с помощью Kibana
* Формат даты предоставляется в виде *YYYY-MM-DD HH:mm:ss*

Docs are available in russian and [english](README.md).

# Установка

```bash
$ composer require --prefer-dist mirocow/yii2-elasticsearch-log
```

# Настройка

```php

return [
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'mirocow\elasticsearch\log\ElasticsearchTarget',
                    'levels' => ['error', 'warning'],
                    'index' => 'yii-log',
                    'type' => 'console',
                ],
            ],
        ],
    ],
];
```


# Видео уроки:

Хорошее видео по настройке данных в Kibana
* https://www.youtube.com/watch?v=imrKm6dV3NQ

# Зависит от:

* [Mirocow/yii2-elasticsearch](https://github.com/Mirocow/yii2-elasticsearch)
