<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../run_clustering.php';

class KMeansDistanceTest extends TestCase {

    public function testStandardDistance() {
        $kmeans = new KMeansClustering(2);

        $distance = $kmeans->euclideanDistance(
            ['age'=>0,'income'=>0,'purchase_amount'=>0],
            ['age'=>3,'income'=>4,'purchase_amount'=>0]
        );

        $this->assertEquals(5, $distance);
    }

    public function testIdenticalPoints() {
        $kmeans = new KMeansClustering(2);

        $distance = $kmeans->euclideanDistance(
            ['age'=>10,'income'=>20,'purchase_amount'=>30],
            ['age'=>10,'income'=>20,'purchase_amount'=>30]
        );

        $this->assertEquals(0, $distance);
    }
}
