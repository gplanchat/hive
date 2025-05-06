<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use App\Authentication\Infrastructure\Security\User;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class KeycloakAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly Keycloak $keycloak,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        // Get token from header
        $jwtToken = $request->headers->get('Authorization');
        if (false === str_starts_with($jwtToken, 'Bearer ')) {
            throw new AuthenticationException('Invalid token');
        }

        $jwtToken = str_replace('Bearer ', '', $jwtToken);

        // Decode the token
        $parts = explode('.', $jwtToken);
        if (count($parts) !== 3) {
            throw new AuthenticationException('Invalid token');
        }

        $header = json_decode(base64_decode($parts[0]), true);

        // Validate token
        try {
            $this->keycloak->fetchOpenidCertificates();

            $decodedToken = JWT::decode($jwtToken, $this->getJwks(), [$header['alg']]);
        } catch (SignatureInvalidException $exception) {
            throw new AuthenticationException('Provided JWT was invalid because the signature verification failed', previous: $exception);
        } catch (BeforeValidException $exception) {
            throw new AuthenticationException('Provided JWT is trying to be used before it\'s eligible as defined by \'nbf\' or  before it\'s been created as defined by \'iat\'', previous: $exception);
        } catch (ExpiredException $exception) {
            throw new AuthenticationException('Provided JWT has since expired, as defined by the \'exp\' claim', previous: $exception);
        } catch (\InvalidArgumentException $exception) {
            throw new AuthenticationException('Provided key/key-array was empty or malformed', previous: $exception);
        } catch (\DomainException $exception) {
            throw new AuthenticationException('Provided JWT is malformed', previous: $exception);
        } catch (\UnexpectedValueException $exception) {
            throw new AuthenticationException('Provided JWT was invalid', previous: $exception);
        }

        return new SelfValidatingPassport(
            new UserBadge($decodedToken->sub, function (string $userId) {
                $user = $this->userRepository->find($userId);
                if (null === $user) {
                    $user = new User($userId);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                }

                return $user;
            })
        );
    }
    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $firewallName
     *
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     *
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'error' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
