<?php
declare(strict_types=1);

namespace restFee\controllers;

use restFee\components\Config;
use restFee\models\BitcoinerLiveFee;
use restFee\models\FeeInterface;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use yii\rest\Controller;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * Class FeeController
 * @package restFee\controllers
 */
class FeeController extends Controller
{

    /** @var FeeInterface|BitcoinerLiveFee */
    public $feeService;

    /**
     * @param $action
     * @return bool
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function beforeAction($action) {
        /** @var Config $config */
        $config = Yii::$app->get('config');
        $className = $config->get('API_MODEL_CLASSNAME');
        $this->feeService = new $className;
        return parent::beforeAction($action);
    }

    /**
     * возвращает массив типа [
     * [номер блока => мин комиссия для попадания],
     * вес мемпула,
     * рекомендованная комиссия
     * ]
     * @return array
     * @throws Exception
     */
    public function actionIndex()
    {
        return [
            'blocksMinFee' => $this->feeService->getBlocksMinFee(),
            'mempoolWeight' => $this->feeService->getCurrentMempoolWeight(),
        ] + $this->feeService->getRecommendedFee();
    }

    /**
     * возвращает размер рекомендованной комиссии
     * @return array
     */
    public function actionRecommended()
    {
        return $this->feeService->getRecommendedFee();
    }

    /**
     * возвращает текущий вес мемпула
     * @return float
     * @throws Exception
     */
    public function actionMempoolWeight()
    {
        return $this->feeService->getCurrentMempoolWeight();
    }

    /**
     * возвращает текущую нагрузку сети в процентах
     * @return array
     */
    public function actionLoad()
    {
        return $this->feeService->getCurrentLoad();
    }

    /**
     * возвращает массив типа [номер блока => мин комиссия для попадания]
     * @return array
     * @throws Exception
     */
    public function actionBlocksMinFee()
    {
        return $this->feeService->getBlocksMinFee();
    }
}