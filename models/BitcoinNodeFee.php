<?php
declare(strict_types=1);

namespace restFee\models;

use UnexpectedValueException;
use yii\httpclient\Client;
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
     * @return string
     * @throws Exception
     */
    public function getRecommendedFeeFromApi(): string
    {
        $requestData = json_encode(
            [
                "jsonrpc" => "2.0",
                "method" => "estimatesmartfee",
                "params" => [1]
            ]
        );
        $response = $this->client->post('',$requestData, ['content-type' => 'application/json'])->setFormat(Client::FORMAT_JSON)->send();
        if (!$response->isOk) {
            throw new UnexpectedValueException('Response is not ok');
        }
        if (!isset($response->data['result']['feerate'])) {
            throw new UnexpectedValueException('Response is not ok');
        }
        $feeBtcPerKB = $response->data['result']['feerate'];
        return (string)intval(($feeBtcPerKB*(10**8))/1024);
    }

    /**
     * @return float
     * @throws Exception
     */
    public function getMempoolWeightFromApi(): float
    {
        $requestData = json_encode(
            [
                "jsonrpc" => "2.0",
                "method" => "getmempoolinfo",
                "params" => []
            ]
        );
        $response = $this->client->post('',$requestData, ['content-type' => 'application/json'])->setFormat(Client::FORMAT_JSON)->send();
        if (!$response->isOk) {
            throw new UnexpectedValueException('Response is not ok');
        }
        if (!isset($response->data['result'])) {
            throw new UnexpectedValueException('Response is not ok');
        }
        return (float)$response->data['result']['usage']/self::BYTES_PER_MEGABYTE;

    }
}