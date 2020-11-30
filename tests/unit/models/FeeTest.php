<?php

namespace tests\unit\models;

use restFee\components\Config;
use restFee\models\FeeInterface;
use Yii;
use Codeception\Test\Unit;
use yii\base\InvalidConfigException;

/**
 * Class FeeTest
 * @package tests\unit\models
 * @property  FeeInterface $feeService
 */
class FeeTest extends Unit
{
    private $feeService;

    /**
     * @throws InvalidConfigException
     */
    public function _before()
    {
        /** @var Config $config */
        $config = Yii::$app->get('config');
        $className = $config->get('API_MODEL_CLASSNAME');
        $this->feeService = new $className;
    }

    public function testGetRecommendedFee()
    {
        $recommendedFee = $this->feeService->getRecommendedFee();
        $this->assertIsNumeric($recommendedFee['recommendedFee']);
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