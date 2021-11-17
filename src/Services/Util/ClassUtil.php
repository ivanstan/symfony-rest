<?php

namespace Ivanstan\SymfonySupport\Services\Util;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class ClassUtil
{
    public static function getClassNameFromFqn(string $input): string
    {
        return substr($input, strrpos($input, '\\') + 1);
    }

    public static function camelCaseToSnakeCase(string $input): string
    {
        return str_replace('_', '-', (new CamelCaseToSnakeCaseNameConverter())->normalize($input));
    }

    public static function snakeCaseToCamelCase(string $input, $separator = '-'): string
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }
}
