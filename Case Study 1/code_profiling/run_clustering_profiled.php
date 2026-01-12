<?php
/**
 * Profiled K-Means Customer Clustering Script
 * This version includes detailed performance profiling
 * Usage: php run_clustering_profiled.php [num_clusters]
 */

set_time_limit(0);
ini_set('memory_limit', '256M');

require_once 'db.php';

define('DEFAULT_CLUSTERS', 5);
define('MAX_ITERATIONS', 300);
define('CONVERGENCE_THRESHOLD', 0.0001);
define('RANDOM_SEED', 42);

// ============================================================================
// Profiler Class
// ============================================================================

class Profiler {
    private $timers = [];
    private $callCounts = [];

    public function start($label) {
        if (!isset($this->timers[$label])) {
            $this->timers[$label] = [
                'total_time' => 0,
                'memory_used' => 0,
                'calls' => 0
            ];
        }
        
        $this->timers[$label]['_temp_start'] = microtime(true);
        $this->timers[$label]['_temp_mem'] = memory_get_usage();
    }

    public function stop($label) {
        if (!isset($this->timers[$label]['_temp_start'])) {
            return;
        }

        $duration = microtime(true) - $this->timers[$label]['_temp_start'];
        $memUsed = memory_get_usage() - $this->timers[$label]['_temp_mem'];
        
        $this->timers[$label]['total_time'] += $duration;
        $this->timers[$label]['memory_used'] += $memUsed;
        $this->timers[$label]['calls']++;
        
        unset($this->timers[$label]['_temp_start']);
        unset($this->timers[$label]['_temp_mem']);
    }

    public function report() {
        // Sort by total time (descending)
        uasort($this->timers, function($a, $b) {
            return $b['total_time'] <=> $a['total_time'];
        });

        echo "\n" . str_repeat("=", 90) . "\n";
        echo "PERFORMANCE PROFILE - CLUSTERING SCRIPT\n";
        echo str_repeat("=", 90) . "\n";
        printf("%-40s %12s %10s %12s %12s\n", 
            "Function/Operation", "Total (s)", "Calls", "Avg (ms)", "Memory (MB)");
        echo str_repeat("-", 90) . "\n";

        $grandTotal = 0;
        foreach ($this->timers as $label => $data) {
            $avgTime = $data['calls'] > 0 ? ($data['total_time'] / $data['calls']) * 1000 : 0;
            printf("%-40s %12.4f %10d %12.2f %12.2f\n", 
                substr($label, 0, 40),
                $data['total_time'],
                $data['calls'],
                $avgTime,
                $data['memory_used'] / 1024 / 1024
            );
            $grandTotal += $data['total_time'];
        }
        
        echo str_repeat("-", 90) . "\n";
        printf("%-40s %12.4f\n", "TOTAL TIME", $grandTotal);
        echo str_repeat("=", 90) . "\n\n";
        
        // Performance insights
        echo "PERFORMANCE INSIGHTS:\n";
        echo str_repeat("-", 90) . "\n";
        $topFunction = array_key_first($this->timers);
        $topTime = $this->timers[$topFunction]['total_time'];
        $percentage = ($topTime / $grandTotal) * 100;
        echo "🔴 Bottleneck: '$topFunction' takes " . number_format($percentage, 1) . "% of total time\n";
        
        // Find most called function
        $maxCalls = max(array_column($this->timers, 'calls'));
        foreach ($this->timers as $label => $data) {
            if ($data['calls'] === $maxCalls) {
                echo "🔁 Most called: '$label' executed {$data['calls']} times\n";
                break;
            }
        }
        
        echo "\n";
    }
}

// ============================================================================
// Profiled K-Means Algorithm
// ============================================================================

class KMeansClusteringProfiled {
    private $k;
    private $maxIterations;
    private $convergenceThreshold;
    private $centroids = [];
    private $clusters = [];
    private $profiler;

    public function __construct($k, $maxIter, $threshold, $profiler) {
        $this->k = $k;
        $this->maxIterations = $maxIter;
        $this->convergenceThreshold = $threshold;
        $this->profiler = $profiler;
    }

    public function normalizeData($data) {
        $this->profiler->start('normalizeData');
        
        if (empty($data)) {
            $this->profiler->stop('normalizeData');
            return [];
        }
    
        $normalized = [];
        $features = ['age', 'income', 'purchase_amount'];
        $stats = [];

        // Calculate statistics
        $this->profiler->start('normalizeData_calculate_stats');
        foreach ($features as $feature) {
            $values = array_column($data, $feature);
            $mean = array_sum($values) / count($values);
            $variance = 0;
            foreach ($values as $value) {
                $variance += pow($value - $mean, 2);
            }
            $stdDev = sqrt($variance / count($values));
            $stats[$feature] = ['mean' => $mean, 'stdDev' => $stdDev];
        }
        $this->profiler->stop('normalizeData_calculate_stats');

        // Normalize points
        $this->profiler->start('normalizeData_transform');
        foreach ($data as $point) {
            $normalizedPoint = $point;
            foreach ($features as $feature) {
                $normalizedPoint[$feature] = ($point[$feature] - $stats[$feature]['mean']) /
                                            ($stats[$feature]['stdDev'] ?: 1);
            }
            $normalized[] = $normalizedPoint;
        }
        $this->profiler->stop('normalizeData_transform');

        $this->profiler->stop('normalizeData');
        return $normalized;
    }

    public function euclideanDistance($point1, $point2) {
        $this->profiler->start('euclideanDistance');
        
        $sum = 0;
        $sum += pow($point1['age'] - $point2['age'], 2);
        $sum += pow($point1['income'] - $point2['income'], 2);
        $sum += pow($point1['purchase_amount'] - $point2['purchase_amount'], 2);
        $result = sqrt($sum);
        
        $this->profiler->stop('euclideanDistance');
        return $result;
    }

    public function initializeCentroids($data) {
        $this->profiler->start('initializeCentroids');
        
        srand(RANDOM_SEED);
        $centroids = [];

        // Choose first centroid randomly
        $centroids[] = $data[array_rand($data)];

        // K-means++ initialization
        for ($i = 1; $i < $this->k; $i++) {
            $distances = [];

            foreach ($data as $point) {
                $minDist = PHP_FLOAT_MAX;
                foreach ($centroids as $centroid) {
                    $dist = $this->euclideanDistance($point, $centroid);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                    }
                }
                $distances[] = $minDist * $minDist;
            }

            $sum = array_sum($distances);
            $rand = mt_rand() / mt_getrandmax() * $sum;
            $cumulative = 0;

            foreach ($distances as $idx => $dist) {
                $cumulative += $dist;
                if ($cumulative >= $rand) {
                    $centroids[] = $data[$idx];
                    break;
                }
            }
        }

        $this->profiler->stop('initializeCentroids');
        return $centroids;
    }

    public function assignClusters($data, $centroids) {
        $this->profiler->start('assignClusters');
        
        $clusters = array_fill(0, $this->k, []);

        $this->profiler->start('assignClusters_loop');
        foreach ($data as $point) {
            $minDist = PHP_FLOAT_MAX;
            $clusterIndex = 0;

            foreach ($centroids as $idx => $centroid) {
                $dist = $this->euclideanDistance($point, $centroid);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $clusterIndex = $idx;
                }
            }

            $clusters[$clusterIndex][] = $point;
        }
        $this->profiler->stop('assignClusters_loop');

        $this->profiler->stop('assignClusters');
        return $clusters;
    }

    public function updateCentroids($clusters) {
        $this->profiler->start('updateCentroids');
        
        $centroids = [];

        foreach ($clusters as $idx => $cluster) {
            if (empty($cluster)) {
                $centroids[] = $this->centroids[$idx];
                continue;
            }

            $centroid = [
                'age' => array_sum(array_column($cluster, 'age')) / count($cluster),
                'income' => array_sum(array_column($cluster, 'income')) / count($cluster),
                'purchase_amount' => array_sum(array_column($cluster, 'purchase_amount')) / count($cluster)
            ];

            $centroids[] = $centroid;
        }

        $this->profiler->stop('updateCentroids');
        return $centroids;
    }

    public function hasConverged($oldCentroids, $newCentroids) {
        $this->profiler->start('hasConverged');
        
        for ($i = 0; $i < $this->k; $i++) {
            $dist = $this->euclideanDistance($oldCentroids[$i], $newCentroids[$i]);
            if ($dist > $this->convergenceThreshold) {
                $this->profiler->stop('hasConverged');
                return false;
            }
        }
        
        $this->profiler->stop('hasConverged');
        return true;
    }

    public function fit($data) {
        $this->profiler->start('fit_total');
        
        // Normalize data
        $normalizedData = $this->normalizeData($data);

        // Initialize centroids
        $this->centroids = $this->initializeCentroids($normalizedData);

        // Iterate
        $this->profiler->start('fit_iterations');
        for ($iteration = 0; $iteration < $this->maxIterations; $iteration++) {
            $this->profiler->start("iteration_$iteration");
            
            // Assign clusters
            $this->clusters = $this->assignClusters($normalizedData, $this->centroids);

            // Update centroids
            $newCentroids = $this->updateCentroids($this->clusters);

            // Check convergence
            if ($this->hasConverged($this->centroids, $newCentroids)) {
                $this->profiler->stop("iteration_$iteration");
                echo "✓ Converged after " . ($iteration + 1) . " iterations\n";
                break;
            }

            $this->centroids = $newCentroids;
            $this->profiler->stop("iteration_$iteration");
        }
        $this->profiler->stop('fit_iterations');

        // Final assignment
        $this->profiler->start('final_assignment');
        $labels = [];
        foreach ($normalizedData as $idx => $point) {
            $minDist = PHP_FLOAT_MAX;
            $label = 0;

            foreach ($this->centroids as $clusterIdx => $centroid) {
                $dist = $this->euclideanDistance($point, $centroid);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $label = $clusterIdx;
                }
            }

            $labels[$data[$idx]['customer_id']] = $label;
        }
        $this->profiler->stop('final_assignment');

        $this->profiler->stop('fit_total');
        return $labels;
    }
}

// ============================================================================
// Profiled Helper Functions
// ============================================================================

function getAgeCategory($avgAge) {
    if ($avgAge < 30) return "Young";
    if ($avgAge < 45) return "Middle-Aged";
    if ($avgAge < 60) return "Mature";
    return "Senior";
}

function getIncomeCategory($avgIncome) {
    if ($avgIncome < 30000) return "Budget";
    if ($avgIncome < 50000) return "Mid-Tier";
    if ($avgIncome < 70000) return "Affluent";
    return "High-Income";
}

function getSpendingCategory($avgPurchase) {
    if ($avgPurchase < 1500) return "Conservative";
    if ($avgPurchase < 2500) return "Moderate";
    if ($avgPurchase < 3500) return "Active";
    return "Premium";
}

function generateClusterName($avgAge, $avgIncome, $avgPurchase) {
    return getIncomeCategory($avgIncome) . " " .
           getAgeCategory($avgAge) . " " .
           getSpendingCategory($avgPurchase);
}

function generateClusterDescription($clusterStats) {
    return sprintf(
        "This segment consists of %s customers characterized by %s demographics (avg age %.1f), " .
        "%s income levels (avg $%s), and %s spending behavior (avg $%s per purchase).",
        number_format($clusterStats['customer_count']),
        strtolower(getAgeCategory($clusterStats['avg_age'])),
        $clusterStats['avg_age'],
        strtolower(getIncomeCategory($clusterStats['avg_income'])),
        number_format($clusterStats['avg_income'], 0),
        strtolower(getSpendingCategory($clusterStats['avg_purchase_amount'])),
        number_format($clusterStats['avg_purchase_amount'], 0)
    );
}

function generateBusinessRecommendations($clusterStats) {
    $recommendations = [];
    $avgIncome = $clusterStats['avg_income'];
    $avgPurchase = $clusterStats['avg_purchase_amount'];
    $avgAge = $clusterStats['avg_age'];

    if ($avgIncome > 70000 && $avgPurchase > 3000) {
        $recommendations[] = "Target with premium product offerings and exclusive services";
        $recommendations[] = "Implement VIP loyalty program with personalized benefits";
    } elseif ($avgIncome > 70000 && $avgPurchase < 2000) {
        $recommendations[] = "Identify barriers to purchase through targeted campaigns";
        $recommendations[] = "Introduce mid-tier to premium product lines";
    } elseif ($avgAge < 30) {
        $recommendations[] = "Leverage social media marketing and influencer partnerships";
        $recommendations[] = "Offer entry-level product bundles and flexible payment plans";
    } elseif ($avgAge >= 30 && $avgAge < 55) {
        $recommendations[] = "Focus on value proposition and quality messaging";
        $recommendations[] = "Offer family-oriented products and bundled solutions";
    } elseif ($avgAge >= 55) {
        $recommendations[] = "Emphasize ease of use, reliability, and customer support";
        $recommendations[] = "Provide clear documentation and instructional content";
    } elseif ($avgIncome < 40000) {
        $recommendations[] = "Highlight value pricing and cost-saving benefits";
        $recommendations[] = "Offer payment plans and budget-friendly options";
    } else {
        $recommendations[] = "Implement cross-selling strategies based on purchase history";
        $recommendations[] = "Create targeted email campaigns with personalized offers";
    }

    return implode('; ', $recommendations);
}

function extractCustomerData($pdo, $profiler) {
    $profiler->start('extractCustomerData');
    
    try {
        $sql = "SELECT customer_id, age, gender, income, purchase_amount, region
                FROM customers
                WHERE age IS NOT NULL
                  AND income IS NOT NULL
                  AND purchase_amount IS NOT NULL";
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✓ Extracted " . count($data) . " customer records\n";
        
        $profiler->stop('extractCustomerData');
        return $data;
    } catch (PDOException $e) {
        $profiler->stop('extractCustomerData');
        die("✗ Error extracting data: " . $e->getMessage() . "\n");
    }
}

function calculateClusterStatistics($pdo, $labels, $k, $profiler) {
    $profiler->start('calculateClusterStatistics');
    
    $clusterStats = [];

    for ($i = 0; $i < $k; $i++) {
        $profiler->start("calc_cluster_$i");
        
        $customerIds = array_keys(array_filter($labels, function($label) use ($i) {
            return $label === $i;
        }));

        if (empty($customerIds)) {
            $profiler->stop("calc_cluster_$i");
            continue;
        }

        $placeholders = implode(',', array_fill(0, count($customerIds), '?'));
        $sql = "SELECT
                    COUNT(*) as customer_count,
                    ROUND(AVG(age), 2) as avg_age,
                    MIN(age) as age_min,
                    MAX(age) as age_max,
                    ROUND(AVG(income), 2) as avg_income,
                    ROUND(MIN(income), 2) as income_min,
                    ROUND(MAX(income), 2) as income_max,
                    ROUND(AVG(purchase_amount), 2) as avg_purchase_amount,
                    ROUND(MIN(purchase_amount), 2) as purchase_min,
                    ROUND(MAX(purchase_amount), 2) as purchase_max
                FROM customers
                WHERE customer_id IN ($placeholders)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($customerIds);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get dominant gender
        $sql = "SELECT gender, COUNT(*) as cnt
                FROM customers
                WHERE customer_id IN ($placeholders)
                GROUP BY gender
                ORDER BY cnt DESC
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($customerIds);
        $genderRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['dominant_gender'] = $genderRow['gender'] ?? 'Unknown';

        // Get dominant region
        $sql = "SELECT region, COUNT(*) as cnt
                FROM customers
                WHERE customer_id IN ($placeholders)
                GROUP BY region
                ORDER BY cnt DESC
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($customerIds);
        $regionRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['dominant_region'] = $regionRow['region'] ?? 'Unknown';

        $stats['cluster_id'] = $i;
        $clusterStats[] = $stats;
        
        $profiler->stop("calc_cluster_$i");
    }

    $profiler->stop('calculateClusterStatistics');
    return $clusterStats;
}

function updateDatabase($pdo, $labels, $clusterStats, $profiler) {
    $profiler->start('updateDatabase');
    
    try {
        $pdo->beginTransaction();

        // Clear existing results
        $profiler->start('updateDatabase_clear');
        $pdo->exec("DELETE FROM segmentation_results");
        echo "✓ Cleared existing segmentation results\n";
        $profiler->stop('updateDatabase_clear');

        // Insert cluster assignments
        $profiler->start('updateDatabase_insert_labels');
        $stmt = $pdo->prepare("INSERT INTO segmentation_results (customer_id, cluster_label) VALUES (?, ?)");
        foreach ($labels as $customerId => $label) {
            $stmt->execute([$customerId, $label]);
        }
        echo "✓ Inserted " . count($labels) . " cluster assignments\n";
        $profiler->stop('updateDatabase_insert_labels');

        // Clear cluster metadata
        $profiler->start('updateDatabase_clear_metadata');
        $pdo->exec("DELETE FROM cluster_metadata");
        echo "✓ Cleared existing cluster metadata\n";
        $profiler->stop('updateDatabase_clear_metadata');

        // Insert cluster metadata
        $profiler->start('updateDatabase_insert_metadata');
        $stmt = $pdo->prepare("
            INSERT INTO cluster_metadata (
                cluster_id, cluster_name, description,
                avg_age, avg_income, avg_purchase_amount,
                customer_count, age_min, age_max,
                income_min, income_max, purchase_min, purchase_max,
                dominant_gender, dominant_region, business_recommendation
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($clusterStats as $stats) {
            $clusterName = generateClusterName($stats['avg_age'], $stats['avg_income'], $stats['avg_purchase_amount']);
            $description = generateClusterDescription($stats);
            $recommendations = generateBusinessRecommendations($stats);

            $stmt->execute([
                $stats['cluster_id'],
                $clusterName,
                $description,
                $stats['avg_age'],
                $stats['avg_income'],
                $stats['avg_purchase_amount'],
                $stats['customer_count'],
                $stats['age_min'],
                $stats['age_max'],
                $stats['income_min'],
                $stats['income_max'],
                $stats['purchase_min'],
                $stats['purchase_max'],
                $stats['dominant_gender'],
                $stats['dominant_region'],
                $recommendations
            ]);
        }

        echo "✓ Inserted metadata for " . count($clusterStats) . " clusters\n";
        $profiler->stop('updateDatabase_insert_metadata');

        $pdo->commit();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $profiler->stop('updateDatabase');
        die("✗ Error updating database: " . $e->getMessage() . "\n");
    }
    
    $profiler->stop('updateDatabase');
}

// ============================================================================
// Main Execution
// ============================================================================

$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>Profiled K-Means Clustering</title>";
    echo "<style>body{font-family:monospace;background:#1e1e1e;color:#d4d4d4;padding:20px;}";
    echo ".success{color:#4ec9b0;} .error{color:#f48771;} .info{color:#569cd6;}</style></head><body>";
    echo "<h2>Profiled K-Means Customer Clustering</h2><pre>";
}

// Initialize profiler
$profiler = new Profiler();

// Get number of clusters
$numClusters = DEFAULT_CLUSTERS;
if ($isCLI && isset($argv[1])) {
    $numClusters = (int)$argv[1];
} elseif (!$isCLI && isset($_GET['clusters'])) {
    $numClusters = (int)$_GET['clusters'];
}

echo str_repeat("=", 70) . "\n";
echo "K-MEANS CUSTOMER CLUSTERING (PROFILED)\n";
echo str_repeat("=", 70) . "\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "Number of clusters: $numClusters\n";
echo str_repeat("=", 70) . "\n\n";

// Step 1: Extract customer data
echo "STEP 1: Extracting customer data...\n";
$customerData = extractCustomerData($pdo, $profiler);
echo "\n";

// Step 2: Run k-means clustering
echo "STEP 2: Running k-means clustering...\n";
$kmeans = new KMeansClusteringProfiled($numClusters, MAX_ITERATIONS, CONVERGENCE_THRESHOLD, $profiler);
$labels = $kmeans->fit($customerData);
echo "✓ Clustering complete\n\n";

// Step 3: Calculate cluster statistics
echo "STEP 3: Calculating cluster statistics...\n";
$clusterStats = calculateClusterStatistics($pdo, $labels, $numClusters, $profiler);
echo "✓ Statistics calculated for " . count($clusterStats) . " clusters\n\n";

// Step 4: Update database
echo "STEP 4: Updating database...\n";
updateDatabase($pdo, $labels, $clusterStats, $profiler);
echo "\n";

// Step 5: Display summary
echo str_repeat("=", 70) . "\n";
echo "CLUSTERING SUMMARY\n";
echo str_repeat("=", 70) . "\n\n";

foreach ($clusterStats as $stats) {
    $clusterName = generateClusterName($stats['avg_age'], $stats['avg_income'], $stats['avg_purchase_amount']);
    echo "Cluster {$stats['cluster_id']}: $clusterName\n";
    echo "  Customers: " . number_format($stats['customer_count']) . "\n";
    echo "  Age: {$stats['avg_age']} ({$stats['age_min']}-{$stats['age_max']})\n";
    echo "  Income: $" . number_format($stats['avg_income'], 0) .
         " ($" . number_format($stats['income_min'], 0) . "-$" . number_format($stats['income_max'], 0) . ")\n";
    echo "  Purchase: $" . number_format($stats['avg_purchase_amount'], 0) .
         " ($" . number_format($stats['purchase_min'], 0) . "-$" . number_format($stats['purchase_max'], 0) . ")\n\n";
}

echo str_repeat("=", 70) . "\n";
echo "✓ Clustering complete! $numClusters clusters created.\n";
echo str_repeat("=", 70) . "\n";

// Display performance profile
$profiler->report();

if (!$isCLI) {
    echo "</pre>";
    echo "<p><a href='index.php' style='color:#569cd6;'>← Back to Dashboard</a></p>";
    echo "</body></html>";
}
?>