<?php

namespace App\Tests\Functional;

use App\Entity\Photo;
use App\Tests\Support\EntityFactoryTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AlbumListingTest extends WebTestCase
{
    use EntityFactoryTrait;

    public function testHomepageShowsAtMostNineVisibleAlbumsNewestFirst(): void
    {
        $client = static::createClient();
        $author = $this->createUser();

        for ($i = 1; $i <= 10; ++$i) {
            $this->createAlbum($author, sprintf('Album %02d', $i), createdAt: new \DateTimeImmutable(sprintf('2026-01-%02d', $i)));
        }
        $this->createAlbum($author, 'Album ascuns', visible: false, createdAt: new \DateTimeImmutable('2026-02-01'));
        $this->entityManager()->clear();

        $crawler = $client->request('GET', '/');

        $titles = $crawler->filter('.album-card-title')->each(static fn ($node) => $node->text());
        self::assertCount(9, $titles);
        self::assertSame('Album 10', $titles[0]);
        self::assertNotContains('Album ascuns', $titles);
        self::assertNotContains('Album 01', $titles);
    }

    public function testPhotoCountIncludesOnlyVisiblePhotos(): void
    {
        $client = static::createClient();
        $this->createAlbum($this->createUser(), 'Portrete', visiblePhotos: 3, hiddenPhotos: 2);
        $this->entityManager()->clear();

        $client->request('GET', '/gallery');

        self::assertSelectorTextSame('.album-card-meta', '3 poze');
    }

    #[DataProvider('albumListingUrls')]
    public function testAlbumListingRunsASingleQuery(string $url): void
    {
        $client = static::createClient();
        // First request boots the app; queries from the fixtures below would otherwise be counted in it.
        $client->request('GET', '/login');

        $author = $this->createUser();
        for ($i = 0; $i < 6; ++$i) {
            $this->createAlbum($author, 'Album '.$i, visiblePhotos: 2);
        }
        $this->entityManager()->clear();

        $client->enableProfiler();
        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
        self::assertSame(1, $client->getProfile()->getCollector('db')->getQueryCount());
    }

    public static function albumListingUrls(): iterable
    {
        yield 'home' => ['/'];
        yield 'gallery' => ['/gallery'];
    }

    public function testHiddenAlbumIsNotReachable(): void
    {
        $client = static::createClient();
        $album = $this->createAlbum($this->createUser(), 'Ciornă', visible: false);
        $this->entityManager()->clear();

        $client->request('GET', '/gallery/'.$album->getId());

        self::assertResponseStatusCodeSame(404);
    }

    public function testAlbumPagePhotosFollowSortOrder(): void
    {
        $client = static::createClient();
        $author = $this->createUser();
        $album = $this->createAlbum($author, 'Ordine');

        foreach ([2, 0, 1] as $sortOrder) {
            $this->entityManager()->persist(
                (new Photo())
                    ->setAlbum($album)
                    ->setPath(sprintf('uploads/order-%d.jpg', $sortOrder))
                    ->setSortOrder($sortOrder)
                    ->setVisible(true)
                    ->setCreatedBy($author),
            );
        }
        $this->entityManager()->flush();
        $this->entityManager()->clear();

        $crawler = $client->request('GET', '/gallery/'.$album->getId());

        self::assertSame(
            ['/uploads/order-0.jpg', '/uploads/order-1.jpg', '/uploads/order-2.jpg'],
            $crawler->filter('main img')->each(static fn ($img) => $img->attr('src')),
        );
    }

    public function testAlbumPageShowsOnlyVisiblePhotos(): void
    {
        $client = static::createClient();
        $album = $this->createAlbum($this->createUser(), 'Studio', visiblePhotos: 2, hiddenPhotos: 1);
        $this->entityManager()->clear();

        $crawler = $client->request('GET', '/gallery/'.$album->getId());

        self::assertResponseIsSuccessful();
        self::assertCount(2, $crawler->filter('main img'));
    }
}
