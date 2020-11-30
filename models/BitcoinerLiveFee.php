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
     * @return float mempool weight
     * @throws Exception
     */
    public function getCurrentMempoolWeight(): float
    {
        $mempool = $this->getMempoolFromApi();
        return array_sum($mempool)/(4*1048576);
    }

    /**
     * @return array [blockNum => minFee]
     * @throws Exception
     */
    public function getBlocksMinFee(): array
    {
        $mempool = $this->getMempoolFromApi();
        krsort($mempool);
        $blocksMinFee = [];
        $currentWeight = 0;
        $prevFee = 0;
        $blockNum = 1;
        $blockMaxWeight = 1;
        foreach ($mempool as $fee => $weight) {
            $weight = $weight/(4*1048576);
            $fee = intval($fee);
            if ($currentWeight + $weight  < $blockMaxWeight) {
                $currentWeight += $weight;
                $prevFee = $fee;
                $blockMaxWeight = 1;
            } else if ($weight>1) {
                $currentWeight += $weight;
                while ($currentWeight >= 1) {
                    $blocksMinFee[$blockNum] = $fee;
                    $blockNum++;
                    $currentWeight--;
                }
                $prevFee = $fee;
            }
            else {
                $currentWeight += $weight;
                $blocksMinFee[$blockNum] = $prevFee;
                $prevFee = $fee;
                $currentWeight--;
                $blockNum++;
            }
        }
        if ($currentWeight > 0) {
            $blocksMinFee[$blockNum] = $prevFee;
        }
        return $blocksMinFee;
    }
}