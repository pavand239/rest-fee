<?php
declare(strict_types=1);

namespace restFee\models;

use yii\httpclient\Client;
use Yii;
use yii\web\NotFoundHttpException;

abstract class FeeAbstract
{
    /** @var string сообщение об ошибке при недоступности метода для выбранной валюты */
    protected const UNAVAILABLE_METHOD_MESSAGE = 'Method unavailable for this currency';
    /** @var string базовый url api */
    protected $baseUrl;
    /** @var string код валюты */
    protected $currency;

    public const BYTES_PER_MEGABYTE = 1048576;
    /** @var Client  */
    public $client;

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
        return $this->currency.$name;
    }
    /**
     * возвращает размер рекомендованной комиссии с кэшированием
     * @return array ['recommendedFee' => int]
     */
    public function getRecommendedFee(): array
    {
        $fee = Yii::$app->cache->getOrSet($this->currency.'_recommendedFee', function () {
            return $this->getRecommendedFeeFromApi();
        }, 60);
        return ['recommendedFee'=>$fee];
    }
    /**
     * @return integer
     */
    abstract public function getRecommendedFeeFromApi(): int;

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
}
