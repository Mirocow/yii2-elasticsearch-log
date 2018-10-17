<?php
namespace mirocow\elasticsearch\log;

use mirocow\elasticsearch\components\indexes\AbstractSearchIndex;

/**
 * Class LogTargetIndex
 * @package common\repositories\indexes
 */
class LogTargetIndex extends AbstractSearchIndex
{
    /** @var string */
    public $index_name = 'yii';

    /** @var string */
    public $index_type = 'log';
    
    /** @var string @see https://www.elastic.co/guide/en/elasticsearch/reference/5.6/mapping-date-format.html */
    public $formatTime = 'yyyy-MM-dd HH:mm:ss';

    /** @inheritdoc */
    public function accepts($document)
    {
        return true;
    }

    /** @inheritdoc */
    public function documentIds()
    {
        return [];
    }

    /** @inheritdoc */
    public function documentCount()
    {
        return 0;
    }

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
                        ],
                        // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analyzer.html
                        // https://www.elastic.co/guide/en/elasticsearch/reference/5.6/search-analyzer.html
                        'analyzer' => [
                            // victoria's, victorias, victoria
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
                        "_all" => [
                            "enabled" => false
                        ],
                        'properties' => [

                            'attributes' => [
                                'properties' => [
                                    '@timestamp' => [
                                        "type" => "date",
                                        "format" => $this->formatTime,
                                    ],
                                ],
                            ],

                            'category' => [
                                'type' => 'text',
                                'search_analyzer' => 'search_analyzer',
                                'analyzer' => 'search_analyzer',
                            ],

                            'level' => [
                                'type' => 'keyword',
                            ],

                            'trace' => [
                                'type' => 'text',
                                'search_analyzer' => 'search_analyzer',
                                'analyzer' => 'search_analyzer',
                            ],

                            'message' => [
                                'type' => 'text',
                                'search_analyzer' => 'search_analyzer',
                                'analyzer' => 'search_analyzer',
                            ],

                            'exception' => [
                                'properties' => [
                                    'file' => [
                                        'type' => 'text',
                                    ],
                                    'line' => [
                                        'type' => 'integer',
                                    ],
                                    'code' => [
                                        'type' => 'integer',
                                    ],
                                    'trace' => [
                                        'type' => 'text',
                                        'search_analyzer' => 'search_analyzer',
                                        'analyzer' => 'search_analyzer',
                                    ],
                                ]
                            ],

                        ],
                    ],
                ],
            ],
        ];
    }

}
