<?php
declare(strict_types=1);

namespace restFee\models;

use UnexpectedValueException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use Yii;
use yii\httpclient\Exception;
use yii\web\NotFoundHttpException;

abstract class FeeAbstract
{
    /** @var string сообщение об ошибке при недоступности метода для выбранной валюты */
    protected const UNAVAILABLE_METHOD_MESSAGE = 'Method unavailable for this currency';
    /** @var string базовый url api */
    protected string $baseUrl;
    /** @var string код валюты */
    protected string $currency;

    public const BYTES_PER_MEGABYTE = 1048576;
    /** @var Client  */
    public Client $client;

    public function __construct()
    {
        $this->client = new Client(['baseUrl' => $this->baseUrl]);
    }

    /**
     * @param string $name
     * @return string name with currency prefix
     */
    public function getCacheName(string $name): string
    {
        return $this->currency.'_'.$name;
    }
    /**
     * возвращает размер рекомендованной комиссии с кэшированием
     * @return string[] ['recommendedFee' => string]
     */
    public function getRecommendedFee(): array
    {
        $fee = Yii::$app->cache->getOrSet(
            $this->getCacheName('recommended-fee'),
            function () {
                return $this->getRecommendedFeeFromApi();
            },
            60);
        return ['recommendedFee'=>$fee];
    }

    /**
     * @return string
     */
    abstract public function getRecommendedFeeFromApi(): string;

    /**
     * @return array ['currentLoad' => int]
     * @throws NotFoundHttpException
     */
    public function getCurrentLoad(): array
    {
        throw new NotFoundHttpException(self::UNAVAILABLE_METHOD_MESSAGE);
    }

    /**
     * @return array [fee => weight(WU)]
     * @throws NotFoundHttpException
     */
    public function getMempool(): array
    {
        throw new NotFoundHttpException(self::UNAVAILABLE_METHOD_MESSAGE);
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function getMempoolFromApi(): array
    {
        throw new NotFoundHttpException(self::UNAVAILABLE_METHOD_MESSAGE);
    }

    /**
     * @return float
     * @throws NotFoundHttpException
     */
    public function getMempoolWeight(): float
    {
        throw new NotFoundHttpException(self::UNAVAILABLE_METHOD_MESSAGE);
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function getBlocksMinFee(): array
    {
        throw new NotFoundHttpException(self::UNAVAILABLE_METHOD_MESSAGE);
    }

    /**
     * @param string $requestData json-rpc params
     * @param string $key key for ArrayHelper::getValue
     * @return mixed
     * @throws Exception
     * @throws \Exception
     */
    protected function sendRequestJsonRPC(string $requestData, string $key)
    {
        $response =  $this->client->post('',$requestData, ['content-type' => 'application/json'])->setFormat(Client::FORMAT_JSON)->send();
        if (!$response->isOk) {
            throw new UnexpectedValueException('Response is not ok');
        }
        if (!ArrayHelper::getValue($response->data, $key)) {
            throw new UnexpectedValueException('Response is not ok');
        }
        return ArrayHelper::getValue($response->data, $key);
    }

    /**
     * @param string $method
     * @param string $url
     * @param string $key key for ArrayHelper::getValue
     * @param array $data
     * @return mixed
     * @throws Exception
     * @throws InvalidConfigException
     * @throws \Exception
     */
    protected function sendRequestSimple(string $method, string $url, string $key, array $data = [])
    {
        $response = $this->client->createRequest()
            ->setMethod($method)
            ->setUrl($url)
            ->setData($data)
            ->setFormat(Client::FORMAT_JSON)
            ->setHeaders(['Content-Type' => 'application/json'])
            ->send();

        if (!$response->isOk) {
            throw new UnexpectedValueException('Response is not ok');
        }
        if (!ArrayHelper::getValue($response->data, $key)) {
            throw new UnexpectedValueException('Response is not ok');
        }
        return ArrayHelper::getValue($response->data, $key);
    }


    /**
     * подготовить json для json-rpc запроса
     * @param string $method
     * @param array $params
     * @return string
     */
    protected function prepareRequestData(string $method, array $params = []): string
    {
        return json_encode(
            [
                "jsonrpc" => "2.0",
                "method" => $method,
                "params" => $params,
                "id" => 1
            ]
        );
    }
}
