<?php

namespace AppBundle\Service;

use AppBundle\Model\User;
use AppBundle\Model\UserQuery;
use Creonit\UserBundle\Model\UserGroupQuery;
use Creonit\UserBundle\Model\UserSign;
use Creonit\UserBundle\Security\AuthorizationInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthorizationService implements AuthorizationInterface
{

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function processNewUser($email, $password, $name = '')
    {
        $user = $this->createUser($email, $password, $name);

        $this->createUserSign($user, 'email', $user->getEmail(), true);
        $user->setGroup(UserGroupQuery::create()->findOneByName('users'));
        $this->sendMailWelcome($user, $email, $password);

        return $user;
    }

    public function createUser($email, $password, $name = '')
    {
        $user = new User();

        $this->changePassword($user, $password);

        $user->setEmail($email);
        $user->setName($name);
        $user->setPassword($password);
        $user->save();

        return $user;
    }

    public function createUserSign(User $user, $provider, $username, $enabled = true)
    {
        if (!$sign = UserSign::get($provider, $username)) {
            $sign = new UserSign();
            $sign->setUser($user);
            $sign->setProvider($provider);
            $sign->setUsername($username);
            $sign->setEnabled($enabled);
            $sign->save();
        }

        return $sign;
    }

    public function changePassword(User $user, $password)
    {
        $user->setSalt(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36));
        $encoder = $this->container->get('security.password_encoder');
        $password = $encoder->encodePassword($user, $password);
        $user->setPassword($password);
        $user->save();
    }

    public function findUserSign($provider, $username, $user = null, $enabled = null)
    {
        return UserSign::get($provider, $username, $user, $enabled);
    }

    public function deleteUserSign(User $user, $provider, $username)
    {
        if ($sign = UserSign::get($provider, $username, $user)) {
            $sign->delete();
        }
    }

    public function sendMailWelcome(User $user, $email, $password)
    {

        $mailing = $this->container->get('creonit_mailing');
        $mailing->send(
            $mailing->createMessage('', [
                'auth.welcome' => $this->container->get('serializer')->normalize([
                    'user' => $user,
                    'email' => $email,
                    'password' => $password
                ])
            ]),
            $email
        );
    }


    public function generateSecret()
    {
        return md5(uniqid());
    }

    public function generatePassword($length = 6)
    {
        return mb_substr(md5(uniqid()), 0, $length);
    }

    public function authorizeUser(User $user)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());

        $this->container->get('security.token_storage')->setToken($token);
        $this->container->get('session')->set('_security_main', serialize($token));
    }

    public function loadUserByUsername($username)
    {
        $exception = new UsernameNotFoundException;
        $exception->setUsername($username);
        throw $exception;
    }

    /**
     * @param UserInterface|User $user
     * @return User
     * @throws UnsupportedUserException
     */
    public function refreshUser(UserInterface $user)
    {
        if ($refreshed = UserQuery::create()->findPk($user->getId())) {
            return $refreshed;
        }

        throw new UnsupportedUserException();
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getUsername();
        $provider = $response->getResourceOwner()->getName();

        if ($sign = UserSign::get($provider, $username)) {
            if ($user = $sign->getUser()) {
                return $user;
            }

        } else {

            $password = $this->generatePassword(6);
            $email = $response->getEmail();
            $name = $response->getRealName();
            $user = $this->createUser($email, $password, $name);
            $this->createUserSign($user, $provider, $username);

            return $user;
        }

        $exception = new UsernameNotFoundException;
        $exception->setUsername($username);
        throw $exception;
    }

    public function supportClass($class)
    {
        return 'AppBundle\Model\User' == $class;
    }

    public function enableUserSign(UserSign $sign)
    {
        $sign->setSecret('')->setEnabled(true)->save();
    }

}