<?php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class LoadUsers extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // create objects
        $userUser = $this->createUser('user', 'user', ['ROLE_USER']);
        $userAdmin = $this->createUser('admin', 'admin', ['ROLE_ADMIN']);
        $userGabby = $this->createUser('gabby', 'gabby', ['ROLE_SUPER_ADMIN']);
        $userAlice = $this->createUser('alice', 'alice', ['ROLE_USER']);
        $userSam = $this->createUser('sam', 'sam', ['ROLE_USER']);

        // store to DB
        $manager->persist($userUser);
        $manager->persist($userAdmin);
        $manager->persist($userGabby);
        $manager->persist($userAlice);
        $manager->persist($userSam);

        $manager->flush();
    }

    /**
     * @param       $username
     * @param       $plainPassword
     * @param array $roles // default to ROLE_USER if no ROLE supplied
     *
     * @return User
     */
    private function createUser($username, $plainPassword, $roles = ['ROLE_PUBLIC']):User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setRoles($roles);

        // password - and encoding
        $encodedPassword = $this->encodePassword($user, $plainPassword);
        $user->setPassword($encodedPassword);

        return $user;
    }

    private function encodePassword($user, $plainPassword):string
    {
        $encodedPassword = $this->encoder->encodePassword($user, $plainPassword);
        return $encodedPassword;
    }
}