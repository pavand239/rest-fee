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
        return (string)intval(($feeBtcPerKB*(10**8))/1000);
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getCurrentLoadFromApi(): int
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

        $usage = (float)$response->data['result']['usage'];
        $maxmempool = (float)$response->data['result']['maxmempool'];
        return intval(ceil($usage/$maxmempool*100));
    }
}