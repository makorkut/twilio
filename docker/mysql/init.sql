-- MySQL Initialization Script for Docker
-- This script runs only once when the container is first created

-- Set character set and collation
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Create additional users or configurations if needed
-- (The main database and user are created by docker-compose environment variables)

-- Log initialization
SELECT 'MySQL initialization completed' AS status;
