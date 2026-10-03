<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/** Marks developer-owned JavaScript for Util::toJavaScript; ordinary strings never execute. */
final readonly class JsExpression implements \JsonSerializable
{
    public function __construct(public string $expression)
    {
        // An expression is also embedded in HTML scripts; reject HTML parser delimiters.
        if (preg_match('~</script|<!--|-->~i', $expression))
            throw new \InvalidArgumentException('JavaScript expressions cannot contain HTML script delimiters.');
    }

    /** Preserves a string under numeric conversion, escaping it before crossing the code boundary. */
    public static function literal(string $value) : self
    {
        return new self(json_encode($value, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    /** Builds a call to a fixed function reference, keeping interpolated names and URLs as data. */
    public static function call(string $function, mixed ...$arguments) : self
    {
        if (!preg_match('/^[a-z_$][\w$]*(?:\.[a-z_$][\w$]*)*$/i', $function))
            throw new \InvalidArgumentException('Expected a JavaScript function reference.');

        $args = Util::toJavaScript($arguments, JSON_UNESCAPED_UNICODE);
        if ($args === '')
            throw new \JsonException('Could not encode JavaScript call arguments.');

        return new self($function.'('.substr($args, 1, -1).')');
    }

    public function __toString() : string
    {
        return $this->expression;
    }

    public function jsonSerialize() : never
    {
        throw new \JsonException('JavaScript expressions require Util::toJavaScript.');
    }
}
