<?php
declare(strict_types=1);

namespace restFee\models;

use UnexpectedValueException;
use Yii;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
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
        $gasPrice = $this->sendRequest($requestData,'result');
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
            60
        );
        return ['currentLoad' => $load];
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getCurrentLoadFromApi(): int
    {
        $blocks = $this->getLastBlocks(100);
        ['avgGasLimit' => $avgGasLimit, 'avgGasUsed' => $avgGasUsed] = $this->getAvgGasInfoFromBlocks($blocks);
        return intval(ceil(($avgGasUsed/$avgGasLimit)*100));
    }

    /**
     * @param array $blocks
     * @return float[]|int[]
     */
    protected function getAvgGasInfoFromBlocks(array $blocks): array
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
    protected function getLastBlocks(int $n = 100): array
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
    protected function getBlockByNum(int $n): array
    {
        $requestData = $this->prepareRequestData(
            'eth_getBlockByNumber',
            [
                '0x'.dechex($n),
                false
            ]
        );
        return $this->sendRequest($requestData, 'result');
    }


    /**
     * получить номер последнего блока
     * @return int
     * @throws Exception
     */
    protected function getMostRecentBlockNumber(): int
    {
        $requestData = $this->prepareRequestData('eth_blockNumber');
        return hexdec($this->sendRequest($requestData, 'result'));
    }

    /**
     * @param string $requestData json-rpc params
     * @param string $key key for ArrayHelper::getValue
     * @return mixed
     * @throws Exception
     * @throws \Exception
     */
    protected function sendRequest(string $requestData, string $key)
    {
        $response =  $this->client->post('',$requestData, ['content-type' => 'application/json'])->setFormat(Client::FORMAT_JSON)->send();
        if (!$response->isOk) {
            throw new UnexpectedValueException('Response is not ok');
        }
        if (!ArrayHelper::getValue($response->data, $key)) {
            throw new UnexpectedValueException('Response is not ok');
        }
        return ArrayHelper::getValue($response->data, $key);
    }

    /**
     * подготовить json для json-rpc запроса
     * @param string $method
     * @param array $params
     * @return string
     */
    protected function prepareRequestData(string $method, array $params = []): string
    {
        return json_encode(
            [
                "jsonrpc" => "2.0",
                "method" => $method,
                "params" => $params,
                "id" => 1
            ]
        );
    }
}