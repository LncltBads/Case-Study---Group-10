<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../run_clustering.php';

class KMeansNormalizeDataTest extends TestCase {

    public function testNormalData() {
        $kmeans = new KMeansClustering(2);

        $data = [
            ['age'=>20,'income'=>30000,'purchase_amount'=>1000],
            ['age'=>30,'income'=>50000,'purchase_amount'=>2000],
            ['age'=>40,'income'=>70000,'purchase_amount'=>3000]
        ];

        $result = $kmeans->normalizeData($data);

        $this->assertCount(3, $result);
        $this->assertIsNumeric($result[0]['age']);
    }

    public function testZeroStandardDeviation() {
        $kmeans = new KMeansClustering(2);

        $data = [
            ['age'=>25,'income'=>40000,'purchase_amount'=>1500],
            ['age'=>25,'income'=>40000,'purchase_amount'=>1500]
        ];

        $result = $kmeans->normalizeData($data);

        $this->assertEquals(0, $result[0]['age']);
        $this->assertEquals(0, $result[1]['income']);
    }

    public function testNegativeValues() {
        $kmeans = new KMeansClustering(2);

        $data = [
            ['age'=>-10,'income'=>-5000,'purchase_amount'=>-200],
            ['age'=>0,'income'=>0,'purchase_amount'=>0]
        ];

        $result = $kmeans->normalizeData($data);

        $this->assertIsNumeric($result[0]['age']);
        $this->assertIsNumeric($result[1]['income']);
    }

    public function testEmptyArray() {
        $kmeans = new KMeansClustering(2);

        $result = $kmeans->normalizeData([]);

        $this->assertEmpty($result);
    }
}
