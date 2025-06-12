<?php
// Mock router for development purposes
if (file_exists(__DIR__ . $_SERVER['REQUEST_URI']) && !is_dir(__DIR__ . $_SERVER['REQUEST_URI'])) return false;
include __DIR__ . '/index.php';