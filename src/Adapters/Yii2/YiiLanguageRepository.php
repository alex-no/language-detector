<?php
namespace LanguageDetector\Adapters\Yii2;

use LanguageDetector\Core\Contracts\LanguageRepositoryInterface;
use yii\db\Connection;
use yii\db\Query;

class YiiLanguageRepository implements LanguageRepositoryInterface
{
    private Connection $db;
    private string $table;
    private string $codeField;
    private string $enabledField;
    private string $orderField;

    public function __construct(Connection $db, string $table = 'language', string $codeField = 'code', string $enabledField = 'is_enabled', string $orderField = 'order')
    {
        $this->db = $db;
        $this->table = $table;
        $this->codeField = $codeField;
        $this->enabledField = $enabledField;
        $this->orderField = $orderField;
    }

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
