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
        $this->assertIsString($recommendedFee['recommendedFee']);
        echo print_r($recommendedFee);
        codecept_debug($recommendedFee);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function testGetCurrentLoad()
    {
        $currentLoad = $this->feeService->getCurrentLoad();
        $this->assertIsInt($currentLoad['currentLoad']);
    }

    public function testGetRecommendedFeeFromApi()
    {
        $recommendedFee = $this->feeService->getRecommendedFeeFromApi();
        $this->assertIsString($recommendedFee);
        echo print_r($recommendedFee);
        codecept_debug($recommendedFee);
    }


}