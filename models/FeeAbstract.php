<?php
declare(strict_types=1);

namespace restFee\models;

use yii\httpclient\Client;
use Yii;

abstract class FeeAbstract
{
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
     * @return array ['currentLoad' => int]
     */
    abstract public function getCurrentLoad(): array;

    /**
     * @return array [fee => weight(WU)]
     */
    abstract public function getMempool(): array;

    /**
     * @return integer
     */
    abstract public function getRecommendedFeeFromApi(): int;

    /**
     * @return array
     */
    abstract public function getMempoolFromApi(): array;

    /**
     * @return float
     */
    abstract public function getCurrentMempoolWeight(): float;

    /**
     * @return array
     */
    abstract public function getBlocksMinFee(): array;
}
