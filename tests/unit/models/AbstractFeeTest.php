<?php

namespace tests\unit\models;

use restFee\models\BitcoinCashNodeFee;
use restFee\models\BitcoinNodeFee;
use restFee\models\EthNodeFee;
use restFee\models\FeeAbstract;
use Codeception\Test\Unit;
use yii\web\NotFoundHttpException;

/**
 * Class AbstractFeeTest
 * @package tests\unit\models
 * @property  FeeAbstract $feeService
 */
abstract class AbstractFeeTest extends Unit
{
    /** @var BitcoinNodeFee|EthNodeFee|BitcoinCashNodeFee */
    protected $feeService;
    protected string $feeServiceClassName;

    public function _before()
    {
        $this->feeService = new $this->feeServiceClassName;
    }


    public function testGetRecommendedFee()
    {
        $recommendedFee = $this->feeService->getRecommendedFee();
        $this->assertTrue(floatval($recommendedFee['recommendedFee'])>0);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function testGetCurrentLoad()
    {
        $currentLoad = $this->feeService->getCurrentLoad();
        $this->assertTrue(0<=$currentLoad['currentLoad'] && $currentLoad['currentLoad'] <= 100);
    }
}