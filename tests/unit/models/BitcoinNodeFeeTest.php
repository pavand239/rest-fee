<?php
declare(strict_types=1);

namespace tests\unit\models;

class BitcoinNodeFeeTest extends AbstractFeeTest
{
    public function _before()
    {
        $this->feeServiceClassName = 'restFee\models\BitcoinNodeFee';
        parent::_before();
    }

    public function testGetRecommendedFee()
    {
        $recommendedFee = $this->feeService->getRecommendedFeeFromApi();
        $this->assertTrue($recommendedFee>0);
    }


}