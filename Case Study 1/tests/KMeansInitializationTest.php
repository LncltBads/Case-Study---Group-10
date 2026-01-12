<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../run_clustering.php';

class KMeansInitializationTest extends TestCase {

    public function testDeterministicWithSeed() {
        $kmeans = new KMeansClustering(2);

        $data = [
            ['age'=>20,'income'=>30000,'purchase_amount'=>1000],
            ['age'=>30,'income'=>50000,'purchase_amount'=>2000],
            ['age'=>40,'income'=>70000,'purchase_amount'=>3000]
        ];

        $centroids1 = $kmeans->initializeCentroids($data);
        $centroids2 = $kmeans->initializeCentroids($data);

        $this->assertEquals($centroids1, $centroids2);
    }
}
