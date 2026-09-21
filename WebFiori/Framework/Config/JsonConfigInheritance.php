<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 */
namespace WebFiori\Framework\Config;

use WebFiori\Framework\Exceptions\ConfigurationException;

/**
 * Handles JSON config file composition via the `extends` key.
 *
 * Create one instance per config resolution (JsonDriver creates a fresh
 * instance in initialize()). Instance state is not shared across requests.
 *
 * Resolution rules:
 * - Child wins over extended files.
 * - `extends` is an array; bases are merged left-to-right, child on top.
 * - Cycles throw ConfigurationException with the cycle path.
 * - Diamonds (shared base) are allowed; the base is resolved once and reused.
 * - Paths are relative to the extending file.
 *
 * @since 3.1.0
 * @see ADR-0053
 */
class JsonConfigInheritance {
    /**
     * Default merge strategy per top-level config section.
     * Mirrors the AgentProfile inheritance model (ADR-0045).
     *
     * @var array<string, string>
     */
    private static array $defaultStrategies = [
        'env-vars' => 'merge',
        'database-connections' => 'merge',
        'smtp-connections' => 'merge',
        'app-names' => 'merge',
        'app-descriptions' => 'merge',
        'base-url' => 'replace',
        'theme' => 'replace',
        'primary-lang' => 'replace',
        'name-separator' => 'replace',
        'home-page' => 'replace',
        'scheduler-password' => 'replace',
        'version-info' => 'replace',
        'titles' => 'merge',
        'write-targets' => 'replace',
    ];

    /**
     * Cache of already-resolved files — used to handle diamond dependencies
     * without re-reading or re-merging the same file twice.
     *
     * @var array<string, array<string, mixed>>
     */
    private array $resolved = [];
    /**
     * Current DFS stack of real paths — used to detect cycles.
     *
     * @var string[]
     */
    private array $visited = [];

    /**
     * Resolves a JSON config file, applying any `extends` inheritance.
     *
     * @param string $entryPath Absolute path to the main config file.
     *
     * @return array<string, mixed> The fully merged config array, with
     *         `extends`, `inheritance_strategy`, and `write-targets` stripped.
     *
     * @throws ConfigurationException On circular inheritance or missing files.
     */
    public function resolve(string $entryPath): array {
        return $this->resolveFile($entryPath);
    }

    /**
     * Merges a child section set on top of a base section set using per-section
     * strategies.
     *
     * @param array<string, mixed> $base The base data.
     * @param array<string, mixed> $child The child data (wins on conflict).
     * @param array<string, string> $strategyOverrides Per-section strategy overrides.
     *
     * @return array<string, mixed> The merged data.
     */
    private function mergeSections(array $base, array $child, array $strategyOverrides): array {
        $allKeys = array_unique(array_merge(array_keys($base), array_keys($child)));
        $result = $base;

        foreach ($allKeys as $key) {
            if (!array_key_exists($key, $child)) {
                // Key only in base — keep as-is.
                continue;
            }

            $strategy = $strategyOverrides[$key]
                ?? self::$defaultStrategies[$key]
                ?? (is_array($child[$key] ?? null) ? 'merge' : 'replace');

            if ($strategy === 'merge') {
                $baseVal = $base[$key] ?? [];
                $childVal = $child[$key];

                if (is_array($baseVal) && is_array($childVal)) {
                    // Deep merge: child keys override base keys.
                    $result[$key] = array_merge($baseVal, $childVal);
                } else {
                    // One side isn't an array — child wins.
                    $result[$key] = $childVal;
                }
            } else {
                // replace — child wins wholesale.
                $result[$key] = $child[$key];
            }
        }

        return $result;
    }

    /**
     * Recursively resolves a single config file, merging it with its bases.
     *
     * @param string $path Absolute path to the file being resolved.
     * @param array<string, mixed>|null $childData Data from the child file to
     *        merge on top after resolving this file's own bases.
     *
     * @return array<string, mixed>
     *
     * @throws ConfigurationException
     */
    private function resolveFile(string $path, ?array $childData = null): array {
        $realPath = realpath($path);

        if ($realPath === false) {
            throw new ConfigurationException("Config file not found: '$path'.");
        }

        // Diamond: already resolved — return cached result.
        if (isset($this->resolved[$realPath]) && $childData === null) {
            return $this->resolved[$realPath];
        }

        // Cycle detection.
        if (in_array($realPath, $this->visited, true)) {
            $chain = implode(' → ', array_map('basename', $this->visited));

            throw new ConfigurationException(
                "Circular config inheritance detected: $chain → ".basename($realPath)
            );
        }

        $this->visited[] = $realPath;

        $raw = file_get_contents($realPath);

        if ($raw === false) {
            array_pop($this->visited);

            throw new ConfigurationException("Failed to read config file: '$realPath'.");
        }

        $data = json_decode($raw, true);

        if (!is_array($data)) {
            array_pop($this->visited);

            throw new ConfigurationException("Invalid JSON in config file: '$realPath'.");
        }

        // Resolve bases if this file has an extends key.
        if (isset($data['extends']) && $data['extends'] !== [] && $data['extends'] !== '') {
            $bases = is_array($data['extends']) ? $data['extends'] : [$data['extends']];
            $strategies = $data['inheritance_strategy'] ?? [];
            unset($data['extends'], $data['inheritance_strategy']);
            $baseDir = dirname($realPath);

            // Start with an empty base, merge each base file left-to-right.
            $merged = [];

            foreach ($bases as $baseRef) {
                $basePath = $baseDir.DIRECTORY_SEPARATOR.$baseRef;
                $baseData = $this->resolveFile($basePath);
                $merged = $this->mergeSections($merged, $baseData, $strategies);
            }

            // Apply this file's own data on top (child wins).
            $data = $this->mergeSections($merged, $data, $strategies);
        } else {
            unset($data['extends'], $data['inheritance_strategy']);
        }

        // Apply child data on top if provided.
        if ($childData !== null) {
            $childStrategies = $childData['inheritance_strategy'] ?? [];
            unset($childData['extends'], $childData['inheritance_strategy']);
            $data = $this->mergeSections($data, $childData, $childStrategies);
        }

        array_pop($this->visited);

        // Cache for diamond reuse (only when no child data, i.e. standalone resolution).
        if ($childData === null) {
            $this->resolved[$realPath] = $data;
        }

        return $data;
    }
}
