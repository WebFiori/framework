<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework;

use Error;
use ReflectionClass;
use WebFiori\File\File;

/**
 * Handles auto-discovery and registration of classes from directories.
 *
 * @author Ibrahim
 */
class ClassRegistrar {
    /**
     * Scans a directory for PHP classes and registers them via a callback.
     *
     * @param string $folder The folder to scan (relative to APP_PATH or ROOT_PATH).
     * @param callable $regCallback Callback invoked with each instantiated class.
     * @param string|null $suffix If set, only classes ending with this suffix are registered.
     * @param array $constructorParams Parameters to pass to class constructors.
     * @param array $otherParams Additional parameters passed to the callback after the instance.
     */
    public static function register(string $folder, callable $regCallback, ?string $suffix = null, array $constructorParams = [], array $otherParams = []): void {
        $dir = APP_PATH.$folder;

        if (!File::isDirectory($dir)) {
            $dir = ROOT_PATH.DS.$folder;
        }

        if (File::isDirectory($dir)) {
            $dirContent = array_diff(scandir($dir), ['.', '..']);
            $folder = str_replace('/', '\\', $folder);

            foreach ($dirContent as $phpFile) {
                $expl = explode('.', $phpFile);

                if (count($expl) == 2 && $expl[1] == 'php') {
                    if ($suffix !== null) {
                        $classSuffix = substr($expl[0], -1 * strlen($suffix));

                        if ($classSuffix !== $suffix) {
                            continue;
                        }
                    }

                    self::registerHelper([
                        'dir' => $dir,
                        'php-file' => $phpFile,
                        'folder' => $folder,
                        'class-name' => $expl[0],
                        'params' => $otherParams,
                        'callback' => $regCallback,
                        'constructor-params' => $constructorParams
                    ]);
                }
            }
        }
    }
    /**
     * Helper for instantiating and registering a single class.
     *
     * @param array $options Configuration with dir, php-file, folder, class-name, params, callback, constructor-params.
     */
    private static function registerHelper(array $options): void {
        $dir = $options['dir'];
        $phpFile = $options['php-file'];
        $folder = $options['folder'];
        $className = $options['class-name'];
        $otherParams = $options['params'];
        $regCallback = $options['callback'];
        $constructorParams = $options['constructor-params'];
        $instanceNs = require_once $dir.DS.$phpFile;

        if (strlen($instanceNs) == 0 || $instanceNs == 1) {
            $instanceNs = self::extractNamespace($dir.DS.$phpFile);

            if ($instanceNs === null) {
                $instanceNs = '\\'.APP_DIR.'\\'.$folder;
            }
        }
        $class = $instanceNs.'\\'.$className;

        try {
            $reflectionClass = new ReflectionClass($class);

            if (self::canAcceptArgs($reflectionClass, $constructorParams)) {
                $instance = $reflectionClass->newInstanceArgs($constructorParams);
            } else {
                $instance = $reflectionClass->newInstance();
            }

            $toPass = [$instance];

            foreach ($otherParams as $param) {
                $toPass[] = $param;
            }
            call_user_func_array($regCallback, $toPass);
        } catch (Error $ex) {
        }
    }
    /**
     * Checks if a class constructor can accept the given arguments.
     *
     * @param ReflectionClass $refClass The reflection of the class.
     * @param array $args The arguments to check.
     *
     * @return bool
     */
    private static function canAcceptArgs(ReflectionClass $refClass, array $args): bool {
        if (empty($args)) {
            return true;
        }

        $constructor = $refClass->getConstructor();

        if ($constructor === null) {
            return false;
        }

        $params = $constructor->getParameters();

        if (count($args) > count($params)) {
            return false;
        }

        foreach ($args as $index => $arg) {
            if (!isset($params[$index])) {
                return false;
            }

            $paramType = $params[$index]->getType();

            if ($paramType === null) {
                continue;
            }

            if ($paramType instanceof \ReflectionUnionType) {
                $matched = false;

                foreach ($paramType->getTypes() as $type) {
                    if (self::argMatchesType($arg, $type)) {
                        $matched = true;

                        break;
                    }
                }

                if (!$matched) {
                    return false;
                }
            } else if (!self::argMatchesType($arg, $paramType)) {
                return false;
            }
        }

        return true;
    }
    /**
     * Checks if a single argument matches a reflected type.
     *
     * @param mixed $arg The argument value.
     * @param \ReflectionNamedType $type The type to check against.
     *
     * @return bool
     */
    private static function argMatchesType($arg, \ReflectionNamedType $type): bool {
        $typeName = $type->getName();

        if ($arg === null) {
            return $type->allowsNull();
        }

        if ($type->isBuiltin()) {
            return match ($typeName) {
                'string' => is_string($arg),
                'int' => is_int($arg),
                'float' => is_float($arg) || is_int($arg),
                'bool' => is_bool($arg),
                'array' => is_array($arg),
                'callable' => is_callable($arg),
                'mixed' => true,
                default => false,
            };
        }

        return $arg instanceof $typeName;
    }
    /**
     * Extracts the namespace declaration from a PHP file.
     *
     * @param string $filePath Absolute path to the PHP file.
     *
     * @return string|null The namespace, or null if not found.
     */
    private static function extractNamespace(string $filePath): ?string {
        $content = file_get_contents($filePath);

        if ($content !== false && preg_match('/^\s*namespace\s+([^;{]+)/m', $content, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
