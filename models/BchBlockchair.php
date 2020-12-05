<?php
declare(strict_types=1);

namespace restFee\models;

use UnexpectedValueException;
use Yii;
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
     * @throws Exception
     */
    public function getStat(): array {
        $response = $this->client->get('')->send();
        if (!$response->isOk) {
            throw new UnexpectedValueException('Response is not ok');
        }
        if (!isset($response->data['data'])) {
            throw new UnexpectedValueException('Response is not ok');
        }
        return $response->data['data'];
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
        $load = intval(ceil($weight));
        if ($load>100) {
            $load = 100;
        }
        return ['currentLoad'=>$load];
    }

    /**
     * вес мемпула из api (без кэширования)
     * @return float
     * @throws Exception
     */
    public function getMempoolWeightFromApi(): float
    {
        return $this->getStat()['mempool_size']/self::BYTES_PER_MEGABYTE;
    }

    /**
     * рекомендуемая комиссия satoshi/byte (без кэширование)
     * @return int
     * @throws Exception
     */
    public function getRecommendedFeeFromApi(): string
    {
        return (string)$this->getStat()['suggested_transaction_fee_per_byte_sat'];
    }

}
