<?php
namespace LanguageDetector\Application;

use LanguageDetector\Domain\Contracts\SourceInterface;
use LanguageDetector\Domain\Sources\{
    PostSource, GetSource, PathSource, UserProfileSource,
    SessionSource, CookieSource, HeaderSource, DefaultSource
};

class SourceFactory
{
    const HEADER_KEY = 'Accept-Language';
    /**
     * @param array $config Configuration options:
     *                      - paramName: string Parameter name for sources (default: 'lang')
     *                      - default: string Default language code (default: 'en')
     *                      - pathSegmentIndex: int Index of path segment for PathSource (default: 0)
     */
    public function __construct(private array $config) {}

    /**
     * Get the mapping of source keys to their factory functions
     * @return array<string, callable> Map of source keys to factory functions
     */
    private function getMap(): array
    {
        $paramName = $this->config['paramName'] ?? 'lang';
        return [
            'post'    => fn() => new PostSource($paramName),
            'get'     => fn() => new GetSource($paramName),
            'path'    => fn() => new PathSource($this->config['pathSegmentIndex'] ?? 0),
            'user'    => fn() => new UserProfileSource($paramName),
            'session' => fn() => new SessionSource($paramName),
            'cookie'  => fn() => new CookieSource($paramName),
            'header'  => fn() => new HeaderSource(self::HEADER_KEY),
            'default' => fn() => new DefaultSource($this->config['default'] ?? 'en'),
        ];
    }

    /**
     * Create source instances based on provided keys
     * @param string[] $keys
     * @return SourceInterface[]
     */
    public function make(array $keys): array
    {
        $map = $this->getMap();
        $sources = [];

        foreach ($keys as $key) {
            if (!isset($map[$key])) {
                throw new \InvalidArgumentException("Unknown language source key: $key");
            }
            $sources[] = $map[$key](); // call the factory function
        }

        return $sources;
    }
}
