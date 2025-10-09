<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2024 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Autoload;

/**
 * A class that contains the names of indices that are used by loaded class info array.
 *
 */
class ClassInfo {
    /**
     * A constant that represents the value 'cached' of class.
     */
    const CACHED = 'loaded-from-cache';
    /**
     * A constant that represents the value 'name' of class.
     */
    const NAME = 'class-name';
    /**
     * A constant that represents the value 'namespace' of class.
     */
    const NS = 'namespace';
    /**
     * A constant that represents the value 'path' of class.
     */
    const PATH = 'path';
}
