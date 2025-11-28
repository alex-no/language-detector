<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii3;
/**
 * Yii3LanguageRepository.php
 * This file is part of LanguageDetector package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Yii3
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use LanguageDetector\Domain\Contracts\LanguageRepositoryInterface;

class Yii3LanguageRepository implements LanguageRepositoryInterface
{
    /**
     * Yii3LanguageRepository constructor.
     * @param \PDO $pdo
     * @param string $table
     * @param string $codeField
     * @param string $enabledField
     * @param string $orderField
     */
    public function __construct(
        private \PDO $pdo,
        private string $table = 'language',
        private string $codeField = 'code',
        private string $enabledField = 'is_enabled',
        private string $orderField = 'order',
    ) {}

    /**
     * @inheritDoc
     */
    public function getEnabledLanguageCodes(): array
    {
        try {
            $sql = sprintf(
                'SELECT `%s` FROM `%s` WHERE `%s` = 1 ORDER BY `%s` ASC',
                $this->codeField,
                $this->table,
                $this->enabledField,
                $this->orderField
            );

            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $codes = [];
            foreach ($rows as $r) {
                if (isset($r[$this->codeField])) {
                    $codes[] = (string)$r[$this->codeField];
                }
            }
            return array_values(array_filter($codes, fn($v) => $v !== ''));
        } catch (\Throwable) {
            return [];
        }
    }
}
