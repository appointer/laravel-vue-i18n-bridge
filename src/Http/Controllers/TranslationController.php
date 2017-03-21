<?php

namespace Appointer\VueTranslation\Http\Controllers;


use Appointer\VueTranslation\TranslationResolver;

class TranslationController
{
    /**
     * Show the application dashboard.
     *
     * @param null $locale
     * @param TranslationResolver $resolver
     * @return \Illuminate\Http\Response
     */
    public function show($locale = null, TranslationResolver $resolver)
    {
        $locale = $locale ?? config('app.locale');
        $fallbackLocale = config('app.fallback_locale');

        return [
            $locale => $resolver->expose($locale, $fallbackLocale)
        ];
    }
}