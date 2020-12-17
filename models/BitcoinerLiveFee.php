<?php

namespace restFee\models;


use UnexpectedValueException;
use Yii;

use yii\base\InvalidConfigException;
use yii\httpclient\Exception;

class BitcoinerLiveFee extends FeeAbstract
{
    public function __construct()
    {
        $this->feeCurrency = 'sat/vB';
        $this->baseUrl = 'https://bitcoiner.live/api';
        $this->currency = 'BTC';
        parent::__construct();
    }

    /**
     * переопределено, т.к. обновление кэша будет реализоваться через консольное приложение по крону
     * @return array
     */
    public function getRecommendedFee(): array
    {
        $fee = Yii::$app->cache->get($this->getCacheName('recommended-fee'));
        if ($fee === false) {
            throw new UnexpectedValueException('Value from cache expired');
        }
        return ['recommendedFee'=>
            [
                "value" => $fee,
                "currency" => $this->feeCurrency
            ]
        ];
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
     * @return string
     */
    public function getRecommendedFeeFromApi(): string
    {
        return (string)$this->getBlocksMinFee()[1];
    }

    /**
     * возвращает текущий вес мемпула
     * @return float
     */
    private function getMempoolWeight(): float
    {
        $mempool = $this->getMempool();
        return array_sum($mempool)/(4*1048576);
    }

    /**
     * возвращает массив вида [номер блока => мин комиссия для попадания]
     * @return array [blockNum => minFee]
     */
    private function getBlocksMinFee(): array
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

    /**
     * возращает мемпул с кэшированием
     * @return array
     */
    private function getMempool(): array
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
     * получает текущий мемпул из api
     * @return array [fee => weight(WU)]
     * @throws Exception
     * @throws InvalidConfigException
     */
    private function getMempoolFromApi(): array
    {
        return $this->sendRequestSimple('GET','mempool/latest', 'mempool');
    }
}