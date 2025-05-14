<?php
// DB credentials and app constants
// PSR-12: Constants for PDO connection and global settings

// Database
const DB_HOST = 'localhost';
const DB_NAME = 'ayzkrim_db';
const DB_USER = 'root'; // Change if not using default XAMPP
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

// App
const BASE_URL = 'http://localhost/ayskrimWebsite';
const HOME_URL = BASE_URL . '/landingPage/home/home.php';
const APP_NAME = 'Ayskrim E-Commerce';
const DEFAULT_PROFILE_PIC = 'default.png';
const SESSION_TIMEOUT = 3600; // seconds
const ENVIRONMENT = 'development'; // Set to 'production' for live site

// Security
const PASSWORD_COST = 10; // bcrypt cost
const CSRF_TOKEN_KEY = 'ayskrim_csrf_token';
