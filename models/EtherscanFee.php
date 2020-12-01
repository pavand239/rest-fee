<?php
declare(strict_types=1);

namespace restFee\models;

use UnexpectedValueException;
use yii\httpclient\Exception;

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

}