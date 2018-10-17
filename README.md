# Elasticsearch log storage module based on mirocow/yii2-elasticsearch

[![Latest Stable Version](https://poser.pugx.org/mirocow/yii2-elasticsearch-log/v/stable)](https://packagist.org/packages/mirocow/yii2-elasticsearch-log) [![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FMirocow%2Fyii2-elasticsearch-log.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2FMirocow%2Fyii2-elasticsearch-log?ref=badge_shield)

[![Latest Unstable Version](https://poser.pugx.org/mirocow/yii2-elasticsearch-log/v/unstable)](https://packagist.org/packages/mirocow/yii2-elasticsearch-log) 
[![Total Downloads](https://poser.pugx.org/mirocow/yii2-elasticsearch-log/downloads)](https://packagist.org/packages/mirocow/yii2-elasticsearch-log) [![License](https://poser.pugx.org/mirocow/yii2-elasticsearch-log/license)](https://packagist.org/packages/mirocow/yii2-elasticsearch-log)
[![Maintainability](https://api.codeclimate.com/v1/badges/fdb8ceb634a97a184f90/maintainability)](https://codeclimate.com/github/Mirocow/yii2-elasticsearch-log/maintainability)

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


## License
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FMirocow%2Fyii2-elasticsearch-log.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2FMirocow%2Fyii2-elasticsearch-log?ref=badge_large)