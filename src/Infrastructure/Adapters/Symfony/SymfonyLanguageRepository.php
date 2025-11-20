<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Symfony;
/**
 * Language repository using Doctrine DBAL connection
 * to fetch enabled languages from database.
 * Assumes a table with language codes and an enabled flag.
 * Can be configured with table and field names.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Symfony
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use Doctrine\DBAL\Connection;
use LanguageDetector\Domain\Contracts\LanguageRepositoryInterface;

class SymfonyLanguageRepository implements LanguageRepositoryInterface
{
    /**
     * Constructor.
     * @param Connection $conn Doctrine DBAL connection.
     * @param string $table Table name.
     * @param string $codeField Language code field name.
     * @param string $enabledField Enabled flag field name.
     * @param string $orderField Order field name.
     */
    public function __construct(
        private Connection $conn,
        private string $table = 'language',
        private string $codeField = 'code',
        private string $enabledField = 'is_enabled',
        private string $orderField = 'order',
    ) {}

    /**
     * Get enabled language codes from the database.
     * @return string[] Array of enabled language codes.
     */
    public function getEnabledLanguageCodes(): array
    {
        try {
            $sql = sprintf(
                'SELECT %s FROM %s WHERE %s = :enabled ORDER BY %s ASC',
                $this->codeField,
                $this->table,
                $this->enabledField,
                $this->orderField
            );
            $rows = $this->conn->fetchAllAssociative($sql, ['enabled' => 1]);
            $codes = [];
            foreach ($rows as $r) {
                $codes[] = (string)($r[$this->codeField] ?? '');
            }
            return array_values(array_filter($codes, fn($v) => $v !== ''));
        } catch (\Throwable) {
            return [];
        }
    }
}
