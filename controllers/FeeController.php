<?php
declare(strict_types=1);

namespace restFee\controllers;

use yii\rest\Controller;
use Yii;

class FeeController extends Controller
{
    public $feeService;

    public function beforeAction($action) {
        $modelClassName = Yii::$app->config->get('API_MODEL_CLASSNAME');
        $this->feeService = new $modelClassName;
        return parent::beforeAction($action);
    }
    public function actionIndex() {
        return [
            $this->feeService->getBlocksMinFee(),
            $this->feeService->getRecommendedFeeFromApi(),
            $this->feeService->getCurrentMempoolWeight()
        ];
    }
    public function actionRecommended() {
        return $this->feeService->getRecommendedFee();
    }
    public function actionMempoolWeight() {
        return $this->feeService->getCurrentMempoolWeight();
    }
    public function actionLoad() {
        return $this->feeService->getCurrentLoad();
    }
    public function actionBlocksMinFee() {
        return $this->feeService->getBlocksMinFee();
    }
}