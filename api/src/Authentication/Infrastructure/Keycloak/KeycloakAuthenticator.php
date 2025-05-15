<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\Query\RoleRepositoryInterface;
use App\Authentication\Domain\User\Query\UserRepositoryInterface;
use App\Authentication\Domain\User\UserId;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Psr\Log\LoggerInterface;
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
        private readonly KeycloakInterface $keycloak,
        private readonly UserRepositoryInterface $userRepository,
        private readonly RoleRepositoryInterface $roleRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('authorization');
    }

    public function authenticate(Request $request): Passport
    {
        // Get token from header
        $jwtToken = $request->headers->get('authorization');
        if ($jwtToken === null || false === str_starts_with($jwtToken, 'Bearer ')) {
            throw new AuthenticationException('Invalid token');
        }

        $jwtToken = str_replace('Bearer ', '', $jwtToken);

        // Decode the token
        $parts = explode('.', $jwtToken);
        if (3 !== \count($parts)) {
            throw new AuthenticationException('Invalid token');
        }

        $headers = json_decode(base64_decode($parts[0]), false);
        // FIXME: make the Realm dynamic
        $realmId = RealmId::fromString('acme-inc');

        // Validate token
        try {
            $keys = $this->keycloak->fetchOpenidCertificates($realmId);

            $decodedToken = JWT::decode($jwtToken, $keys, $headers);
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
            new UserBadge($decodedToken->sub, function (string $userId) use ($realmId) {
                $userId = UserId::fromUri($userId);
                try {
                    $user = $this->userRepository->get($userId, $realmId);

                    if (!$user->authorization instanceof KeycloakAuthorization) {
                        return null;
                    }

                    return new KeycloakUser($user->authorization->keycloakUserId, $this->roleRepository->getAll($realmId, ...$user->roleIds)->toArray());
                } catch (NotFoundException $exception) {
                    // We are not providing access if the User repository does not return a User instance
                    // This should be reviewed in the case we are using the database to store users or Keycloak itself
                    $this->logger->info(strtr(
                        'User with ID %userId% was not found in the %realmId% Realm.',
                        [
                            '%userId%' => $userId->toString(),
                            '%realmId%' => $realmId->toString(),
                        ]
                    ));

                    return null;
                }
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'error' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
