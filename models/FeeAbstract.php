<?php
declare(strict_types=1);

namespace restFee\models;

use yii\httpclient\Client;
use Yii;
use yii\web\NotFoundHttpException;

abstract class FeeAbstract
{
    protected const UNAVAILABLE_METHOD_MESSAGE = 'Method unavailable for this currency';
    protected $baseUrl;
    protected $currency;
    /** @var Client  */
    public $client;

    public function __construct()
    {
        $this->client = new Client(['baseUrl' => $this->baseUrl]);
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
    public function getCurrentMempoolWeight(): float
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
