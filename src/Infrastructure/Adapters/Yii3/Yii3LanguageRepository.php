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
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Query\Query;

class Yii3LanguageRepository implements LanguageRepositoryInterface
{
    /**
     * Yii3LanguageRepository constructor.
     * @param ConnectionInterface $db
     * @param string $table
     * @param string $codeField
     * @param string $enabledField
     * @param string $orderField
     */
    public function __construct(
        private ConnectionInterface $db,
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
            $query = (new Query($this->db))
                ->select([$this->codeField])
                ->from($this->table)
                ->where([$this->enabledField => 1])
                ->orderBy([$this->orderField => SORT_ASC]);

            $rows = $query->all();

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
