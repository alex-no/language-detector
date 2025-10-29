<?php
namespace LanguageDetector\Adapters\Laravel;
/**
 * LaravelLanguageRepository.php
 * This file is part of LanguageDetector package.
 * (c) Your Name <Oleksandr Nosov>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license    MIT
 * @package    LanguageDetector\Adapters\Laravel
 * @author     Your Name <Oleksandr Nosov>
 */
use LanguageDetector\Core\Contracts\LanguageRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LaravelLanguageRepository implements LanguageRepositoryInterface
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
            $codes = array_map(fn($c) => Str::lower(substr((string)$c, 0, 2)), $rows);
            return array_values(array_filter(array_unique($codes), fn($v) => $v !== ''));
        } catch (\Throwable $e) {
            return [];
        }
    }
}
