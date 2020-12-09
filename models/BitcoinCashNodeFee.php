<?php
declare(strict_types=1);

namespace restFee\models;

use UnexpectedValueException;
use yii\httpclient\Client;
use Yii;
use yii\httpclient\Exception;
use yii\httpclient\Response;

class BitcoinCashNodeFee extends FeeAbstract
{
    public function __construct()
    {
        $this->currency='BCH';
        $this->baseUrl = require __DIR__ . '/../keys/bitcoinCashNodeUrl.php';
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
        $load = intval(ceil($weight/50));
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
                "method" => "estimatefee",
                "params" => []
            ]
        );
        $response = $this->sendRequest($requestData);
        if (!$response->isOk) {
            throw new UnexpectedValueException('Response is not ok');
        }
        if (!isset($response->data['result']) || isset($response->data['error'])) {
            throw new UnexpectedValueException('Response is not ok');
        }
        $feeBchPerKB = $response->data['result'];
        return (string)intval(($feeBchPerKB*(10**8))/1000);
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
        $response = $this->sendRequest($requestData);
        if (!$response->isOk) {
            throw new UnexpectedValueException('Response is not ok');
        }
        if (!isset($response->data['result'])) {
            throw new UnexpectedValueException('Response is not ok');
        }
        return (float)$response->data['result']['usage']/self::BYTES_PER_MEGABYTE;

    }

    /**
     * @param string $requestData json-rpc params
     * @return Response
     * @throws Exception
     */
    public function sendRequest(string $requestData): Response
    {
        return $this->client->post('',$requestData, ['content-type' => 'application/json'])->setFormat(Client::FORMAT_JSON)->send();
    }
}