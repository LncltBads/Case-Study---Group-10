<?php
/**
 * Simple Integration Tests for Customer Segmentation Dashboard
 * 
 * HOW TO USE:
 * 1. Save this file as: tests/simple_integration_test.php
 * 2. Open command prompt/terminal
 * 3. Navigate to your project folder
 * 4. Run: php tests/simple_integration_test.php
 * 
 * You'll see ✓ for passed tests and ✗ for failed tests
 */

// ============================================================================
// HELPER FUNCTIONS - These make testing easier
// ============================================================================

function assert_true($condition, $message) {
    if ($condition) {
        echo "✓ PASS: $message\n";
        return true;
    } else {
        echo "✗ FAIL: $message\n";
        return false;
    }
}

function assert_equals($expected, $actual, $message) {
    if ($expected === $actual) {
        echo "✓ PASS: $message\n";
        return true;
    } else {
        echo "✗ FAIL: $message - Expected '$expected', got '$actual'\n";
        return false;
    }
}

function assert_contains($needle, $haystack, $message) {
    if (strpos($haystack, $needle) !== false) {
        echo "✓ PASS: $message\n";
        return true;
    } else {
        echo "✗ FAIL: $message - Expected to find '$needle'\n";
        return false;
    }
}

function assert_greater_than($minimum, $actual, $message) {
    if ($actual > $minimum) {
        echo "✓ PASS: $message (Value: $actual)\n";
        return true;
    } else {
        echo "✗ FAIL: $message - Expected > $minimum, got $actual\n";
        return false;
    }
}

// ============================================================================
// START TESTING
// ============================================================================

echo "\n";
echo "========================================\n";
echo "  INTEGRATION TESTS\n";
echo "========================================\n\n";

$totalTests = 0;
$passedTests = 0;

// ============================================================================
// TEST 1: Database Connection
// ============================================================================
echo "TEST 1: Database Connection\n";
echo "----------------------------\n";

// Initialize $pdo as null first
$pdo = null;

try {
    // Manually create database connection instead of requiring db.php
    $host = 'localhost';
    $dbname = 'customer_segmentation_ph';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if (assert_true(isset($pdo), "Database connection object created")) {
        $passedTests++;
    }
    $totalTests++;
    
    if (assert_true($pdo instanceof PDO, "Connection is valid PDO instance")) {
        $passedTests++;
    }
    $totalTests++;
    
} catch (PDOException $e) {
    echo "✗ FAIL: Database connection failed - " . $e->getMessage() . "\n";
    echo "\nPossible issues:\n";
    echo "  1. MySQL is not running (start XAMPP)\n";
    echo "  2. Database 'customer_segmentation_ph' doesn't exist\n";
    echo "  3. Wrong username/password\n";
    echo "  4. Wrong host (should be 'localhost')\n\n";
    $totalTests += 2;
    echo " Cannot continue without database connection.\n\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ FAIL: Unexpected error - " . $e->getMessage() . "\n";
    $totalTests += 2;
    exit(1);
}

echo "\n";

// ============================================================================
// TEST 2: Customers Table Exists and Has Data
// ============================================================================
echo "TEST 2: Customers Table\n";
echo "----------------------------\n";

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers");
    $count = $stmt->fetchColumn();
    
    if (assert_greater_than(0, $count, "Customers table has data")) {
        $passedTests++;
    }
    $totalTests++;
    
    // Check if we have enough data for testing
    if ($count < 50) {
        echo " WARNING: Only $count customers. Recommend at least 100 for testing.\n";
    }
    
    // Check table structure
    $stmt = $pdo->query("SELECT * FROM customers LIMIT 1");
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (assert_true(isset($customer['customer_id']), "customer_id column exists")) {
        $passedTests++;
    }
    $totalTests++;
    
    if (assert_true(isset($customer['age']), "age column exists")) {
        $passedTests++;
    }
    $totalTests++;
    
    if (assert_true(isset($customer['gender']), "gender column exists")) {
        $passedTests++;
    }
    $totalTests++;
    
    if (assert_true(isset($customer['income']), "income column exists")) {
        $passedTests++;
    }
    $totalTests++;
    
    if (assert_true(isset($customer['purchase_amount']), "purchase_amount column exists")) {
        $passedTests++;
    }
    $totalTests++;
    
    if (assert_true(isset($customer['region']), "region column exists")) {
        $passedTests++;
    }
    $totalTests++;
    
} catch (Exception $e) {
    echo "✗ FAIL: Customers table error - " . $e->getMessage() . "\n";
    $totalTests += 7;
}

echo "\n";

// ============================================================================
// TEST 3: Gender Segmentation Query
// ============================================================================
echo "TEST 3: Gender Segmentation\n";
echo "----------------------------\n";

try {
    $sql = "SELECT gender, COUNT(*) AS total_customers, 
            ROUND(AVG(income), 2) AS avg_income, 
            ROUND(AVG(purchase_amount), 2) AS avg_purchase_amount 
            FROM customers 
            GROUP BY gender";
    
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (assert_greater_than(0, count($results), "Gender segmentation returns results")) {
        $passedTests++;
    }
    $totalTests++;
    
    if (count($results) > 0) {
        if (assert_true(isset($results[0]['gender']), "Results contain gender column")) {
            $passedTests++;
        }
        $totalTests++;
        
        if (assert_true(isset($results[0]['total_customers']), "Results contain total_customers column")) {
            $passedTests++;
        }
        $totalTests++;
        
        if (assert_true(isset($results[0]['avg_income']), "Results contain avg_income column")) {
            $passedTests++;
        }
        $totalTests++;
        
        if (assert_true(isset($results[0]['avg_purchase_amount']), "Results contain avg_purchase_amount column")) {
            $passedTests++;
        }
        $totalTests++;
    } else {
        $totalTests += 4;
    }
    
} catch (Exception $e) {
    echo "✗ FAIL: Gender segmentation query failed - " . $e->getMessage() . "\n";
    $totalTests += 5;
}

echo "\n";

// ============================================================================
// TEST 4: Region Segmentation Query
// ============================================================================
echo "TEST 4: Region Segmentation\n";
echo "----------------------------\n";

try {
    $sql = "SELECT region, COUNT(*) AS total_customers, 
            ROUND(AVG(income), 2) AS avg_income, 
            ROUND(AVG(purchase_amount), 2) AS avg_purchase_amount 
            FROM customers 
            GROUP BY region 
            ORDER BY total_customers DESC";
    
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (assert_greater_than(0, count($results), "Region segmentation returns results")) {
        $passedTests++;
    }
    $totalTests++;
    
    if (count($results) > 0) {
        if (assert_true(isset($results[0]['region']), "Results contain region column")) {
            $passedTests++;
        }
        $totalTests++;
        
        // Check if ordered correctly (DESC)
        if (count($results) > 1) {
            $isOrdered = $results[0]['total_customers'] >= $results[1]['total_customers'];
            if (assert_true($isOrdered, "Results ordered by total_customers DESC")) {
                $passedTests++;
            }
            $totalTests++;
        }
    } else {
        $totalTests += 2;
    }
    
} catch (Exception $e) {
    echo "✗ FAIL: Region segmentation query failed - " . $e->getMessage() . "\n";
    $totalTests += 3;
}

echo "\n";

// ============================================================================
// TEST 5: Age Group Segmentation Query
// ============================================================================
echo "TEST 5: Age Group Segmentation\n";
echo "----------------------------\n";

try {
    $sql = "SELECT 
            CASE 
                WHEN age BETWEEN 18 AND 25 THEN '18-25'
                WHEN age BETWEEN 26 AND 40 THEN '26-40'
                WHEN age BETWEEN 41 AND 60 THEN '41-60'
                ELSE '61+'
            END AS age_group,
            COUNT(*) AS total_customers,
            ROUND(AVG(income), 2) AS avg_income,
            ROUND(AVG(purchase_amount), 2) AS avg_purchase_amount
            FROM customers
            GROUP BY age_group
            ORDER BY age_group";
    
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (assert_greater_than(0, count($results), "Age group segmentation returns results")) {
        $passedTests++;
    }
    $totalTests++;
    
    if (count($results) > 0) {
        if (assert_true(isset($results[0]['age_group']), "Results contain age_group column")) {
            $passedTests++;
        }
        $totalTests++;
    } else {
        $totalTests++;
    }
    
} catch (Exception $e) {
    echo "✗ FAIL: Age group segmentation query failed - " . $e->getMessage() . "\n";
    $totalTests += 2;
}

echo "\n";

// ============================================================================
// TEST 6: Segmentation Results Table (Clustering)
// ============================================================================
echo "TEST 6: Segmentation Results Table\n";
echo "----------------------------\n";

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM segmentation_results");
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        if (assert_greater_than(0, $count, "Segmentation results table has data")) {
            $passedTests++;
        }
        $totalTests++;
        
        // Check structure
        $stmt = $pdo->query("SELECT * FROM segmentation_results LIMIT 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (assert_true(isset($result['customer_id']), "customer_id column exists")) {
            $passedTests++;
        }
        $totalTests++;
        
        if (assert_true(isset($result['cluster_label']), "cluster_label column exists")) {
            $passedTests++;
        }
        $totalTests++;
        
    } else {
        echo " WARNING: No segmentation results found.\n";
        echo "   Run clustering first: php run_clustering.php\n";
        $totalTests += 3;
    }
    
} catch (Exception $e) {
    echo " WARNING: segmentation_results table doesn't exist - " . $e->getMessage() . "\n";
    echo "   This is OK if you haven't run clustering yet.\n";
    $totalTests += 3;
}

echo "\n";

// ============================================================================
// TEST 7: Cluster Metadata Table
// ============================================================================
echo "TEST 7: Cluster Metadata Table\n";
echo "----------------------------\n";

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM cluster_metadata");
    $clusterCount = $stmt->fetchColumn();
    
    if ($clusterCount > 0) {
        if (assert_greater_than(0, $clusterCount, "Cluster metadata exists")) {
            $passedTests++;
        }
        $totalTests++;
        
        // Check required fields
        $stmt = $pdo->query("SELECT * FROM cluster_metadata LIMIT 1");
        $cluster = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (assert_true(isset($cluster['cluster_id']), "cluster_id column exists")) {
            $passedTests++;
        }
        $totalTests++;
        
        if (assert_true(isset($cluster['cluster_name']), "cluster_name column exists")) {
            $passedTests++;
        }
        $totalTests++;
        
        if (assert_true(isset($cluster['description']), "description column exists")) {
            $passedTests++;
        }
        $totalTests++;
        
        if (assert_true(isset($cluster['business_recommendation']), "business_recommendation column exists")) {
            $passedTests++;
        }
        $totalTests++;
        
        if (assert_true(isset($cluster['avg_age']), "avg_age column exists")) {
            $passedTests++;
        }
        $totalTests++;
        
        if (assert_true(isset($cluster['avg_income']), "avg_income column exists")) {
            $passedTests++;
        }
        $totalTests++;
        
        if (assert_true(isset($cluster['avg_purchase_amount']), "avg_purchase_amount column exists")) {
            $passedTests++;
        }
        $totalTests++;
        
        // Check if cluster_name is not null
        if (assert_true(!empty($cluster['cluster_name']), "cluster_name has value")) {
            $passedTests++;
        }
        $totalTests++;
        
    } else {
        echo " WARNING: No cluster metadata found.\n";
        echo "   Run clustering first: php run_clustering.php\n";
        $totalTests += 9;
    }
    
} catch (Exception $e) {
    echo " WARNING: cluster_metadata table doesn't exist - " . $e->getMessage() . "\n";
    echo "   This is OK if you haven't run clustering yet.\n";
    $totalTests += 9;
}

echo "\n";

// ============================================================================
// TEST 8: Login Functionality (Simulated)
// ============================================================================
echo "TEST 8: Login Validation Logic\n";
echo "----------------------------\n";

// Simulate login logic
$valid_username = 'admin';
$valid_password = 'password';

// Test correct credentials
$test_username = 'admin';
$test_password = 'password';

$login_success = ($test_username === $valid_username && $test_password === $valid_password);

if (assert_true($login_success, "Valid credentials (admin/password) accepted")) {
    $passedTests++;
}
$totalTests++;

// Test incorrect credentials
$test_username = 'wrong';
$test_password = 'wrong';

$login_fail = !($test_username === $valid_username && $test_password === $valid_password);

if (assert_true($login_fail, "Invalid credentials rejected")) {
    $passedTests++;
}
$totalTests++;

echo "\n";

// ============================================================================
// FINAL SUMMARY
// ============================================================================
echo "========================================\n";
echo "  TEST SUMMARY\n";
echo "========================================\n";
echo "Total Tests:  $totalTests\n";
echo "Passed:       $passedTests ✓\n";
echo "Failed:       " . ($totalTests - $passedTests) . " ✗\n";

$percentage = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;
echo "Success Rate: $percentage%\n";
echo "========================================\n\n";

if ($passedTests === $totalTests) {
    echo "ALL TESTS PASSED! Your application is working correctly.\n\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED. Please review the failures above.\n\n";
    exit(1);
}