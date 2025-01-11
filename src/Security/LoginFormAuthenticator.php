<?php

namespace App\Security;

use App\Entity\Korisnik;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(), // Handles the remember me functionality automatically
            ]
        );
    }

    // public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    // {
    //     if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
    //         return new RedirectResponse($targetPath);
    //     }

    //     // For example:
    //     // return new RedirectResponse($this->urlGenerator->generate('some_route'));
    //     return new RedirectResponse($this->urlGenerator->generate('app_home'));
    //     // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    // }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $korisnik = $token->getUser();
        if (!$korisnik instanceof Korisnik) {
            throw new \LogicException('Unexpected user class');
        }

        $roles = $token->getRoleNames();

        if (in_array('ROLE_ADMIN', $roles)) {
            // Redirect admin to /pilane
            return new RedirectResponse($this->urlGenerator->generate('renter'));
        }

        // For other roles or no specific role, redirect to the default target path
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Check if the user has a renter
        if ($korisnik->getRenter()) {
            // Redirect to the palana's page
            return new RedirectResponse($this->urlGenerator->generate('show_renter', ['id' => $korisnik->getRenter()->getId()]));
        }

        // Fallback to a default route, adjust as needed
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
