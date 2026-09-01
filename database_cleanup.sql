-- =====================================================
-- DATABASE CLEANUP SCRIPT
-- Date: 2025-11-26
-- Purpose: Clean up failed jobs and update email templates
-- =====================================================

-- =====================================================
-- 1. FAILED JOBS CLEANUP
-- =====================================================

-- View failed jobs summary
SELECT 
    DATE(failed_at) as date,
    COUNT(*) as count,
    queue,
    LEFT(exception, 100) as error_preview
FROM failed_jobs
GROUP BY DATE(failed_at), queue
ORDER BY date DESC
LIMIT 20;

-- OPTION 1: Delete failed jobs older than 7 days
-- DELETE FROM failed_jobs 
-- WHERE failed_at < DATE_SUB(NOW(), INTERVAL 7 DAY);

-- OPTION 2: Delete all failed jobs (RECOMMENDED after reviewing)
-- TRUNCATE TABLE failed_jobs;

-- =====================================================
-- 2. NOTIFICATION TEMPLATES UPDATE
-- =====================================================

-- Check current email_from addresses
SELECT DISTINCT email_from 
FROM notification_templates;

-- Preview templates that will be updated
SELECT id, template_key, email_from, subject 
FROM notification_templates 
WHERE email_from LIKE '%gainpaydigitalservice.com%';

-- UPDATE email_from addresses to match current configuration
-- UPDATE notification_templates 
-- SET email_from = 'nunupay@rickxchange.com' 
-- WHERE email_from LIKE '%gainpaydigitalservice.com%';

-- Verify update (run after UPDATE)
-- SELECT id, template_key, email_from, subject 
-- FROM notification_templates 
-- LIMIT 10;

-- =====================================================
-- 3. SUBSCRIBER TABLE MONITORING
-- =====================================================

-- Check current subscriber count (should be 0)
SELECT COUNT(*) as total_subscribers FROM subscribers;

-- Check for any recent additions
SELECT * FROM subscribers 
ORDER BY created_at DESC 
LIMIT 20;

-- Monitor bulk additions by date
SELECT 
    DATE(created_at) as date,
    COUNT(*) as count
FROM subscribers
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- =====================================================
-- 4. USER ACCOUNT MONITORING
-- =====================================================

-- Check for suspicious bulk user registrations
SELECT 
    DATE(created_at) as date,
    COUNT(*) as count
FROM users
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at)
HAVING count > 5
ORDER BY date DESC;

-- Check recent user registrations
SELECT id, firstname, email, created_at 
FROM users 
ORDER BY created_at DESC 
LIMIT 20;

-- =====================================================
-- 5. ADMIN ACCOUNT VERIFICATION
-- =====================================================

-- Verify only legitimate admin accounts exist
SELECT id, name, email, created_at 
FROM admins 
ORDER BY created_at DESC;

-- Check for any admin accounts created recently
SELECT id, name, email, created_at 
FROM admins 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);

-- =====================================================
-- 6. EMAIL QUEUE STATUS
-- =====================================================

-- Check pending jobs
SELECT COUNT(*) as pending_jobs FROM jobs;

-- Check failed jobs count
SELECT COUNT(*) as failed_jobs FROM failed_jobs;

-- View recent failed job details
SELECT 
    id,
    queue,
    failed_at,
    LEFT(exception, 200) as error
FROM failed_jobs
ORDER BY failed_at DESC
LIMIT 10;

-- =====================================================
-- EXECUTION INSTRUCTIONS
-- =====================================================

-- 1. Review all SELECT queries first to understand current state
-- 2. Uncomment and run DELETE/UPDATE queries one at a time
-- 3. Verify results after each modification
-- 4. Keep a backup before running any DELETE/UPDATE queries

-- To execute:
-- mysql -u your_username -p your_database < database_cleanup.sql

-- Or via Laravel Tinker:
-- php artisan tinker
-- DB::statement("your SQL here");
