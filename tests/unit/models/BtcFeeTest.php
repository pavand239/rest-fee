<?php
declare(strict_types=1);

namespace tests\unit\models;

use yii\web\NotFoundHttpException;

class BtcFeeTest extends AbstractFeeTest {
    protected $currency = 'BTC';

    /**
     * @throws NotFoundHttpException
     */
    public function testGetMempool()
    {
        $mempool = $this->feeService->getMempool();
        $this->assertIsArray($mempool);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function testGetMempoolFromApi()
    {
        $mempool = $this->feeService->getMempoolFromApi();
        $this->assertIsArray($mempool);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function testGetMempoolWeight()
    {
        $weight = $this->feeService->getMempoolWeight();
        $this->assertIsNumeric($weight);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function testGetBlocksMinFee()
    {
        $blocks = $this->feeService->getBlocksMinFee();
        $this->assertIsArray($blocks);
    }
}