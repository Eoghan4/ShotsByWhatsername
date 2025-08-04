-- Complete Database Setup Script for ShotsByWhatsername Photography Portfolio
-- This file creates the database, tables, and populates with initial data

-- ============================================
-- Create Database
-- ============================================

DROP DATABASE IF EXISTS `shots_by_whatsername`;
CREATE DATABASE `shots_by_whatsername`;
USE `shots_by_whatsername`;

-- ============================================
-- Create Tables
-- ============================================

-- Users Table for Admin Authentication
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(100) NOT NULL UNIQUE,
    `password_hash` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
);

-- Images Table for Gallery Management
CREATE TABLE `images` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `category` varchar(100) NOT NULL,
    `url` varchar(500) NOT NULL,
    PRIMARY KEY (`id`)
);

-- ============================================
-- Insert Default Admin User
-- ============================================

-- Create default admin user (password: 'admin123')
INSERT INTO `users` (`email`, `password_hash`) VALUES 
('eoghanmcgough@gmail.com', '$2y$10$L2qNTakXiKs6xIJGGX/fYeQp.6LIk6l4fRL.CgwBjACWZk5o/CYiO');

-- ============================================
-- Insert Sample Images
-- ============================================

-- Insert sample images using the photos from the pictures folder
INSERT INTO `images` (`title`, `category`, `url`) VALUES 
('Green Landscape', 'nature', '../pictures/green.webp'),
('Howth Coastline', 'landscape', '../pictures/howth.webp'),
('Water Reflection', 'nature', '../pictures/water.webp'),
('Emerald Fields', 'landscape', '../pictures/green.webp'),
('Coastal Beauty', 'nature', '../pictures/howth.webp'),
('Serene Waters', 'landscape', '../pictures/water.webp');
