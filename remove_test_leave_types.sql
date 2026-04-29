-- SQL to remove test leave types
-- Run this in your database management tool

DELETE FROM leave_types WHERE title LIKE '%Test%' OR title LIKE '%TEST%';
