<?php

require __DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
webfiori\framework\App::initFrameworkVersionInfo();
echo "Creating Commit...\n";
exec('git commit --allow-empty -m "chore: release '.WF_VERSION.'" -m "Release-As: '.WF_VERSION.'"');
echo 'Commit Created.';