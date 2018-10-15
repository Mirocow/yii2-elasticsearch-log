<?php
namespace mirocow\elasticsearch\log;

use mirocow\elasticsearch\components\factories\IndexerFactory;
use Yii;
use yii\elasticsearch\Connection;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\log\Target;

/**
 * Elasticsearch Yii2 Log Target
 *
 * @author Mirocow <mr.mirocow@gmail.com>
 * @since 2.0
 */
class ElasticsearchTarget extends Target
{
    /**
     * @var string Elasticsearch index name
     */
    public $index = 'yii';

    /**
     * @var string Elasticsearch type name
     */
    public $type = 'log';

    /**
     * @var LogTargetIndex
     */
    public $db;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $config = [
            LogTargetIndex::class => [
                'class' => LogTargetIndex::class,
                'index_name' => $this->index,
                'index_type' => $this->type,
            ]
        ];
        $this->db = IndexerFactory::createIndex(LogTargetIndex::class, $config);
        $this->db->create(true);
    }

    /**
     * @inheritdoc
     */
    public function export()
    {
        $messages = array_map([$this, 'prepareMessage'], $this->messages);

        $body = implode("\n", $messages) . "\n";

        /** @var QueryBuilder $query */
        $query = $this->db->execute($body, 'bulk');
    }

    /**
     * @inheritdoc
     */
    public function collect($messages, $final)
    {
        $this->messages = array_merge($this->messages, static::filterMessages($messages, $this->getLevels(), $this->categories, $this->except));
        $count = count($this->messages);
        if ($count > 0 && ($final || $this->exportInterval > 0 && $count >= $this->exportInterval)) {
            // set exportInterval to 0 to avoid triggering export again while exporting
            $oldExportInterval = $this->exportInterval;
            $this->exportInterval = 0;
            $this->export();
            $this->exportInterval = $oldExportInterval;

            $this->messages = [];
        }
    }

    /**
     * Prepares a log message
     * @param array $message The log message to be formatted
     * @return string
     */
    public function prepareMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;

        $result = [
            'category' => $category,
            'level' => Logger::getLevelName($level),
            '@timestamp' => date('c', $timestamp),
        ];

        if (isset($message[4])) {
            $result['trace'] = $message[4];
        }

        if ($text instanceof \Exception) {
            $result['message'] = $text->getMessage();
            $result['exception'] = [
                'file' => $text->getFile(),
                'line' => $text->getLine(),
                'code' => $text->getCode(),
                'trace' => $text->getTraceAsString(),
            ];
        } elseif (is_string($text)) {
            $result['message'] = $text;
        } else {
            $result['message'] = VarDumper::export($text);
        }

        $result = array_merge($result, $this->getExtraFields());

        return Json::encode($result);
    }

    private $_extraFields = [];

    /**
     * Returns extra fields
     * @return array
     */
    public function getExtraFields()
    {
        return $this->_extraFields;
    }

    /**
     * Set extra fields
     * @param array $fields
     */
    public function setExtraFields(array $fields)
    {
        foreach ($fields as $name => $field) {
            if ($field instanceof \Closure || is_array($field) && is_callable($field)) {
                $this->_extraFields[$name] = call_user_func($field, Yii::$app);
            } else {
                $this->_extraFields[$name] = $field;
            }
        }
    }
}