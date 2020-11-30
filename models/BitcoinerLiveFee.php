<?php

namespace restFee\models;


use UnexpectedValueException;
use Yii;
use yii\httpclient\Client;
use yii\httpclient\Exception;

class BitcoinerLiveFee implements FeeInterface
{
    private const BASE_URL = 'https://bitcoiner.live/api';
    public $client;

    public function __construct()
    {
        $this->client = new Client(['baseUrl' => self::BASE_URL]);
    }
    /**
     * возвращает размер рекомендованной комиссии с кэшированием
     * @return array ['recommendedFee' => int]
     */
    public function getRecommendedFee(): array
    {
        $fee = Yii::$app->cache->getOrSet('recommendedFee', function () {
            return $this->getRecommendedFeeFromApi();
        }, 60);
        return ['recommendedFee'=>$fee];
    }

    /**
     * возвращает нагрузку сети с кэшированием
     * @return array ['currentLoad' => string]
     */
    public function getCurrentLoad(): array
    {
        $load = Yii::$app->cache->getOrSet('currentLoad', function () {
            $weight = $this->getCurrentMempoolWeight();
            $load = round($weight);
            if ($load>100) {
                return 100;
            }
            return $load;
        }, 60);
        return ['currentLoad'=>$load];
    }

    /**
     * возвращает размер рекомендованной комисии из api
     * @return int
     * @throws Exception
     * @throws UnexpectedValueException
     */
    public function getRecommendedFeeFromApi(): int
    {
        $response = $this->client->get('fees/estimates/latest', ['confidence' => 0.9])->send();
        if (!$response->isOk) {
            throw new UnexpectedValueException('Response is not ok');
        }
        if (!isset($response->data['estimates']['30']['sat_per_vbyte'])) {
            throw new UnexpectedValueException('Response is not ok');
        }
        return $response->data['estimates']['30']['sat_per_vbyte'];
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
     * @return float mempool weight
     * @throws Exception
     */
    public function getCurrentMempoolWeight(): float
    {
        $mempool = $this->getMempoolFromApi();
        return array_sum($mempool)/(4*1048576);
    }

    /**
     * возвращает массив вида [номер блока => мин комиссия для попадания]
     * @return array [blockNum => minFee]
     * @throws Exception
     */
    public function getBlocksMinFee(): array
    {
        // массив мемпула вида [размер комиссии => вес транзакций с такой комиссией в WU ]
        $mempool = $this->getMempoolFromApi();
        krsort($mempool);
        $blocksMinFee = [];
        $currentWeight = 0;
        $blockNum = 1;
        // для первого блока может быть уменьшен макс размер блока
        // поэтому храним размер блока в отдельной переменной
        $blockMaxWeight = 1;
        foreach ($mempool as $fee => $weight) {
            // преобразуем вес в мегабайты
            $weight = $weight/(4*1048576);
            $fee = intval($fee);
            if ($currentWeight + $weight  < $blockMaxWeight) {
                // если на очередной итерации не набираем нужный для формирования блока вес
                // добавляем вес итерации и переписываем размер комисии
                $currentWeight += $weight;
            } else  {
                // если набираем
                $currentWeight += $weight;
                while ($currentWeight >= $blockMaxWeight) {
                    // записываем в рез. массив комиссию итерации
                    $blocksMinFee[$blockNum] = $fee;
                    $blockNum++;
                    // после первой записи переписываем максимальный размер блока
                    $blockMaxWeight = 1;
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