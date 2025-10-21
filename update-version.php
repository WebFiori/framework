<?php
/**
 * Script to update WebFiori Framework version constants
 * Usage: php update-version.php --version=3.0.0 --type=Stable
 */

$options = getopt('', ['version:', 'type:']);

if (!isset($options['version']) || !isset($options['type'])) {
    echo "Usage: php update-version.php --version=VERSION --type=TYPE\n";
    echo "Example: php update-version.php --version=3.0.0 --type=Stable\n";
    exit(1);
}

$version = $options['version'];
$type = $options['type'];
$date = date('Y-m-d');

$appFile = __DIR__ . '/WebFiori/Framework/App.php';
$content = file_get_contents($appFile);

$content = preg_replace(
    "/define\('WF_VERSION', '[^']*'\);/",
    "define('WF_VERSION', '$version');",
    $content
);

$content = preg_replace(
    "/define\('WF_VERSION_TYPE', '[^']*'\);/",
    "define('WF_VERSION_TYPE', '$type');",
    $content
);

$content = preg_replace(
    "/define\('WF_RELEASE_DATE', '[^']*'\);/",
    "define('WF_RELEASE_DATE', '$date');",
    $content
);

file_put_contents($appFile, $content);

echo "Updated version constants:\n";
echo "WF_VERSION: $version\n";
echo "WF_VERSION_TYPE: $type\n";
echo "WF_RELEASE_DATE: $date\n";
