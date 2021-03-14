<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class LoginFormAuthenticator extends AbstractGuardAuthenticator
{

    private $encoder;
    private $urlGenerator;

    public function __construct(UserPasswordEncoderInterface $encoder, UrlGeneratorInterface $urlGenerator)
    {
        $this->encoder = $encoder;
        $this->urlGenerator = $urlGenerator;
    }

    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === "security_login"
            && $request->isMethod("POST");
    }

    public function getCredentials(Request $request): array
    {
        $credentials = $request->request->get('login');

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            return $userProvider->loadUserByUsername($credentials['email']);
        } catch (UsernameNotFoundException $e) {
            throw new AuthenticationException("Identifiants incorrects.");
        }
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        $isValid = $this->encoder->isPasswordValid($user, $credentials['password']);

        if (!$isValid) {
            throw new AuthenticationException("Identifiants incorrects.");
        }

        if (!$user->isVerified()) {
            throw new AuthenticationException("Ce compte n'a pas encore été validé, merci de l'activer en confirmant votre adresse email.");
        }

        return $isValid;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): Response
    {
        return new RedirectResponse('/');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $request->attributes->set(Security::AUTHENTICATION_ERROR, $exception);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->urlGenerator->generate("security_login"));
    }

    public function supportsRememberMe()
    {


    }
}
