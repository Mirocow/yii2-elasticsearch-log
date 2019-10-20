<?php
namespace mirocow\elasticsearch\log;

use mirocow\elasticsearch\components\factories\IndexerFactory;
use mirocow\elasticsearch\exceptions\SearchQueryException;
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
 */
class ElasticsearchTarget extends Target
{
    public $hosts = [
        'localhost:9200'
    ];

    /**
     * @var string Elasticsearch index name
     */
    public $index = 'yii';

    /**
     * @var string Elasticsearch type name
     */
    public $type = 'log';
    
    /**
     * @var string DateTime format
     */    
    public $formatTime = 'Y-m-d H:i:s';

    /**
     * @var LogTargetIndex
     */
    public $db;

    /**
     * @var ExtraFields[] $extraFields
     */
    private $_extraFields = [];

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
                'hosts' => $this->hosts,
            ]
        ];
        $this->db = IndexerFactory::createIndex(LogTargetIndex::class, $config);
        $this->db->create(true);
    }

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
     * @inheritdoc
     */
    public function export()
    {
        $params = [];

        foreach ($this->messages as $message){
            $params['body'][] = [
                'index' => [
                    '_index' => $this->index,
                    '_type' => $this->type,
                ]
            ];

            $params['body'][] = $this->prepareMessage($message);
        }

        $this->db->execute($params, 'bulk');
    }

    /**
     * Prepares a log message
     * @param array $message The log message to be formatted
     * @return string
     */
    private function prepareMessage($message)
    {
        list($exception, $level, $category, $timestamp) = $message;

        $given = \DateTime::createFromFormat('U.u', $timestamp);
        $given->setTimezone(new \DateTimeZone("UTC"));

        $userId = 0;
        $page = '';
        $remoteIp = '';
        $remoteHost = 'localhost';
        $request = [];
        $params = [];
        $post = [];

        if(!Yii::$app->request->isConsoleRequest){
            if(!Yii::$app->user->isGuest) {
                $userId = Yii::$app->user->id;
            }
            $page = Yii::$app->request->getUrl();
            $remoteIp = Yii::$app->request->getRemoteIP();
            $remoteHost = Yii::$app->request->getRemoteHost();
            $request = Yii::$app->request->getQueryParams();
            $params = Yii::$app->request->getParams();
            $post = Yii::$app->request->getBodyParams();
        }

        $result = [
            'category' => $category,
            'level' => Logger::getLevelName($level),
            'attributes'=> [
                '@timestamp' => $given->format($this->formatTime),
            ],
            'userId' => $userId,
            'remote_ip' => $remoteIp,
            'remote_host' => $remoteHost,
            'page' => $page,
            'request' => Json::encode($request, JSON_PRETTY_PRINT),
            'post' => Json::encode($post, JSON_PRETTY_PRINT),
            'params' => Json::encode($params, JSON_PRETTY_PRINT),
        ];

        if (isset($message[4])) {
            $result['trace'] = $message[4];
        }
        if ($exception instanceof SearchQueryException) {
            $result['request'] = $exception->requestQuery;
        }
        if ($exception instanceof \Exception) {
            $result['message'] = $exception->getMessage();
            $result['exception'] = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'trace' => Json::encode($exception->getTrace(), JSON_PRETTY_PRINT),
            ];
        } elseif (is_string($exception)) {
            $result['message'] = $exception;
        } else {
            $result['message'] = VarDumper::export($exception);
        }

        return array_merge($result, $this->getExtraFields());

    }

}
