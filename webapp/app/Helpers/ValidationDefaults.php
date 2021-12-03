<?php

namespace App\Helpers;

use Illuminate\Validation\Rule;

use \App\Models\Currency;
use \App\Models\Community;
use \App\Models\Economy;
use \App\Perms\AppRoles;
use \App\Perms\BarRoles;
use \App\Perms\CommunityRoles;

/**
 * Class ValidationDefaults.
 *
 * This class defines the default validation configurations to use.
 *
 * @package App\Helpers
 */
class ValidationDefaults {

    /**
     * A regular name.
     * For example, a community or bar name.
     */
    const NAME = 'string|min:2|max:256';

    /**
     * Email validation configuration.
     */
    const EMAIL = 'string|email|max:255';

    /**
     * A regular name.
     * For example, a community or bar name.
     */
    const USER_NAME = self::NAME . '|max:64|not_regex:/[:\.\n\r]/';

    /**
     * User password validation configuration.
     */
    const USER_PASSWORD = 'string|min:6|max:4096';

    /**
     * Simple password validation configuration.
     * This is used for community and bar passwords, and are less constrained.
     */
    const SIMPLE_PASSWORD = 'string|min:4|max:4096';

    /**
     * Email reset token validation configuration.
     */
    const EMAIL_RESET_TOKEN = 'string|alpha_num|size:32';

    /**
     * Email reset token validation configuration.
     */
    const EMAIL_VERIFY_TOKEN = self::EMAIL_RESET_TOKEN;

    /**
     * Password reset token validation configuration.
     */
    const PASSWORD_RESET_TOKEN = 'string|size:32';

    /**
     * First name validation configuration.
     */
    const FIRST_NAME = self::USER_NAME;

    /**
     * Last name validation configuration.
     */
    const LAST_NAME = self::USER_NAME;

    /**
     * A nickname.
     */
    const NICKNAME = self::NAME . '|max:32|not_regex:/[:\.\n\r]/';

    /**
     * User tags list.
     */
    const USER_TAGS = 'string|max:128';

    /**
     * Product tags list.
     */
    const PRODUCT_TAGS = 'string|max:255';

    /**
     * Base slug validation configuration.
     */
    const SLUG = 'string|alpha_dash|min:2|max:64|regex:' . self::SLUG_REGEX;

    /**
     * A regex for slug validation.
     */
    const SLUG_REGEX = '/^[a-zA-Z_][a-zA-Z0-9_-]{1,64}$/';

    /**
     * A protection code for a community or bar.
     */
    const CODE = 'string|min:2|max:4096';

    /**
     * A description.
     */
    const DESCRIPTION = 'string|max:2048';

    /**
     * A currency symbol.
     */
    const CURRENCY_SYMBOL = 'string|min:1|max:25';

    /**
     * A currency format.
     */
    const CURRENCY_FORMAT = 'string|min:1|max:50';

    /**
     * A price value, with two optional decimal digits, may be zero.
     * It cannot be negative.
     */
    const PRICE = 'regex:/^(\d{0,8}([,.]\d{1,2})?)?$/';

    /**
     * A price value, with two optional decimal digits, may be negative,
     * positive or zero.
     */
    const PRICE_SIGNED = 'regex:/^(-?\d{0,8}([,.]\d{1,2})?)?$/';

    /**
     * A price value, with two optional decimal digits, may only be positive and
     * not zero.
     */
    const PRICE_POSITIVE = 'regex:/^('
            . '[1-9][0-9]{0,7}([,.]\d{1,2})?|'
            . '\d{0,8}[,.]([0-9][1-9]|[1-9][0-9]?)'
        . ')?$/';

    /**
     * A price value, with two optional decimal digits, may be positve and
     * negative but not zero.
     * not zero.
     */
    const PRICE_NOT_ZERO = 'regex:/^-?('
            . '[1-9][0-9]{0,7}([,.]\d{1,2})?|'
            . '\d{0,8}[,.]([0-9][1-9]|[1-9][0-9]?)'
        . ')?$/';

    /**
     * bunq API token.
     */
    const BUNQ_TOKEN = 'string|alpha_dash|size:64';

    /**
     * Build the community slug validation configuration.
     *
     * @param int|null $community The community this configuration is built for.
     * @return string The validation configuration.
     */
    public static function communitySlug($community = null) {
        // Build the uniqueness rule, ignore the current if given
        $unique = Rule::unique('community', 'slug');
        if(!empty($community))
            $unique = $unique->ignore($community->id);

        return self::SLUG . '|' . $unique;
    }

    /**
     * Build the bar slug validation configuration.
     *
     * @param int|null $bar The bar this configuration is built for.
     * @return string The validation configuration.
     */
    public static function barSlug($bar = null) {
        // Build the uniqueness rule, ignore the current if given
        $unique = Rule::unique('bar', 'slug');
        if(!empty($bar))
            $unique = $unique->ignore($bar->id);

        return self::SLUG . '|' . $unique;
    }

    /**
     * Build the community economy validation configuration.
     *
     * This checks whether the submitted economy is part of the given community.
     *
     * @param Community $community The community this configuration is built for.
     * @return Rule The validation rule.
     */
    public static function communityEconomy(Community $community) {
        return Rule::exists('economy', 'id')
            ->where(function($query) use($community) {
                // Scope to the current community
                return $query->where('community_id', $community->id);
            });
    }

    /**
     * Build the economy inventory validation configuration.
     *
     * This checks whether the submitted inventory is part of the given economy.
     *
     * @param Economy $economy The economy this configuration is built for.
     * @return Rule The validation rule.
     */
    public static function economyInventory(Economy $economy) {
        return Rule::exists('inventory', 'id')
            ->where(function($query) use($economy) {
                // Scope to the current economy
                return $query->where('economy_id', $economy->id);
            });
    }

    /**
     * A validator for a currency ID in an economy.
     *
     * Note: this function returns an array of validation rules.
     *
     * @param Economy $economy The economy the currency ID must be in.
     *
     * @return Array An array of validation rules.
     */
    // TODO: do not return array here
    public static function currency(Economy $economy) {
        return [
            Rule::exists('currency', 'id')->where('economy_id', $economy->id),
        ];
    }

    /**
     * A validator for a currency code in an economy.
     * The currency code must be in the currency code list as well.
     *
     * Note: this function returns an array of validation rules.
     *
     * @param Economy $economy The economy the currency code must be in.
     * @param bool [$new=false] True if the currency code must not exist in the
     *      economy yet.
     *
     * @return Array An array of validation rules.
     */
    public static function currencyCode(Economy $economy, $new = true) {
        $rules = [
            Rule::in(Currency::currencyCodeList()),
        ];

        // Test against database
        if($new)
            $rules[] = Rule::unique('currency', 'code')->where('economy_id', $economy->id);
        else
            $rules[] = Rule::exists('currency', 'code')->where('economy_id', $economy->id);

        return $rules;
    }

    /**
     * A validator for a currency ID in an economy, that a user must be able to
     * create a wallet for.
     *
     * Note: this function returns an array of validation rules.
     *
     * @param Economy $economy The economy the ID must be in.
     * @return Array An array of validation rules.
     */
    // TODO: do not return array here
    public static function walletCurrency(Economy $economy) {
        return [
            Rule::exists('currency', 'id')
                ->where('enabled', true)
                ->where('economy_id', $economy->id)
                ->where('allow_wallet', true),
        ];
    }

    /**
     * Build a validator configuration for application role IDs.
     *
     * @return string The validation configuration.
     */
    public static function appRoles() {
        return Rule::in(AppRoles::ids());
    }

    /**
     * Build a validator configuration for community role IDs.
     *
     * @return string The validation configuration.
     */
    public static function communityRoles() {
        return Rule::in(CommunityRoles::ids());
    }

    /**
     * Build a validator configuration for bar role IDs.
     *
     * @return string The validation configuration.
     */
    public static function barRoles() {
        return Rule::in(BarRoles::ids());
    }

    /**
     * Spreadsheet file export types.
     *
     * @return Rule File export type rules.
     */
    public static function exportTypes() {
        $types = collect(config('bar.spreadsheet_export_types'))
            ->map(function($format) {
                return $format['type'];
            });
        return Rule::in($types);
    }
}
