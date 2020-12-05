<?php
declare(strict_types=1);

namespace restFee\controllers;

use restFee\components\Config;
use restFee\models\BchBlockchair;
use restFee\models\BitcoinerLiveFee;
use restFee\models\BitcoinNodeFee;
use restFee\models\EthBlockchair;
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

    /** @var FeeAbstract|BitcoinerLiveFee|EthBlockchair|BchBlockchair|BitcoinNodeFee */
    public $feeService;
    private array $allowedCurrency = ['BTC', 'ETH', 'BCH'];

    /**
     * @param $action
     * @return bool
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function beforeAction($action): bool
    {
        /** @var Config $config */
        $config = Yii::$app->get('config');
        $currency = strtoupper(Yii::$app->request->get('currency'));
        if (!in_array($currency, $this->allowedCurrency)) {
            throw new NotFoundHttpException('Unknown currency code');
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
    public function actionIndex(): array
    {
        return $this->feeService->getRecommendedFee()
            + $this->feeService->getCurrentLoad();
    }

    /**
     * возвращает размер рекомендованной комиссии
     * @return array
     */
    public function actionRecommended(): array
    {
        return $this->feeService->getRecommendedFee();
    }

    /**
     * возвращает текущий вес мемпула
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionMempoolWeight(): array
    {
        return ['mempoolWeight' => $this->feeService->getMempoolWeight()];
    }

    /**
     * возвращает текущую нагрузку сети в процентах
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionLoad(): array
    {
        return ['currentLoad' => $this->feeService->getCurrentLoad()];
    }

    /**
     * возвращает массив типа [номер блока => мин комиссия для попадания]
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionBlocksMinFee(): array
    {
        return $this->feeService->getBlocksMinFee();
    }
}