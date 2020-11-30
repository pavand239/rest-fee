<?php
declare(strict_types=1);

namespace restFee\controllers;

use restFee\models\BitcoinerLiveFee;
use yii\rest\Controller;

class FeeController extends Controller
{
    public $feeService;
    public function beforeAction($action) {
        $this->feeService = new BitcoinerLiveFee();
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