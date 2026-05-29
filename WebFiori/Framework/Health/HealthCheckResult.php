<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026 WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Health;

/**
 * Value object representing the result of a health check.
 */
class HealthCheckResult {
    private string $status;
    private ?string $reason;
    private array $meta;

    private function __construct(string $status, ?string $reason = null, array $meta = []) {
        $this->status = $status;
        $this->reason = $reason;
        $this->meta = $meta;
    }
    /**
     * Create a passing result.
     *
     * @param array $meta Optional metadata (e.g., latency_ms).
     *
     * @return self
     */
    public static function ok(array $meta = []): self {
        return new self('ok', null, $meta);
    }
    /**
     * Create a failing result.
     *
     * @param string $reason The failure reason.
     * @param array $meta Optional metadata.
     *
     * @return self
     */
    public static function fail(string $reason, array $meta = []): self {
        return new self('fail', $reason, $meta);
    }
    /**
     * Returns the status ('ok' or 'fail').
     *
     * @return string
     */
    public function getStatus(): string {
        return $this->status;
    }
    /**
     * Returns the failure reason, or null if ok.
     *
     * @return string|null
     */
    public function getReason(): ?string {
        return $this->reason;
    }
    /**
     * Returns metadata.
     *
     * @return array
     */
    public function getMeta(): array {
        return $this->meta;
    }
    /**
     * Converts to array for JSON serialization.
     *
     * @return array
     */
    public function toArray(): array {
        $arr = ['status' => $this->status];

        if ($this->reason !== null) {
            $arr['reason'] = $this->reason;
        }

        if (!empty($this->meta)) {
            $arr = array_merge($arr, $this->meta);
        }

        return $arr;
    }
}
