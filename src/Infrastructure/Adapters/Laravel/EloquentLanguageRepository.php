<?php
namespace LanguageDetector\Adapters\Laravel;
/**
 * An example LanguageRepository implementation using an Eloquent model that has columns:
 * - code (string) - language code like 'en', 'uk'
 * - is_active (bool/integer)
 *
 * You may pass either a class-string of the model or an instance that supports the query methods used below.
 *
 * This file is part of LanguageDetector package.
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Adapters\Laravel
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
use LanguageDetector\Domain\Language\Contracts\LanguageRepositoryInterface as CoreLanguageRepositoryInterface;
use Illuminate\Support\Str;

class EloquentLanguageRepository implements CoreLanguageRepositoryInterface
{
    /**
     * @param string|object $model Class name of Eloquent model or model instance.
     */
    public function __construct(
        private $model
    ) {}

    /**
     * @inheritDoc
     */
    public function getEnabledLanguageCodes(): array
    {
        try {
            $queryable = is_string($this->model) ? ($this->model)::query() : $this->model->newQuery();
            $rows = $queryable->where('is_active', 1)->pluck('code')->all();
            $codes = array_map(fn($c) => Str::lower(substr((string)$c, 0, 2)), (array)$rows);
            // remove duplicates and empty
            $codes = array_values(array_filter(array_unique($codes), fn($v) => $v !== ''));
            return $codes;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
