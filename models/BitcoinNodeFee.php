<?php
declare(strict_types=1);

namespace restFee\models;

use UnexpectedValueException;
use Yii;
use yii\httpclient\Exception;

class BitcoinNodeFee extends FeeAbstract
{
    public function __construct()
    {
        $this->currency='BTC';
        $this->baseUrl = require __DIR__ . '/../keys/bitcoinNodeUrl.php';
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
        return ['recommendedFee'=>$fee];
    }

    /**
     * @return int[]
     */
    public function getCurrentLoad(): array
    {
        $load = Yii::$app->cache->getOrSet(
          $this->getCacheName('load'),
          fn() => $this->getCurrentLoadFromApi(),
          60
        );
        return ['currentLoad' => $load];
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getRecommendedFeeFromApi(): string
    {
        return (string)$this->getBlocksMinFee()[1];
    }

    /**
     * @return string
     * @throws Exception
     * @deprecated
     */
    public function getRecommendedFeeFromApi_deprecated(): string
    {
        $requestData = $this->prepareRequestData('estimatesmartfee',[1]);
        $feeBtcPerKB = $this->sendRequestJsonRPC($requestData, 'result.feerate');
        return (string)intval(($feeBtcPerKB*(10**8))/1000);
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getCurrentLoadFromApi(): int
    {
        $requestData = $this->prepareRequestData('getmempoolinfo');
        $result = $this->sendRequestJsonRPC($requestData, 'result');
        $usage = (float)$result['usage'];
        $maxmempool = (float)$result['maxmempool'];
        return intval(ceil($usage/$maxmempool*100));
    }

    /**
     * возвращает массив вида [номер блока => мин комиссия для попадания]
     * @return array
     * @throws Exception
     */
    public function getBlocksMinFee(): array
    {
        $mempool = $this->getMempoolWeightDistribution();
        $blocksMinFee = [];
        $currentWeight = 0;
        $blockNum = 1;
        // для первого блока может быть уменьшен макс размер блока
        // поэтому храним размер блока в отдельной переменной
        $blockMaxWeight = 0.8*self::BYTES_PER_MEGABYTE;
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
                    $blockMaxWeight = self::BYTES_PER_MEGABYTE;
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
     * распределяем вес сырого мемпула по размеру комиссии за байт
     * @return array [fee => weight(vbytes)]
     * @throws Exception
     */
    protected function getMempoolWeightDistribution(): array
    {
        // todo-andrey обсудить передавать мемпул как аргумент функции или получать внутри функции напрямую
        $rawMempool = $this->getRawMempool();
        $distributedMempool = [];
        foreach ($rawMempool as $transaction) {
            $feeInSatoshiPerByte = round(($transaction['fee']/$transaction['vsize'])*100000000);
            $distributedMempool[$feeInSatoshiPerByte] = isset($distributedMempool[$feeInSatoshiPerByte])?$distributedMempool[$feeInSatoshiPerByte]+$transaction['vsize']:$transaction['vsize'];
        }
        krsort($distributedMempool);
        return $distributedMempool;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function getRawMempool(): array
    {
        $requestData = $this->prepareRequestData('getrawmempool', [true]);
        return $this->sendRequestJsonRPC($requestData, 'result');
    }
}