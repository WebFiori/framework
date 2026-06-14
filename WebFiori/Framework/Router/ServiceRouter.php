<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Router;

use ReflectionClass;
use WebFiori\Container\ContainerFacade;
use WebFiori\Framework\App;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\RequestProcessor;
use WebFiori\Http\WebService;
use WebFiori\Http\WebServicesManager;

/**
 * Discovers and registers API routes from a namespace.
 *
 * Supports three types of classes:
 * - Classes with #[RestController] attribute (uses attribute name or derived name)
 * - WebService subclasses without attribute (uses getName())
 * - WebServicesManager subclasses (registered as manager routes)
 *
 * @author Ibrahim
 */
class ServiceRouter {
    /**
     * @var array Discovered service map for introspection.
     */
    private static array $discovered = [];

    /**
     * Discover and register routes for all services in a namespace.
     *
     * @param string $namespace Fully qualified namespace (e.g. 'App\\Apis').
     * @param string $basePath URL prefix (e.g. '/apis').
     * @param array $routeOptions Shared route options applied to all routes.
     * @param string|null $directory Directory to scan. If null, derived from namespace relative to ROOT_PATH.
     *
     * @return int Number of routes registered.
     */
    public static function discover(string $namespace, string $basePath, array $routeOptions = [], ?string $directory = null): int {
        $dir = $directory ?? self::namespaceToPath($namespace);
        $map = self::scanNamespace($namespace, $dir);
        $count = 0;
        $basePath = rtrim($basePath, '/');

        foreach ($map as $name => $entry) {
            $class = $entry['class'];
            $type = $entry['type'];
            $path = $basePath . '/' . $name;

            $options = array_merge($routeOptions, [
                RouteOption::PATH => $path,
            ]);

            if ($type === 'manager') {
                $options[RouteOption::TO] = $class;
            } else {
                $options[RouteOption::TO] = self::createServiceClosure($class);
            }

            Router::api($options);
            self::$discovered[$name] = [
                'class' => $class,
                'type' => $type,
                'path' => $path,
            ];
            $count++;
        }

        return $count;
    }

    /**
     * Returns all discovered services.
     *
     * @return array Associative array keyed by name with class, type, and path.
     */
    public static function getDiscovered(): array {
        return self::$discovered;
    }

    /**
     * Reset discovered services.
     */
    public static function reset(): void {
        self::$discovered = [];
    }

    /**
     * Register a dynamic namespace route that resolves services at request time.
     *
     * Usage:
     * ```php
     * ServiceRouter::dynamic('App\\Apis', '/apis/{controller}', [...options]);
     * ```
     *
     * @param string $namespace Namespace to search for services.
     * @param string $path Route path with {controller} parameter.
     * @param array $routeOptions Shared route options (middleware, etc.).
     * @param string|null $directory Optional directory override.
     */
    public static function dynamic(string $namespace, string $path, array $routeOptions = [], ?string $directory = null): void {
        $options = array_merge($routeOptions, [
            RouteOption::PATH => $path,
            RouteOption::TO => function () use ($namespace, $directory) {
                $controllerName = App::getRequest()->getParam('controller');

                if ($controllerName === null) {
                    $controllerName = $_GET['controller'] ?? null;
                }

                if ($controllerName === null) {
                    App::getResponse()->setCode(404);
                    App::getResponse()->write(json_encode([
                        'message' => 'Controller parameter missing.',
                        'type' => 'error'
                    ]));

                    return;
                }

                self::handle($controllerName, $namespace, $directory);
            },
        ]);

        Router::api($options);
    }

    /**
     * Handle a dynamic controller request.
     *
     * @param string $controllerName The controller name from the URL.
     * @param string $namespace Namespace to search.
     * @param string|null $directory Optional directory to scan.
     */
    public static function handle(string $controllerName, string $namespace, ?string $directory = null): void {
        $dir = $directory ?? self::namespaceToPath($namespace);
        $map = self::scanNamespace($namespace, $dir);

        if (isset($map[$controllerName])) {
            $entry = $map[$controllerName];

            if ($entry['type'] === 'manager') {
                $class = $entry['class'];
                $manager = new $class();
                $manager->process();
            } else {
                $class = $entry['class'];
                $service = ContainerFacade::has($class)
                    ? ContainerFacade::make($class)
                    : new $class();
                (new RequestProcessor())->process($service, App::getRequest());
            }
        } else {
            App::getResponse()->setCode(404);
            App::getResponse()->write(json_encode([
                'message' => 'Service not found.',
                'type' => 'error'
            ]));
        }
    }

    /**
     * Scan a namespace directory for routable classes.
     *
     * @param string $namespace The namespace to scan.
     * @param string $dir The directory to scan.
     *
     * @return array Map of name => ['class' => FQCN, 'type' => 'service'|'manager']
     */
    private static function scanNamespace(string $namespace, string $dir): array {
        $map = [];

        if (!is_dir($dir)) {
            return $map;
        }

        $files = glob($dir . DIRECTORY_SEPARATOR . '*.php');

        if ($files === false) {
            return $map;
        }

        foreach ($files as $file) {
            $className = basename($file, '.php');
            $fqcn = $namespace . '\\' . $className;

            if (!class_exists($fqcn)) {
                continue;
            }

            $ref = new ReflectionClass($fqcn);

            if ($ref->isAbstract() || $ref->isInterface()) {
                continue;
            }

            // Priority 1: #[RestController] attribute
            $attrs = $ref->getAttributes(RestController::class);

            if (!empty($attrs)) {
                $attr = $attrs[0]->newInstance();
                $name = !empty($attr->name) ? $attr->name : self::deriveNameFromClass($className);
                $map[$name] = ['class' => $fqcn, 'type' => 'service'];

                continue;
            }

            // Priority 2: WebServicesManager subclass
            if ($ref->isSubclassOf(WebServicesManager::class)) {
                $name = self::deriveNameFromClass($className);
                $map[$name] = ['class' => $fqcn, 'type' => 'manager'];

                continue;
            }

            // Priority 3: WebService subclass without attribute
            if ($ref->isSubclassOf(WebService::class)) {
                $instance = new $fqcn();
                $name = $instance->getName();

                if (!empty($name)) {
                    $map[$name] = ['class' => $fqcn, 'type' => 'service'];
                }
            }
        }

        return $map;
    }

    /**
     * Convert namespace to filesystem path.
     */
    private static function namespaceToPath(string $namespace): string {
        return ROOT_PATH . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
    }

    /**
     * Derive route name from class name.
     * OrderService → orders, ProductManager → product
     */
    private static function deriveNameFromClass(string $className): string {
        $name = preg_replace('/(Service|Manager|Controller)$/', '', $className);

        return strtolower($name);
    }

    /**
     * Create a closure that instantiates and processes a service.
     */
    private static function createServiceClosure(string $class): callable {
        return function () use ($class) {
            $service = ContainerFacade::has($class)
                ? ContainerFacade::make($class)
                : new $class();
            (new RequestProcessor())->process($service, App::getRequest());
        };
    }
}
