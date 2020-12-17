<?php
declare(strict_types=1);

namespace restFee\models;

use Yii;
use yii\httpclient\Exception;

class BitcoinCashNodeFee extends FeeAbstract
{
    public const FEE_CURRENCY = 'sat/B';

    public function __construct()
    {
        $this->currency='BCH';
        $this->baseUrl = require __DIR__ . '/../keys/bitcoinCashNodeUrl.php';
        parent::__construct();
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
        $requestData = $this->prepareRequestData('estimatefee');
        $feeBchPerKB = $this->sendRequestJsonRPC($requestData, 'result');
        return (string)intval(($feeBchPerKB*(10**8))/1000);
    }

    /**
     * @return float
     */
    private function getMempoolWeight(): float
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
     * @return float
     * @throws Exception
     */
    private function getMempoolWeightFromApi(): float
    {
        $requestData = $this->prepareRequestData('getmempoolinfo');
        $usage= $this->sendRequestJsonRPC($requestData, 'result.usage');
        return (float)$usage/self::BYTES_PER_MEGABYTE;
    }
}