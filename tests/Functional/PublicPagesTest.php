<?php

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PublicPagesTest extends WebTestCase
{
    #[DataProvider('publicUrls')]
    public function testPublicPageLoads(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('html[lang="ro"] meta[name="viewport"]');
    }

    public static function publicUrls(): iterable
    {
        yield 'home' => ['/'];
        yield 'gallery' => ['/gallery'];
        yield 'contact' => ['/contact'];
        yield 'login' => ['/login'];
    }

    public function testUnknownAlbumReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/gallery/999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testNavbarHasNoAdminLink(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertSelectorNotExists('a[href="/admin"]');
    }
}
