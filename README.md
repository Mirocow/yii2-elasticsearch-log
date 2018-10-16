# Elasticsearch log storage module based on mirocow/yii2-elasticsearch

Docs are available in english and [russian](README.ru.md).

* Conclusion is easy to review and administer using Kibana
* The date format is provided in the form *YYYY-MM-DD HH:mm:ss*

# Install

```bash
$ composer require --prefer-dist mirocow/yii2-elasticsearch-log
```

# Setup

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

# Tutorial

How we can use Discover, Visualization and Dashboard with cusom data
* https://www.youtube.com/watch?v=imrKm6dV3NQ

# Depends

* [Mirocow/yii2-elasticsearch](https://github.com/Mirocow/yii2-elasticsearch)
