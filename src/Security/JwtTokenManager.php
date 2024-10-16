<?php

namespace App\Service;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\Clock\SystemClock;

class JwtTokenManager
{
    private $config;

    public function __construct(string $secretKey)
    {
        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($secretKey)
        );
    }

    public function createToken(array $claims, int $expirationInSeconds): Token
    {
        $now = new \DateTimeImmutable();
        $builder = $this->config->builder()
            ->issuedBy('your-app') // émetteur
            ->permittedFor('your-client') // destinataire
            ->issuedAt($now) // date d'émission
            ->expiresAt($now->modify("+$expirationInSeconds seconds")); // date d'expiration

        foreach ($claims as $key => $value) {
            $builder->withClaim($key, $value);
        }

        return $builder->getToken($this->config->signer(), $this->config->signingKey());
    }

    public function validateToken(Token $token): bool
    {
        $clock = new SystemClock(new \DateTimeZone('UTC'));

        $constraints = [
            new SignedWith($this->config->signer(), $this->config->signingKey()),
            new ValidAt($clock)
        ];

        return $this->config->validator()->validate($token, ...$constraints);
    }

    public function parseToken(string $tokenString): ?Token
    {
        try {
            return $this->config->parser()->parse($tokenString);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
