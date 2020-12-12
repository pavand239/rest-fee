<?php
declare(strict_types=1);

namespace tests\unit\models;

class BitcoinerLiveFeeTest extends AbstractFeeTest
{
    public function _before()
    {
        $this->feeServiceClassName = 'restFee\models\BitcoinerLiveFee';
        parent::_before();
    }
    public function testGetRecommendedFee()
    {
        $recommendedFee = $this->feeService->getRecommendedFeeFromApi();
        $this->assertTrue($recommendedFee>0);
    }
}