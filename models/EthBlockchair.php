<?php
declare(strict_types=1);

namespace restFee\models;


use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Exception;

/**
 * Class EthBlockchair
 * @package restFee\models
 * Все данные рассчитываются на основе данных о 100 последних блоках из api.
 * Расчет рекомендуемой комиссии:
 * ср. сумма комиссии в блоке/ср. количество использованного газа получаем ср. цену газа
 * далее ср. цена газа умножается на 21000 - стд. лимит газа (Рома сказал захардкодить его)
 * далее переводим получившееся число из wei в ETH
 * Нагрузку сети рассчитываем по формуле ср. кол-во использованного газа/ср. лимит газа блока
 */
class EthBlockchair extends FeeAbstract {

    public function __construct()
    {
        $this->baseUrl = "https://api.blockchair.com/ethereum/blocks";
        $this->currency = 'ETH';
        parent::__construct();
    }

    /**
     * ср. сумма комиссии в блоке/ср. количество использованного газа получаем ср. цену газа
     * далее ср. цена газа умножается на 21000 - стд. лимит газа (Рома сказал захардкодить его)
     * дадее переводим получившееся число из wei в ETH
     * @return string
     */
    public function getRecommendedFeeFromApi(): string
    {
        $blocksInfo = $this->getBlocksInfo();
        $gasUsed = $blocksInfo['avgGasUsed'];
        $totalFee = intval($blocksInfo['avgTotalFee']);
        return (string)round((($totalFee/$gasUsed)*21000)/1000000000000000000, 8);
    }

    /**
     * рассчитываем по формуле ср. кол-во использованного газа/ср. лимит газа блока
     * далее переводим в проценты
     * @return int[]
     */
    public function getCurrentLoad(): array
    {
        $blocksInfo = $this->getBlocksInfo();
        $avgGasUsed = $blocksInfo['avgGasUsed'];
        $avgGasLimit = $blocksInfo['avgGasLimit'];
        $load = intval(ceil(($avgGasUsed/$avgGasLimit)*100));
        return ['currentLoad' => $load];
    }

    /**
     * данные последних 100 блоков
     * @return array
     */
    private function getBlocksInfo(): array
    {
        return Yii::$app->cache->getOrSet(
            $this->getCacheName('blocks'),
            function () {
                return $this->getBlocksInfoFromApi();
            },
            60
        );
    }

    /**
     * получаем данные последних 100 сгенерированных блоков из апи
     * считаем средние значения totalFee, gasUsed, gasLimit
     * остальные данные отбрасываем
     * @return float[]|int[]
     * @throws Exception
     * @throws InvalidConfigException
     */
    private function getBlocksInfoFromApi(): array
    {
        $data = $this->sendRequestSimple('GET','','data', ['limit'=>100]);
        return [
            'avgGasUsed' => array_sum(ArrayHelper::getColumn($data, 'gas_used'))/100,
            'avgGasLimit' => array_sum(ArrayHelper::getColumn($data, 'gas_limit'))/100,
            'avgTotalFee' => array_sum(ArrayHelper::getColumn($data, 'fee_total'))/100,
        ];
    }
}