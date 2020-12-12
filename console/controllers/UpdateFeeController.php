<?php
declare(strict_types=1);

namespace restFee\console\controllers;

use restFee\components\Config;
use restFee\models\BitcoinerLiveFee;
use restFee\models\BitcoinNodeFee;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use Yii;
use yii\httpclient\Exception;

class UpdateFeeController extends Controller
{
    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionIndex()
    {
        /** @var Config $config */
        $config = Yii::$app->get('config');
        $className = $config->get('BTC_API_MODEL_CLASSNAME');
        /** @var BitcoinNodeFee|BitcoinerLiveFee $btcModel */
        $btcModel = new $className;
        $recommendedFee = $btcModel->getRecommendedFeeFromApi();
        Yii::$app->cache->set(
            $btcModel->getCacheName('recommended-fee'),
            $recommendedFee,
            120
        );
    }
}