<?php

require_once __DIR__ . '/db.php';

echo "QUERY PERFORMANCE ANALYSIS\n";
echo str_repeat("=", 80) . "\n\n";

// Query 1: Cluster distribution
echo "Query 1: Cluster Distribution\n";
echo str_repeat("-", 80) . "\n";
$stmt = $pdo->query("
    EXPLAIN SELECT 
        sr.cluster_label, 
        cm.cluster_name,
        COUNT(*) as customer_count
    FROM segmentation_results sr
    JOIN cluster_metadata cm ON sr.cluster_label = cm.cluster_id
    GROUP BY sr.cluster_label, cm.cluster_name
");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "\n";

// Query 2: Customer details with cluster
echo "Query 2: Customer Details with Cluster Assignment\n";
echo str_repeat("-", 80) . "\n";
$stmt = $pdo->query("
    EXPLAIN SELECT 
        c.customer_id, c.name, c.age, c.gender, 
        c.income, c.region, c.purchase_amount,
        sr.cluster_label, cm.cluster_name
    FROM customers c
    LEFT JOIN segmentation_results sr ON c.customer_id = sr.customer_id
    LEFT JOIN cluster_metadata cm ON sr.cluster_label = cm.cluster_id
    WHERE c.region = 'NCR'
    LIMIT 100
");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "\n";

// Query 3: Cluster statistics
echo "Query 3: Cluster Statistics Aggregation\n";
echo str_repeat("-", 80) . "\n";
$stmt = $pdo->query("
    EXPLAIN SELECT
        COUNT(*) as customer_count,
        ROUND(AVG(age), 2) as avg_age,
        MIN(age) as age_min,
        MAX(age) as age_max
    FROM customers c
    JOIN segmentation_results sr ON c.customer_id = sr.customer_id
    WHERE sr.cluster_label = 0
");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "\n";

echo "Analysis complete. Check for 'Using filesort', 'Using temporary', or full table scans.\n";
?>
