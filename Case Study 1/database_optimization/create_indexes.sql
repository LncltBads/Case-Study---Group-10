
USE customer_segmentation_ph;

-- Show current indexes
SHOW INDEX FROM customers;
SHOW INDEX FROM segmentation_results;

-- Add indexes to customers table
CREATE INDEX idx_clustering_features ON customers(age, income, purchase_amount);
CREATE INDEX idx_region_age ON customers(region, age);
CREATE INDEX idx_gender ON customers(gender);
CREATE INDEX idx_upload_timestamp ON customers(upload_timestamp);

-- Add composite index for common queries
CREATE INDEX idx_region_gender_age ON customers(region, gender, age);

-- Optimize segmentation_results
CREATE INDEX idx_cluster_customer ON segmentation_results(cluster_label, customer_id);

-- Show new indexes
SHOW INDEX FROM customers;

-- Analyze tables for query optimization
ANALYZE TABLE customers;
ANALYZE TABLE segmentation_results;
ANALYZE TABLE cluster_metadata;

SELECT 'Indexes created successfully!' as status;