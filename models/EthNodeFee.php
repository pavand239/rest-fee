<?php
declare(strict_types=1);

namespace restFee\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\httpclient\Exception;


class EthNodeFee extends FeeAbstract
{
    public function __construct()
    {
        $this->baseUrl = require __DIR__ . '/../keys/ethNodeUrl.php';
        $this->currency = 'ETH';
        parent::__construct();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getRecommendedFeeFromApi(): string
    {
        $requestData = $this->prepareRequestData('eth_gasPrice');
        $gasPrice = $this->sendRequestJsonRPC($requestData,'result');
        return (string)round(hexdec($gasPrice)*21000/pow(10,18), 8);
    }

    /**
     * @return array
     */
    public function getCurrentLoad(): array
    {
        $load = Yii::$app->cache->getOrSet(
            $this->getCacheName('load'),
            fn() => $this->getCurrentLoadFromApi(),
            10
        );
        return ['currentLoad' => $load];
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getCurrentLoadFromApi(): int
    {
        $blocks = $this->getLastBlocks(50);
        ['avgGasLimit' => $avgGasLimit, 'avgGasUsed' => $avgGasUsed] = $this->getAvgGasInfoFromBlocks($blocks);
        return intval(ceil(($avgGasUsed/$avgGasLimit)*100));
    }

    /**
     * @param array $blocks
     * @return float[]|int[]
     */
    private function getAvgGasInfoFromBlocks(array $blocks): array
    {
        $blocksCount = count($blocks);
        $totalGasLimit = array_sum(ArrayHelper::getColumn($blocks, fn($element)=>hexdec($element['gasLimit'])));
        $totalGasUsed = array_sum(ArrayHelper::getColumn($blocks, fn($element)=>hexdec($element['gasUsed'])));
        return [
            'avgGasLimit' => $totalGasLimit/$blocksCount,
            'avgGasUsed' => $totalGasUsed/$blocksCount
        ];
    }

    /**
     * получить информацию о последних n блоках
     * @param int $n количество блоков
     * @return array
     * @throws Exception
     */
    private function getLastBlocks(int $n = 100): array
    {
        $firstBlockNum = $this->getMostRecentBlockNumber();
        $lastBlockNum = $firstBlockNum - $n;
        $blocks = [];
        for ($i = $firstBlockNum; $i > $lastBlockNum; $i--) {
            $blocks[$i] = $this->getBlockByNum($i);
        }
        return $blocks;
    }

    /**
     * получить информацию о блоке по его номеру
     * @param int $n номер блока
     * @return array
     * @throws Exception
     */
    private function getBlockByNum(int $n): array
    {
        $requestData = $this->prepareRequestData(
            'eth_getBlockByNumber',
            [
                '0x'.dechex($n),
                false
            ]
        );
        return $this->sendRequestJsonRPC($requestData, 'result');
    }


    /**
     * получить номер последнего блока
     * @return int
     * @throws Exception
     */
    private function getMostRecentBlockNumber(): int
    {
        $requestData = $this->prepareRequestData('eth_blockNumber');
        return hexdec($this->sendRequestJsonRPC($requestData, 'result'));
    }

}