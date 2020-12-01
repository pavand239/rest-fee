<?php

namespace tests\unit\models;

use restFee\components\Config;
use restFee\models\FeeAbstract;
use Yii;
use Codeception\Test\Unit;
use yii\base\InvalidConfigException;

/**
 * Class AbstractFeeTest
 * @package tests\unit\models
 * @property  FeeAbstract $feeService
 */
abstract class AbstractFeeTest extends Unit
{
    protected const PARAM_NAME = '_API_MODEL_CLASSNAME';
    protected $feeService;
    protected $currency;

    /**
     * @throws InvalidConfigException
     */
    public function _before()
    {
        /** @var Config $config */
        $config = Yii::$app->get('config');
        $className = $config->get($this->currency.static::PARAM_NAME);
        $this->feeService = new $className;
    }

    public function testGetRecommendedFee()
    {
        $recommendedFee = $this->feeService->getRecommendedFee();
        $this->assertIsNumeric($recommendedFee['recommendedFee']);
        echo print_r($recommendedFee);
        codecept_debug($recommendedFee);
    }
    public function testGetCurrentLoad()
    {
        $currentLoad = $this->feeService->getCurrentLoad();
        $this->assertIsNumeric($currentLoad['currentLoad']);
    }

    public function testGetMempool()
    {
        $mempool = $this->feeService->getMempool();
        $this->assertIsArray($mempool);
    }

    public function testGetRecommendedFeeFromApi()
    {
        $recommendedFee = $this->feeService->getRecommendedFeeFromApi();
        $this->assertIsNumeric($recommendedFee);
        echo print_r($recommendedFee);
        codecept_debug($recommendedFee);
    }

    public function testGetMempoolFromApi()
    {
        $mempool = $this->feeService->getMempoolFromApi();
        $this->assertIsArray($mempool);
    }

    public function testGetCurrentMempoolWeight()
    {
        $weight = $this->feeService->getCurrentMempoolWeight();
        $this->assertIsNumeric($weight);
    }

    public function testGetBlocksMinFee()
    {
        $blocks = $this->feeService->getBlocksMinFee();
        $this->assertIsArray($blocks);
    }
}