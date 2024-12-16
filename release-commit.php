<?php

require __DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
webfiori\framework\App::initFrameworkVersionInfo();
echo "Creating Commit...\n";
$commit = 'git commit --allow-empty -m "chore: release v'.WF_VERSION.'" -m "Release-As: v'.WF_VERSION.'"';
echo $commit."\n";
exec($commit);
echo 'Commit Created.';