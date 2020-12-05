<?php

namespace restFee\models;


use UnexpectedValueException;
use Yii;

use yii\httpclient\Exception;

class BitcoinerLiveFee extends FeeAbstract
{
    public function __construct()
    {
        $this->baseUrl = 'https://bitcoiner.live/api';
        $this->currency = 'BTC';
        parent::__construct();
    }

    /**
     * возвращает нагрузку сети с кэшированием
     * @return array ['currentLoad' => string]
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
     * возращает мемпул с кэшированием
     * @return array
     */
    public function getMempool(): array
    {
        return Yii::$app->cache->getOrSet(
            $this->getCacheName('mempool'),
            function ()
            {
                return $this->getMempoolFromApi();
            },
            60
        );
    }

    /**
     * возвращает размер рекомендованной комисии из api
     * @return string
     * @throws Exception
     * @throws UnexpectedValueException
     */
    public function getRecommendedFeeFromApi(): string
    {
        $response = $this->client->get('fees/estimates/latest', ['confidence' => 0.9])->send();
        if (!$response->isOk) {
            throw new UnexpectedValueException('Response is not ok');
        }
        if (!isset($response->data['estimates']['30']['sat_per_vbyte'])) {
            throw new UnexpectedValueException('Response is not ok');
        }
        return (string)$response->data['estimates']['30']['sat_per_vbyte'];
    }

    /**
     * получает текущий мемпул из api
     * @return array [fee => weight(WU)]
     * @throws Exception
     */
    public function getMempoolFromApi(): array
    {
        $response = $this->client->get('mempool/latest')->send();
        if (!$response->isOk) {
            throw new UnexpectedValueException('Response is not ok');
        }
        if (!isset($response->data['mempool'])) {
            throw new UnexpectedValueException('Response is not ok');
        }
        return $response->data['mempool'];
    }


    /**
     * возвращает текущий вес мемпула
     * @return float
     */
    public function getMempoolWeight(): float
    {
        $mempool = $this->getMempool();
        return array_sum($mempool)/(4*1048576);
    }

    /**
     * возвращает массив вида [номер блока => мин комиссия для попадания]
     * @return array [blockNum => minFee]
     */
    public function getBlocksMinFee(): array
    {
        // массив мемпула вида [размер комиссии => вес транзакций с такой комиссией в WU ]
        $mempool = $this->getMempool();
        krsort($mempool);
        $blocksMinFee = [];
        $currentWeight = 0;
        $blockNum = 1;
        // для первого блока может быть уменьшен макс размер блока
        // поэтому храним размер блока в отдельной переменной
        $blockMaxWeight = 4*self::BYTES_PER_MEGABYTE;
        foreach ($mempool as $fee => $weight) {
            $fee = intval($fee);
            if (($currentWeight + $weight)  < $blockMaxWeight) {
                $currentWeight += $weight;
            } else  {
                $currentWeight += $weight;
                while ($currentWeight >= $blockMaxWeight) {
                    // записываем в рез. массив комиссию итерации
                    $blocksMinFee[$blockNum] = $fee;
                    $blockNum++;
                    $currentWeight-=$blockMaxWeight;
                }
            }
        }

        // в случае если после последней итерации в мемпуле еще что то остается
        // записываем в рез. массив с размером комиссии из последнего элемента мемпула
        if ($currentWeight > 0) {
            $blocksMinFee[$blockNum] = intval(array_key_last($mempool));
        }

        return $blocksMinFee;
    }
}