<?php

declare(strict_types=1);

namespace App\Services;

use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token;

class JWTService
{
    /**
     * @param string $stringToken
     * @return bool
     */
    public function validateToken(string $stringToken): bool
    {
        /*
            Implement, I created it just as an example.
            Implement with credential key
        */
        return true;
    }

    /**
     * @param string $token
     * @return Token
     */
    public function getParserToken(string $token)
    {
        return (new Parser(new JoseEncoder()))->parse($token);
    }
}
