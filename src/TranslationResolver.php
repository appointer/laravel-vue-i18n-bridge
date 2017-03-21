<?php

namespace Appointer\VueTranslation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\LoaderInterface;
use SplFileInfo;

class TranslationResolver
{
    /**
     * Internal helper to identify the global namespace.
     */
    const NAMESPACE_GLOBAL = 'global';

    /**
     * The laravel translation loader.
     *
     * @var LoaderInterface
     */
    private $loader;

    /**
     * Filesystem adapter instance.
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Default path for translation files.
     *
     * @var string
     */
    private $path;

    /**
     * Fallback locale for the translation.
     *
     * @var string
     */
    private $locale;

    /**
     * TranslationLoader constructor.
     *
     * @param LoaderInterface $loader
     * @param Filesystem $filesystem
     * @param $path
     */
    function __construct(LoaderInterface $loader, Filesystem $filesystem, $path)
    {
        $this->loader = $loader;
        $this->filesystem = $filesystem;
        $this->path = $path;
    }

    /**
     * Exposes all translation files of the given locale.
     *
     * @param string $locale
     * @param string $fallbackLocale
     * @return array
     */
    public function expose(string $locale, string $fallbackLocale): array
    {
        // Set the fallback locale.
        $this->locale = $fallbackLocale;

        $keys = [];

        foreach ($this->resolveLanguageFiles($locale) as $namespace => $files) {
            // Since we are not able to set 'null' as a direct
            // asociative array key, we have to transform
            // it back to its original value here.
            if ($namespace === self::NAMESPACE_GLOBAL) {
                $keys = array_merge($keys, $this->load($files, $locale, null));
            } else {
                $keys = array_merge($keys, [
                    $namespace => $this->load($files, $locale, $namespace)
                ]);
            }
        }

        return $keys;
    }

    /**
     * @param array $files
     * @param string $locale
     * @param string|null $namespace
     * @return array
     */
    protected function load(array $files, string $locale, $namespace): array
    {
        $keys = [];

        foreach ($files as $file) {
            // Extract the name of the translation group from the filename.
            $key = $this->keyFromFile($file);

            // Use the native translation loader to load the whole group
            // under the specified namespace. Also we use the
            // fallback locale here if we got an empty
            // result for the  desired locale.
            $locales = $this->loader->load($locale, $key, $namespace) ?:
                $this->loader->load($this->locale, $key, $namespace);

            $keys[$key] = $locales;
        }

        return $keys;
    }

    /**
     * Resolve all language files  and apply
     * white- and blacklist filters.
     *
     * @param string $locale
     * @return array
     */
    protected function resolveLanguageFiles(string $locale): array
    {
        $files = [];

        foreach ($this->getTranslationSources() as $namespace => $path) {
            if ($this->filesystem->exists("$path/$locale")) {
                // Load the files for the desired locale.
                $files[$namespace] = $this->filesystem->allFiles("$path/$locale");
            } elseif ($this->filesystem->exists("$path/$this->locale")) {
                // Load the files for the fallback locale.
                $files[$namespace] = $this->filesystem->allFiles("$path/$this->locale");
            }
        }

        // Apply whitelist first if there is any.
        if ($filtered = $this->applyWhitelistFilter($files)) {
            return $filtered;
        }

        // Apply blacklist if there is any.
        if ($filtered = $this->applyBlacklistFilter($files)) {
            return $filtered;
        }

        return $files;
    }

    /**
     * Merge all sources of translations together.
     *
     * @return array
     */
    protected function getTranslationSources(): array
    {
        // Merge all possible translation sources together.
        // We also consider namespaces from possible
        // third party packages.
        $sources = array_merge($this->loader->namespaces(), [
            self::NAMESPACE_GLOBAL => $this->path
        ]);

        return $sources;
    }

    /**
     * Apply whitelist to translation sources.
     *
     * @param array $sources
     * @return bool|array
     */
    private function applyWhitelistFilter(array $sources)
    {
        if (empty($whitelist = config('vue-translation.whitelist'))) {
            return false;
        }

        return $this->redux($sources, function($collection) use ($whitelist) {
            return $collection->only($whitelist);
        });
    }

    /**
     * Apply blacklist to translation sources.
     *
     * @param array $sources
     * @return bool|array
     */
    private function applyBlacklistFilter(array $sources)
    {
        if (empty($blacklist = config('vue-translation.blacklist'))) {
            return false;
        }

        return $this->redux($sources, function($collection) use ($blacklist) {
            return $collection->except($blacklist);
        });
    }

    /**
     * Reduction helper to use with the specific
     * generated data structure.
     *
     * @param array $sources
     * @param callable $callback
     * @return array
     */
    private function redux(array $sources, callable $callback): array
    {
        foreach ($sources as $namespace => $files) {
            // Remap the array keys to values we can actually filter.
            $collection = collect($files)->mapWithKeys(function ($file) {
                return [$this->keyFromFile($file) => $file];
            });

            // Invoke the manipulation callback.
            $sources[$namespace] = $callback($collection)->toArray();
        }

        return $sources;
    }

    /**
     * Extract the filename without the extension from an
     * SplFileInfo. The result is then used as the
     * translation group key.
     *
     * @param $file
     * @return string
     */
    private function keyFromFile(SplFileInfo $file): string {
        return $file->getBasename('.' . $file->getExtension());
    }
}