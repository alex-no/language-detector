<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Laravel;
/**
 * Language repository using Laravel DB/Query Builder
 * implements LanguageRepositoryInterface.
 * Fetches enabled language codes from database.
 * Assumes a table with language codes and enabled flag.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Laravel
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use Illuminate\Database\ConnectionInterface;
use LanguageDetector\Domain\Contracts\LanguageRepositoryInterface;

class LaravelLanguageRepository implements LanguageRepositoryInterface
{
    /**
     * Constructor
     * @param ConnectionInterface $db Laravel database connection
     * @param string $table Table name containing languages
     * @param string $codeField Field name for language code
     * @param string $enabledField Field name for enabled flag
     * @param string $orderField Field name for ordering
     */
    public function __construct(
        private ConnectionInterface $db,
        private string $table = 'language',
        private string $codeField = 'code',
        private string $enabledField = 'is_enabled',
        private string $orderField = 'order',
    ) {}

    /**
     * Get enabled language codes from database.
     * @return string[] Array of enabled language codes
     */
    public function getEnabledLanguageCodes(): array
    {
        try {
            $rows = $this->db->table($this->table)
                ->select($this->codeField)
                ->where($this->enabledField, 1)
                ->orderBy($this->orderField, 'asc')
                ->get();

            $codes = [];
            foreach ($rows as $r) {
                // row may be stdClass
                $codes[] = (string)($r->{$this->codeField} ?? '');
            }
            return array_values(array_filter($codes, fn($v) => $v !== ''));
        } catch (\Throwable) {
            return [];
        }
    }
}
