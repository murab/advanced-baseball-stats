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
                'k_percentage_plus' => 110,
                'kbb_percentage' => 14.3,
                'gs' => 3,
                'velo' => 90.0,
                'opprpa' => 80,
                'oppops' => .850,
                'xwoba' => .300
            ],
            'Tom Seaver' => [
                'name' => 'Tom Seaver',
                'pa' => 123,
                'ip' => '25',
                'g' => 3,
                'k' => 24,
                'k_percentage' => 30.0,
                'k_percentage_plus' => 120,
                'kbb_percentage' => 17.3,
                'gs' => 3,
                'velo' => 95.0,
                'opprpa' => 90,
                'oppops' => .900,
                'xwoba' => .250
            ]
        ];
        $cs = new CustomStats();
        $output = $cs->computeKpercentMinusAdjustedXwoba($data, .850);
        $this->tester->assertEquals(-0.049, $output[1]['value']);
        $this->tester->assertEquals('Tom Seaver', $output[0]['name']);
        $output = $cs->computeKpercentMinusAdjustedXwoba($data, .425);
        $this->tester->assertEquals(.101, $output[1]['value']);
        $this->tester->assertEquals('Tom Seaver', $output[0]['name']);
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
        $bpData = [
            'Bob Jones' => [
                'name' => 'Bob Jones',
                'opprpa' => 110,
                'oppops' => '0.800'
            ]
        ];
        $cs = new CustomStats();
        $output = $cs->mergeSourceData($fgData, $bsData, $bpData);
        $this->tester->assertEquals('Bob Jones', $output['Bob Jones']['name']);
    }
}