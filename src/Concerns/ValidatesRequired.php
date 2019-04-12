<?php

namespace JPCaparas\TradeMeAPI\Concerns;

trait ValidatesRequired
{
    /**
     * Validates input against required keys.
     *
     * @param array $requiredKeys The keys that must exist
     * @param array $input The input to validate
     * @param callable $onError The function to call when the input does not have all required keys
     */
    protected static function validateRequired(array $requiredKeys, array $input, callable $onError): void
    {
        $paramKeys = array_keys($input);

        $matchCount = count(array_intersect($requiredKeys, $paramKeys));

        if ($matchCount < count($requiredKeys)) {
            $onError($requiredKeys);
        }
    }
}
