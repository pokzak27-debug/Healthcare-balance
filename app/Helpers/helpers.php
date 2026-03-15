<?php

if (! function_exists('can_access_dossier')) {
    function can_access_dossier(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role?->slug ?? '', ['super_admin', 'doctor'], true);
    }
}

if (! function_exists('format_dh')) {
    /**
     * Formate un montant en dirhams marocains (DH).
     *
     * @param  float|int|string  $amount  Montant à formater
     * @param  bool  $showSymbol  Afficher le symbole DH
     */
    function format_dh(float|int|string $amount, bool $showSymbol = true): string
    {
        $amount = (float) $amount;
        $decimals = config('currency.decimals', 2);
        $thousands = config('currency.thousands_sep', ' ');
        $decimal = config('currency.decimal_sep', ',');

        $formatted = number_format($amount, $decimals, $decimal, $thousands);

        return $showSymbol ? $formatted . ' DH' : $formatted;
    }
}
