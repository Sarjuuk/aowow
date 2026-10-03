<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/** Bounded numeric grammar for database defaults and spell formulas; never executes PHP. */
final class NumericExpression
{
    private const array PRECEDENCE = [
        'or' => 1, 'xor' => 2, 'and' => 3, '||' => 20, '&&' => 30,
        '|' => 33, '^' => 34, '&' => 35,
        '==' => 40, '!=' => 40, '===' => 40, '!==' => 40,
        '<' => 50, '<=' => 50, '>' => 50, '>=' => 50,
        '<<' => 60, '>>' => 60, '+' => 70, '-' => 70,
        '*' => 80, '/' => 80, '%' => 80, '**' => 100
    ];
    private const array ARITY = ['abs' => 1, 'ceil' => 1, 'floor' => 1,
        'min' => 2, 'max' => 2, 'gt' => 2, 'lt' => 2, 'gte' => 2, 'lte' => 2, 'eq' => 2,
        'cond' => 3, 'clamp' => 3];

    private array $tokens = [];
    private int $position = 0;

    private function __construct(string $expression, private bool $spellFunctions)
    {
        if (strlen($expression) > 8192)
            throw new \InvalidArgumentException('numeric expression too long');

        $offset = 0;
        while ($offset < strlen($expression))
        {
            if (!preg_match('/\G\s*(0[xX][0-9a-fA-F]+|0[bB][01]+|(?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+-]?\d+)?|\$[a-zA-Z]+|true\b|false\b|and\b|xor\b|or\b|===|!==|\*\*|<<|>>|<=|>=|==|!=|&&|\|\||[()+\-*\/%^&|!~<>,?:])/A', $expression, $m, 0, $offset))
            {
                if (trim(substr($expression, $offset)) === '')
                    break;
                throw new \InvalidArgumentException('invalid numeric expression');
            }
            $offset += strlen($m[0]);
            $this->tokens[] = $m[1];
            if (count($this->tokens) > 1024)
                throw new \InvalidArgumentException('too many numeric tokens');
        }
    }

    public static function evaluate(string $expression, bool $spellFunctions = false) : int|float|bool
    {
        $parser = new self($expression, $spellFunctions);
        $tree = $parser->parse(0, 0);
        if ($parser->position !== count($parser->tokens))
            throw new \InvalidArgumentException('unexpected numeric token');
        return self::resolve($tree, 0);
    }

    private function take() : string
    {
        return $this->tokens[$this->position++] ?? throw new \InvalidArgumentException('incomplete numeric expression');
    }

    private function expect(string $token) : void
    {
        if ($this->take() !== $token)
            throw new \InvalidArgumentException('unexpected numeric token');
    }

    private function parse(int $minimum, int $depth) : array
    {
        if ($depth > 64)
            throw new \InvalidArgumentException('numeric expression too deep');

        $token = $this->take();
        if (in_array($token, ['+', '-', '!', '~'], true))
            $left = ['unary', $token, $this->parse(90, $depth + 1)];
        else if ($token === '(')
        {
            $left = $this->parse(0, $depth + 1);
            $this->expect(')');
        }
        else if ($token[0] === '$')
        {
            $name = strtolower(substr($token, 1));
            if (!$this->spellFunctions || !isset(self::ARITY[$name]))
                throw new \InvalidArgumentException('unknown numeric function');
            $this->expect('(');
            $args = [];
            for ($i = 0; $i < self::ARITY[$name]; $i++)
            {
                if ($i)
                    $this->expect(',');
                $args[] = $this->parse(0, $depth + 1);
            }
            $this->expect(')');
            $left = ['call', $name, $args];
        }
        else
        {
            $value = match (true) {
                $token === 'true' => true,
                $token === 'false' => false,
                !!preg_match('/^0[xX][0-9a-fA-F]+$/D', $token) => hexdec(substr($token, 2)),
                !!preg_match('/^0[bB][01]+$/D', $token) => bindec(substr($token, 2)),
                !!preg_match('/^0[0-7]+$/D', $token) => octdec($token),
                !!preg_match('/^0\d+$/D', $token) => throw new \InvalidArgumentException('invalid octal number'),
                is_numeric($token) => $token + 0,
                default => throw new \InvalidArgumentException('expected number')
            };
            $left = ['value', self::finite($value)];
        }

        while (isset($this->tokens[$this->position]))
        {
            $op = $this->tokens[$this->position];
            if ($op === '?' && $minimum <= 10)
            {
                $this->position++;
                $yes = ($this->tokens[$this->position] ?? '') === ':' ? $left : $this->parse(0, $depth + 1);
                $this->expect(':');
                $left = ['conditional', $left, $yes, $this->parse(11, $depth + 1)];
                continue;
            }
            $priority = self::PRECEDENCE[$op] ?? -1;
            if ($priority < $minimum)
                break;
            $this->position++;
            $left = ['binary', $op, $left, $this->parse($priority + ($op === '**' ? 0 : 1), $depth + 1)];
        }
        return $left;
    }

    private static function finite(int|float|bool $value) : int|float|bool
    {
        if (is_float($value) && !is_finite($value))
            throw new \RangeException('non-finite numeric result');
        return $value;
    }

    /** Explicit arithmetic also handles spell field modifiers without interpolating DB values. */
    public static function operation(string $op, mixed $left, mixed $right) : int|float|bool
    {
        if ((!is_numeric($left) && !is_bool($left)) || (!is_numeric($right) && !is_bool($right)))
            throw new \InvalidArgumentException('non-numeric operand');
        $left = self::finite(is_bool($left) ? $left : $left + 0);
        $right = self::finite(is_bool($right) ? $right : $right + 0);
        return self::finite(match ($op) {
            '+' => $left + $right, '-' => $left - $right, '*' => $left * $right,
            '/' => $right == 0 ? throw new \RangeException('division by zero') : $left / $right,
            '%' => (int)$right === 0 ? throw new \RangeException('division by zero') : (int)$left % (int)$right,
            '**' => $left ** $right,
            '&' => (int)$left & (int)$right, '|' => (int)$left | (int)$right, '^' => (int)$left ^ (int)$right,
            '<<' => (int)$right < 0 ? throw new \RangeException('negative shift') : (int)$left << (int)$right,
            '>>' => (int)$right < 0 ? throw new \RangeException('negative shift') : (int)$left >> (int)$right,
            '>' => $left > $right, '>=' => $left >= $right, '<' => $left < $right, '<=' => $left <= $right,
            '==' => $left == $right, '!=' => $left != $right, '===' => $left === $right, '!==' => $left !== $right,
            '&&', 'and' => $left && $right, '||', 'or' => $left || $right, 'xor' => (bool)$left !== (bool)$right,
            default => throw new \InvalidArgumentException('unknown numeric operator')
        });
    }

    private static function resolve(array $node, int $depth) : int|float|bool
    {
        if ($depth > 64)
            throw new \InvalidArgumentException('numeric expression too deep');
        if ($node[0] === 'value')
            return $node[1];
        if ($node[0] === 'unary')
        {
            $v = self::resolve($node[2], $depth + 1);
            return self::finite(match ($node[1]) { '+' => +$v, '-' => -$v, '!' => !$v, '~' => ~(int)$v });
        }
        if ($node[0] === 'conditional')
            return self::resolve(self::resolve($node[1], $depth + 1) ? $node[2] : $node[3], $depth + 1);
        if ($node[0] === 'binary')
        {
            $left = self::resolve($node[2], $depth + 1);
            if (in_array($node[1], ['&&', 'and'], true) && !$left)
                return false;
            if (in_array($node[1], ['||', 'or'], true) && $left)
                return true;
            return self::operation($node[1], $left, self::resolve($node[3], $depth + 1));
        }

        $args = array_map(fn($arg) => self::resolve($arg, $depth + 1), $node[2]);
        return self::finite(match ($node[1]) {
            'abs' => abs($args[0]), 'ceil' => ceil($args[0]), 'floor' => floor($args[0]),
            'min' => min(...$args), 'max' => max(...$args),
            'gt' => $args[0] > $args[1], 'lt' => $args[0] < $args[1],
            'gte' => $args[0] >= $args[1], 'lte' => $args[0] <= $args[1], 'eq' => $args[0] == $args[1],
            'cond' => $args[0] ? $args[1] : $args[2],
            'clamp' => $args[0] > $args[2] ? $args[2] : ($args[0] < $args[1] ? $args[1] : $args[0])
        });
    }
}
