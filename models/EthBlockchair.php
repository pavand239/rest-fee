<?php
declare(strict_types=1);

namespace restFee\models;

use UnexpectedValueException;
use Yii;
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

    protected string $baseUrl = "https://api.blockchair.com/ethereum/blocks";
    protected string $currency = 'ETH';

    /**
     * данные последних 100 блоков
     * @return array
     */
    public function getBlocksInfo(): array
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
     * @return array
     * @throws Exception
     */
    public function getBlocksInfoFromApi(): array
    {
        $response = $this->client->get('', ['limit'=>100])->send();
        if (!$response->isOk) {
            throw new UnexpectedValueException('Response is not ok');
        }
        if (!isset($response->data['data'])) {
            throw new UnexpectedValueException('Response is not ok');
        }
        return [
            'avgGasUsed' => array_sum(ArrayHelper::getColumn($response->data['data'], 'gas_used'))/100,
            'avgGasLimit' => array_sum(ArrayHelper::getColumn($response->data['data'], 'gas_limit'))/100,
            'avgTotalFee' => array_sum(ArrayHelper::getColumn($response->data['data'], 'fee_total'))/100,
        ];
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
}