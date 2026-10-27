<?php

namespace App\Tests\Functional;

use App\Entity\ContactRequest;
use App\Tests\Support\EntityFactoryTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdminAccessTest extends WebTestCase
{
    use EntityFactoryTrait;

    #[DataProvider('adminUrls')]
    public function testAnonymousVisitorIsSentToLogin(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertResponseRedirects('/login');
    }

    #[DataProvider('adminUrls')]
    public function testNonAdminUserIsForbidden(string $url): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['ROLE_USER'], 'user@example.com'));
        $client->request('GET', $url);

        self::assertResponseStatusCodeSame(403);
    }

    public static function adminUrls(): iterable
    {
        yield 'dashboard' => ['/admin'];
        yield 'contact requests' => ['/admin/mesaje'];
    }

    public function testAdminSeesContactRequests(): void
    {
        $client = static::createClient();
        $request = (new ContactRequest())
            ->setName('Ion Ionescu')
            ->setEmail('ion@example.com')
            ->setMessage('Salut!');
        $this->entityManager()->persist($request);
        $this->entityManager()->flush();

        $client->loginUser($this->createUser());
        $client->request('GET', '/admin/mesaje');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table', 'ion@example.com');
    }
}
