<?php
namespace LanguageDetector\Adapters\Yii2;
/**
 * YiiLanguageRepository.php
 *
 * This file is part of LanguageDetector package.
 *
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Adapters\Yii2
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
use LanguageDetector\Core\Contracts\LanguageRepositoryInterface;
use yii\db\Connection;
use yii\db\Query;

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
        return (new Query())
            ->select([$this->codeField])
            ->from($this->table)
            ->where([$this->enabledField => 1])
            ->orderBy([$this->orderField => SORT_ASC])
            ->column($this->db);
    }
}
