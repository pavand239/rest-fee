<?php

namespace tests\unit\models;

use Yii;
use Codeception\Test\Unit;

/**
 * Class FeeTest
 * @package tests\unit\models
 * @property  \restFee\models\FeeInterface $feeService
 */
class FeeTest extends Unit
{
    private $feeService;

    public function _before()
    {
        $modelClassName = Yii::$app->config->get('API_MODEL_CLASSNAME');
        $this->feeService = new $modelClassName;
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