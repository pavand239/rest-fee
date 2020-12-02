<?php

namespace tests\unit\models;

use restFee\components\Config;
use restFee\models\FeeAbstract;
use Yii;
use Codeception\Test\Unit;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

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

    /**
     * @throws NotFoundHttpException
     */
    public function testGetCurrentLoad()
    {
        $currentLoad = $this->feeService->getCurrentLoad();
        $this->assertIsNumeric($currentLoad['currentLoad']);
    }

    /**
     * @throws NotFoundHttpException
     */
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

    /**
     * @throws NotFoundHttpException
     */
    public function testGetMempoolFromApi()
    {
        $mempool = $this->feeService->getMempoolFromApi();
        $this->assertIsArray($mempool);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function testGetMempoolWeight()
    {
        $weight = $this->feeService->getMempoolWeight();
        $this->assertIsNumeric($weight);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function testGetBlocksMinFee()
    {
        $blocks = $this->feeService->getBlocksMinFee();
        $this->assertIsArray($blocks);
    }
}