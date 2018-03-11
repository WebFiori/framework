<?php
require_once '../root.php';
SessionManager::get()->kill();
header('location: login');