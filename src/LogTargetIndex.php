<?php
namespace mirocow\elasticsearch\log;

use mirocow\elasticsearch\components\indexes\AbstractSearchIndex;
use mirocow\elasticsearch\exceptions\SearchIndexerException;

/**
 * Class LogTargetIndex
 * @package common\repositories\indexes
 */
class LogTargetIndex extends AbstractSearchIndex
{
    /** @var string */
    public $index_name = 'index_log';

    /** @var string */
    public $index_type = 'yii';

    /** @inheritdoc */
    protected function indexConfig(): array
    {
        return [
            'index' => $this->name(),
            'body' => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                    // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analysis-analyzers.html
                    // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analysis.html
                    'analysis' => [
                        'filter' => [
                            '_delimiter' => [
                                'type' => 'word_delimiter',
                                'generate_word_parts' => true,
                                'catenate_words' => true,
                                'catenate_numbers' => true,
                                'catenate_all' => true,
                                'split_on_case_change' => true,
                                'preserve_original' => true,
                                'split_on_numerics' => true,
                                'stem_english_possessive' => true // `s
                            ],
                            'fulltext_index_ngram_filter' => [
                                'type' => 'edge_ngram',
                                'min_gram' => '2',
                                'max_gram' => '20',
                            ],

                            /**
                             * Russian
                             */

                            "russian_stop" => [
                                "type" => "stop",
                                "stopwords" => "_russian_",
                            ],
                            "russian_keywords" => [
                                "type" => "keyword_marker",
                                "keywords" => ["пример"],
                            ],
                            "russian_stemmer" => [
                                "type" => "stemmer",
                                "language" => "russian",
                            ],

                            /**
                             * English
                             */

                            "english_stop" => [
                                "type" => "stop",
                                "stopwords" => "_english_",
                            ],
                            "english_keywords" => [
                                "type" => "keyword_marker",
                                "keywords" => ["example"],
                            ],
                            "english_stemmer" => [
                                "type" => "stemmer",
                                "language" => "english",
                            ],
                            "english_possessive_stemmer" => [
                                "type" => "stemmer",
                                "language" => "possessive_english",
                            ],
                        ],
                        // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analyzer.html
                        // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/search-analyzer.html
                        'analyzer' => [
                            // victoria's, victorias, victoria
                            'autocomplete' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => [
                                    // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analysis-standard-tokenfilter.html
                                    'standard',
                                    // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analysis-lowercase-tokenizer.html
                                    'lowercase',
                                    // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analysis-stop-tokenfilter.html
                                    'stop',
                                    // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analysis-asciifolding-tokenfilter.html
                                    'asciifolding',
                                    // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analysis-porterstem-tokenfilter.html
                                    'porter_stem',
                                    //'english_stemmer',
                                    //'russian_stemmer',
                                    '_delimiter',
                                ],
                            ],
                            'search_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => [
                                    // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analysis-standard-tokenfilter.html
                                    'standard',
                                    // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analysis-lowercase-tokenizer.html
                                    'lowercase',
                                    // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analysis-stop-tokenfilter.html
                                    'stop',
                                    // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analysis-asciifolding-tokenfilter.html
                                    'asciifolding',
                                    // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analysis-porterstem-tokenfilter.html
                                    'porter_stem',
                                    //'english_stemmer',
                                    //'russian_stemmer',
                                ],
                            ],
                            'suggestion_analyzer' => [
                                'filter' => [
                                    'lowercase',
                                ],
                                'tokenizer' => 'standard',
                            ],
                            'fulltext_search_analyzer' => [
                                'filter' => [
                                    'lowercase',
                                ],
                                'tokenizer' => 'standard',
                            ],
                            'lowercase_keyword_analyzer' => [
                                'filter' => [
                                    'lowercase',
                                ],
                                'tokenizer' => 'keyword',
                            ],
                        ],
                    ],
                ],
                'mappings' => [
                    $this->type() => [
                        // Определяет базовый набор свойств для группы полей
                        'dynamic_templates' => [
                            [
                                'attributes' => [
                                    'path_match' => 'attributes.*',
                                    'mapping' => [
                                        'index' => false,
                                    ],
                                ],
                            ],
                        ],
                        // При индексировании поля _all все поля документа объединяются в одну большую строку независимо от типа данных.
                        // По умолчанию поле _all включено.
                        "_all" => [
                            "enabled" => false
                        ],
                        'properties' => [

                            // Возвращаемые данные, не индексируются
                            // Заполняет модель методом populate
                            // Не индексируется
                            'attributes' => [
                                'properties' => [
                                    'created_at' => [
                                        "type" => "date",
                                        // 2016-12-28 16:21:30
                                        "format" => "yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis",
                                    ],
                                ],
                            ],

                        ],
                    ],
                ],
            ],
        ];
    }

}