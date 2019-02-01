<?php

require __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = new Dotenv(__DIR__.'/../../');
$dotenv->load();

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('env')) {

	 /**
     * Gets the value of an environment variable.
     * This was originally copied from Laravel
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
	function env($key, $default = null)
	{
		
		$value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        if (strlen($value) > 1 && Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
	}
}
