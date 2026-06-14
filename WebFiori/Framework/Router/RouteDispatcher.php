<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Router;

use WebFiori\File\File;
use WebFiori\Framework\App;
use WebFiori\Framework\Exceptions\RoutingException;
use WebFiori\Framework\Ui\HTTPCodeView;
use WebFiori\Framework\Ui\WebPage;
use WebFiori\Http\Response;
use WebFiori\Http\WebServicesManager;
use WebFiori\Json\Json;

/**
 * A class responsible for dispatching matched routes to their resources.
 *
 * @author Ibrahim
 */
class RouteDispatcher {
    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    /**
     * Dispatches a matched route.
     *
     * @param RouterUri $route The matched route.
     * @param bool $loadResource Whether to load the resource.
     */
    public function dispatch(RouterUri $route, bool $loadResource): void {
        if ($route->isRequestMethodAllowed((App::getRequest()->getMethod()))) {
            $this->router->setRouteUri($route);

            foreach ($route->getMiddleware() as $mw) {
                $mw->before(App::getRequest(), App::getResponse());

                if (App::getResponse()->getCode() >= 400) {
                    App::getResponse()->send();

                    return;
                }
            }

            if ($route->getType() == Router::API_ROUTE && !defined('API_CALL')) {
                define('API_CALL', true);
            }

            if (is_callable($route->getRouteTo())) {
                if ($loadResource === true) {
                    call_user_func_array($route->getRouteTo(), $route->getClosureParams());
                }
            } else {
                $file = $route->getRouteTo();
                $xFile = '\\'.str_replace("/", "\\", $file);

                if (class_exists($xFile) && $loadResource) {
                    $class = new $xFile();

                    if ($class instanceof WebServicesManager) {
                        $class->process();
                    } else if ($class instanceof WebPage) {
                        $class->render();
                    } else if ($route->getAction() !== null) {
                        $toCall = $route->getAction();
                        $class->$toCall();
                    }
                } else {
                    $routeType = $route->getType();

                    if ($routeType == Router::VIEW_ROUTE || $routeType == Router::CUSTOMIZED || $routeType == Router::API_ROUTE) {
                        $file = ROOT_PATH.$routeType.$this->fixFilePath($file);
                    } else {
                        $file = ROOT_PATH.$this->fixFilePath($file);
                    }

                    if (gettype($file) == 'string' && file_exists($file)) {
                        if ($loadResource === true) {
                            $route->setRoute($file);
                            $this->loadResource($route);
                        }
                    } else {
                        if ($loadResource === true) {
                            $message = 'The resource "'.App::getRequest()->getRequestedURI().'" was available. '
                            .'but its route is not configured correctly. '
                            .'The resource which the route is pointing to was not found.';

                            if (defined('WF_VERBOSE') && WF_VERBOSE) {
                                $message = 'The resource "'.App::getRequest()->getRequestedURI().'" was available. '
                                .'but its route is not configured correctly. '
                                .'The resource which the route is pointing to was not found ('.$file.').';
                            }
                            throw new RoutingException($message);
                        }
                    }
                }
            }
        } else {
            App::getResponse()->setCode(405);

            if (!defined('API_CALL')) {
                $notFoundView = new HTTPCodeView(405);
                $notFoundView->render();
            } else {
                $json = new Json([
                    'message' => 'Request method not allowed.',
                    'type' => 'error'
                ]);
                App::getResponse()->write($json);
            }
        }
    }

    /**
     * Send http 301 response code and redirect the request to non-www URI.
     */
    public function redirectToNonWWW(RouterUri $uriObj): void {
        App::getResponse()->setCode(301);
        $path = '';

        $host = substr($uriObj->getHost(), strpos($uriObj->getHost(), '.'));

        for ($x = 1 ; $x < count($uriObj->getPathArray()) ; $x++) {
            $path .= '/'.$uriObj->getPathArray()[$x];
        }
        $queryString = '';

        if (strlen($uriObj->getQueryString()) > 0) {
            $queryString = '?'.$uriObj->getQueryString();
        }
        $fragment = '';

        if (strlen($uriObj->getFragment()) > 0) {
            $fragment = '#'.$uriObj->getFragment();
        }
        $port = '';

        if (strlen($uriObj->getPort()) > 0) {
            $port = ':'.$uriObj->getPort();
        }
        App::getResponse()->addHeader('location', $uriObj->getScheme().'://'.$host.$port.$path.$queryString.$fragment);
        App::getResponse()->send();
    }

    private function fixFilePath(string $path): string {
        if (strlen($path) != 0 && $path != '/') {
            $path00 = str_replace('/', DS, $path);
            $path01 = str_replace('\\', DS, $path00);

            if ($path01[strlen($path01) - 1] == DS || $path01[0] == DS) {
                while ($path01[0] == DS || $path01[strlen($path01) - 1] == DS) {
                    $path01 = trim($path01, DS);
                }
                $path01 = DS.$path01;
            }

            if ($path01[0] != DS) {
                $path01 = DS.$path01;
            }
            $path = $path01;
        } else {
            $path = DS;
        }

        return $path;
    }

    private function getFileDirAndName(string $absDir): array {
        $explode = explode(DS, $absDir);
        $fileName = $explode[count($explode) - 1];
        $dir = substr($absDir, 0, strlen($absDir) - strlen($fileName));

        return [
            'name' => $fileName,
            'dir' => $dir
        ];
    }

    private function loadResource(RouterUri $route): void {
        $file = $route->getRouteTo();
        $info = $this->getFileDirAndName($file);
        $fileObj = new File($info['name'], $info['dir']);
        $fileObj->read();

        if ($fileObj->getMIME() === 'text/plain') {
            $classNamespace = require_once $file;

            if (gettype($classNamespace) == 'string') {
                if (strlen($classNamespace) == 0) {
                    $constructor = '\\'.$route->getClassName();
                } else {
                    $constructor = '\\'.$classNamespace.'\\'.$route->getClassName();
                }

                if (class_exists($constructor)) {
                    $instance = new $constructor();

                    if ($instance instanceof WebServicesManager) {
                        if (!defined('API_CALL')) {
                            define('API_CALL', true);
                        }
                        $instance->process();
                    } else if ($instance instanceof WebPage) {
                        $instance->render();
                    } else if ($route->getAction() !== null) {
                        $toCall = $route->getAction();
                        $instance->$toCall();
                    }
                }
            }
        } else {
            $fileObj->view();
        }
    }
}
