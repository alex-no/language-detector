<?php
declare(strict_types=1);

namespace LanguageDetector\Infrastructure\Adapters\Yii2;
/**
 * YiiLanguageRepository.php
 * This file is part of LanguageDetector package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @package LanguageDetector\Infrastructure\Adapters\Yii2
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 * @copyright 2025 Oleksandr Nosov
 */
use yii\db\Connection;
use yii\db\Query;
use LanguageDetector\Domain\Contracts\LanguageRepositoryInterface;

class YiiLanguageRepository implements LanguageRepositoryInterface
{
    /**
     * YiiLanguageRepository constructor.
     * @param Connection $db
     * @param string $table
     * @param string $codeField
     * @param string $enabledField
     * @param string $orderField
     */
    public function __construct(
        private Connection $db,
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
            $rows = (new Query())
                ->select([$this->codeField])
                ->from($this->table)
                ->where([$this->enabledField => 1])
                ->orderBy([$this->orderField => SORT_ASC])
                ->all($this->db);

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
