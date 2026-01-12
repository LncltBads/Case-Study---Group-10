USE customer_segmentation_ph;

-- Drop existing views if any
DROP VIEW IF EXISTS v_customer_segments;
DROP VIEW IF EXISTS v_cluster_summary;

-- View 1: Customer segments (most common query)
CREATE VIEW v_customer_segments AS
SELECT 
    c.customer_id,
    c.name,
    c.age,
    c.gender,
    c.income,
    c.region,
    c.purchase_amount,
    c.upload_timestamp,
    sr.cluster_label,
    cm.cluster_name,
    cm.description,
    cm.avg_age as cluster_avg_age,
    cm.avg_income as cluster_avg_income,
    cm.avg_purchase_amount as cluster_avg_purchase,
    cm.customer_count as cluster_size,
    cm.dominant_gender,
    cm.dominant_region,
    cm.business_recommendation
FROM customers c
LEFT JOIN segmentation_results sr ON c.customer_id = sr.customer_id
LEFT JOIN cluster_metadata cm ON sr.cluster_label = cm.cluster_id;

-- View 2: Cluster summary statistics
CREATE VIEW v_cluster_summary AS
SELECT 
    cm.cluster_id,
    cm.cluster_name,
    cm.customer_count,
    cm.avg_age,
    cm.avg_income,
    cm.avg_purchase_amount,
    cm.dominant_gender,
    cm.dominant_region,
    ROUND(cm.customer_count * 100.0 / (SELECT SUM(customer_count) FROM cluster_metadata), 2) as percentage
FROM cluster_metadata cm
ORDER BY cm.customer_count DESC;

-- Create cache table
DROP TABLE IF EXISTS customer_segments_cache;
CREATE TABLE customer_segments_cache AS SELECT * FROM v_customer_segments;

-- Index the cache
CREATE INDEX idx_cache_region ON customer_segments_cache(region);
CREATE INDEX idx_cache_cluster ON customer_segments_cache(cluster_label);
CREATE INDEX idx_cache_age ON customer_segments_cache(age);
CREATE INDEX idx_cache_name ON customer_segments_cache(name);

-- Stored procedure to refresh cache
DELIMITER $$
DROP PROCEDURE IF EXISTS refresh_customer_segments_cache$$
CREATE PROCEDURE refresh_customer_segments_cache()
BEGIN
    TRUNCATE TABLE customer_segments_cache;
    INSERT INTO customer_segments_cache SELECT * FROM v_customer_segments;
    SELECT CONCAT('Cache refreshed: ', ROW_COUNT(), ' rows') as status;
END$$
DELIMITER ;

SELECT 'Views and cache created successfully!' as status;