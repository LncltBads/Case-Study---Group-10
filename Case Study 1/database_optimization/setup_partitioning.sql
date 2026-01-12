USE customer_segmentation_ph;

-- Backup first
CREATE TABLE customers_backup AS SELECT * FROM customers;

-- Remove foreign keys temporarily
ALTER TABLE segmentation_results DROP FOREIGN KEY fk_seg_customer;

-- Add partitioning
ALTER TABLE customers
PARTITION BY LIST COLUMNS(region) (
    PARTITION p_ncr VALUES IN ('NCR'),
    PARTITION p_luzon VALUES IN ('Region I', 'Region II', 'Region III', 'Region IV-A', 'Region IV-B', 'Region V', 'CAR'),
    PARTITION p_visayas VALUES IN ('Region VI', 'Region VII', 'Region VIII'),
    PARTITION p_mindanao VALUES IN ('Region IX', 'Region X', 'Region XI', 'Region XII', 'Region XIII', 'BARMM'),
    PARTITION p_other VALUES IN (DEFAULT)
);

-- Restore foreign key
ALTER TABLE segmentation_results 
ADD CONSTRAINT fk_seg_customer 
FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE;

SELECT 'Partitioning complete!' as status;