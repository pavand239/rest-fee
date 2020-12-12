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
     * рекомендуемая комиссия satoshi/byte (без кэширование)
     * @return string
     * @throws Exception|InvalidConfigException
     */
    public function getRecommendedFeeFromApi(): string
    {
        return (string)$this->getStat()['suggested_transaction_fee_per_byte_sat'];
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
     * получить вес мемпула (кэширование)
     * @return float
     */
    private function getMempoolWeight(): float
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
     * вес мемпула из api (без кэширования)
     * @return float
     * @throws Exception|InvalidConfigException
     */
    private function getMempoolWeightFromApi(): float
    {
        return $this->getStat()['mempool_size']/self::BYTES_PER_MEGABYTE;
    }

    /**
     * получить всю статистику из api
     * @return array
     * @throws Exception|InvalidConfigException
     */
    private function getStat(): array {
        return $this->sendRequestSimple('GET','','data');
    }
}
