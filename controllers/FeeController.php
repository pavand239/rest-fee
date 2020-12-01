<?php
declare(strict_types=1);

namespace restFee\controllers;

use restFee\components\Config;
use restFee\models\BitcoinerLiveFee;
use restFee\models\EtherscanFee;
use restFee\models\FeeAbstract;
use yii\base\InvalidConfigException;
use yii\rest\Controller;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class FeeController
 * @package restFee\controllers
 */
class FeeController extends Controller
{

    /** @var FeeAbstract|BitcoinerLiveFee|EtherscanFee */
    public $feeService;
    private $allowedCurrency = ['BTC', 'ETH'];

    /**
     * @param $action
     * @return bool
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function beforeAction($action) {
        /** @var Config $config */
        $config = Yii::$app->get('config');
        $currency = strtoupper(Yii::$app->request->get('currency'));
        if (!in_array($currency, $this->allowedCurrency)) {
            throw new NotFoundHttpException();
        }
        $className = $config->get($currency.'_API_MODEL_CLASSNAME');
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
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        return [
            'blocksMinFee' => $this->feeService->getBlocksMinFee(),
            'mempoolWeight' => $this->feeService->getCurrentMempoolWeight(),
        ]
            + $this->feeService->getRecommendedFee();
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
     * @throws NotFoundHttpException
     */
    public function actionMempoolWeight()
    {
        return $this->feeService->getCurrentMempoolWeight();
    }

    /**
     * возвращает текущую нагрузку сети в процентах
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionLoad()
    {
        return $this->feeService->getCurrentLoad();
    }

    /**
     * возвращает массив типа [номер блока => мин комиссия для попадания]
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionBlocksMinFee()
    {
        return $this->feeService->getBlocksMinFee();
    }
}