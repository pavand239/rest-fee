<?php
declare(strict_types=1);

namespace restFee\models;

use UnexpectedValueException;
use yii\httpclient\Exception;
use yii\web\NotFoundHttpException;

class EtherscanFee extends FeeAbstract {

    protected $baseUrl = "https://api.etherscan.io/api";
    protected $apiKey;
    protected $currency = 'ETH';

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = require __DIR__."/../keys/etherscanApiKey.php";
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function getCurrentLoad(): array
    {
        // TODO: Implement getCurrentLoad() method.
        throw new NotFoundHttpException('Method unavailable for this currency');
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function getMempool(): array
    {
        // TODO: Implement getMempool() method.
        throw new NotFoundHttpException('Method unavailable for this currency');
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getRecommendedFeeFromApi(): int
    {
        $module = 'gastracker';
        $action = 'gasoracle';
        $apikey = $this->apiKey;
        $response = $this->client->get('', compact('module', 'action','apikey'))->send();
        if (!$response->isOk) {
            throw new UnexpectedValueException('Response is not ok');
        }
        if (!isset($response->data['result']['FastGasPrice'])) {
            throw new UnexpectedValueException('Response is not ok');
        }
        return intval($response->data['result']['FastGasPrice']);
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function getMempoolFromApi(): array
    {
        // TODO: Implement getMempoolFromApi() method.
        throw new NotFoundHttpException('Method unavailable for this currency');
    }

    /**
     * @return float
     * @throws NotFoundHttpException
     */
    public function getCurrentMempoolWeight(): float
    {
        // TODO: Implement getCurrentMempoolWeight() method.
        throw new NotFoundHttpException('Method unavailable for this currency');
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function getBlocksMinFee(): array
    {
        // TODO: Implement getBlocksMinFee() method.
        throw new NotFoundHttpException('Method unavailable for this currency');
    }
}