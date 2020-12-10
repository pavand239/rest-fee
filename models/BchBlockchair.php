<?php
declare(strict_types=1);

namespace restFee\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;

class BchBlockchair extends FeeAbstract
{
    public function __construct()
    {
        $this->baseUrl = 'https://api.blockchair.com/bitcoin-cash/stats';
        $this->currency = 'BCH';
        parent::__construct();
    }

    /**
     * получить всю статистику из api
     * @return array
     * @throws Exception|InvalidConfigException
     */
    public function getStat(): array {
        return $this->sendRequestSimple('GET','','data');
    }

    /**
     * получить вес мемпула (кэширование)
     * @return float
     */
    public function getMempoolWeight(): float
    {
        return Yii::$app->cache->getOrSet(
            $this->getCacheName('mempool-weight'),
            function ()
            {
                return $this->getMempoolWeightFromApi();
            },
            60
        );
    }

    /**
     * текущая нагрузка сети на оснвое веса мемпула
     * @return int[]
     */
    public function getCurrentLoad(): array
    {
        $weight = $this->getMempoolWeight();
        $load = intval(ceil($weight/50));
        if ($load>100) {
            $load = 100;
        }
        return ['currentLoad'=>$load];
    }

    /**
     * вес мемпула из api (без кэширования)
     * @return float
     * @throws Exception|InvalidConfigException
     */
    public function getMempoolWeightFromApi(): float
    {
        return $this->getStat()['mempool_size']/self::BYTES_PER_MEGABYTE;
    }

    /**
     * рекомендуемая комиссия satoshi/byte (без кэширование)
     * @return string
     * @throws Exception|InvalidConfigException
     */
    public function getRecommendedFeeFromApi(): string
    {
        return (string)$this->getStat()['suggested_transaction_fee_per_byte_sat'];
    }

}
