<?php

require_once __DIR__ . '/../../lib/CustomStats.php';

class CustomStatsTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function _before()
    {

    }

    protected function _after()
    {
    }

    // tests
    public function testGetData()
    {
        $data = [
            'Bob Jones' => [
                'name' => 'Bob Jones',
                'pa' => 150,
                'xwoba' => '0.350',
                'k_percentage' => '25.5'
            ],
            'Dude Yo' => [
                'name' => 'Dude Yo',
                'pa' => 50,
                'xwoba' => '0.350',
                'k_percentage' => '25.5'
            ],
        ];
        $cs = new CustomStats();
        $output = $cs->filterData($data, 100, 1);
        $this->tester->assertCount(1, $output);
        $this->tester->assertEquals('Bob Jones', $output[0]['name']);
    }

    public function testComputeKpercentMinusXwoba()
    {
        $data = [
            'Bob Jones' => [
                'pa' => 150,
                'xwoba' => '0.350',
                'k_percentage' => '25.5'
            ]
        ];
        $cs = new CustomStats();
        $output = $cs->computeKpercentMinusXwoba($data);
        $this->tester->assertEquals(-0.095, $output[0]['value']);
        $this->tester->assertEquals('Bob Jones', $output[0]['name']);
    }

    public function testComputeKpercentMinusAdjustedXwoba()
    {
        $data = [
            'Bob Jones' => [
                'name' => 'Bob Jones',
                'pa' => 123,
                'ip' => '25',
                'g' => 3,
                'k' => 24,
                'k_percentage' => 25.1,
                'kbb_percentage' => 14.3,
                'gs' => 3,
                'velo' => 90.0,
                'opprpa' => 80,
                'xwoba' => .300
            ]
        ];
        $cs = new CustomStats();
        $output = $cs->computeKpercentMinusAdjustedXwoba($data);
        $this->tester->assertEquals(-0.079, $output[0]['value']);
        $this->tester->assertEquals('Bob Jones', $output[0]['name']);
    }

    public function testMergeSourceData()
    {
        $fgData = [
            'Bob Jones' => [
                'name' => 'Bob Jones',
                'k_percentage' => '25.5 %',
                'bb_percentage' => '10.0 %'
            ]
        ];
        $bsData = [
            'Bob Jones' => [
                'name' => 'Bob Jones',
                'pa' => 150,
                'xwoba' => '0.0325'
            ]
        ];
        $cs = new CustomStats();
        $output = $cs->mergeSourceData($fgData, $bsData);
        $this->tester->assertEquals('Bob Jones', $output['Bob Jones']['name']);
    }
}