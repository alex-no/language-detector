<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Repositories;

use LanguageDetector\Domain\Contracts\LanguageRepositoryInterface;

/**
 * PdoLanguageRepository.php
 * Universal PDO-based language repository.
 * Framework-agnostic implementation using raw PDO.
 *
 * This file is part of LanguageDetector package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Repositories
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
class PdoLanguageRepository implements LanguageRepositoryInterface
{
    /**
     * PdoLanguageRepository constructor.
     * @param \PDO $pdo PDO connection instance
     * @param string $table Database table name containing languages
     * @param string $codeField Field name for language code
     * @param string $enabledField Field name for enabled flag (should contain 1/0)
     * @param string $orderField Field name for sort order
     */
    public function __construct(
        private \PDO $pdo,
        private string $table = 'language',
        private string $codeField = 'code',
        private string $enabledField = 'is_enabled',
        private string $orderField = 'order',
    ) {}

    /**
     * Get enabled language codes from database.
     *
     * @return string[] Array of enabled language codes
     */
    public function getEnabledLanguageCodes(): array
    {
        try {
            // Use sprintf for identifier quoting (backticks for MySQL compatibility)
            $sql = sprintf(
                'SELECT `%s` FROM `%s` WHERE `%s` = 1 ORDER BY `%s` ASC',
                $this->codeField,
                $this->table,
                $this->enabledField,
                $this->orderField
            );

            $stmt = $this->pdo->query($sql);
            if ($stmt === false) {
                return [];
            }

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (!is_array($rows)) {
                return [];
            }

            $codes = [];
            foreach ($rows as $r) {
                if (isset($r[$this->codeField]) && $r[$this->codeField] !== '') {
                    $codes[] = (string)$r[$this->codeField];
                }
            }

            return array_values(array_filter($codes, fn($v) => $v !== ''));
        } catch (\Throwable) {
            return [];
        }
    }
}
