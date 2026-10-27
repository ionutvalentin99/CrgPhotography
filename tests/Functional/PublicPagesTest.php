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
        self::assertSelectorNotExists('link[href*="fonts.googleapis.com"], link[href*="fonts.gstatic.com"]');
    }

    public function testIconsAreRenderedAsInlineSvg(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertCount(2, $crawler->filter('.info-panel svg.icon-lg'));
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

    public function testContactDetailsComeFromConfiguration(): void
    {
        $client = static::createClient();
        $email = static::getContainer()->getParameter('app.contact_email');

        $client->request('GET', '/contact');

        self::assertSelectorExists(sprintf('footer a[href="mailto:%s"]', $email));
        self::assertSelectorExists(sprintf('main a[href="mailto:%s"]', $email));
        self::assertSelectorExists('footer a[href="tel:+40700000000"]');
    }

    public function testNavbarHasNoAdminLink(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertSelectorNotExists('a[href="/admin"]');
    }
}
