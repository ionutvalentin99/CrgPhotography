<?php

namespace App\Tests\Functional;

use App\Entity\ContactRequest;
use App\Tests\Support\EntityFactoryTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ContactFormTest extends WebTestCase
{
    use EntityFactoryTrait;

    public function testValidSubmissionIsStoredAndConfirmed(): void
    {
        $client = $this->createContactClient();

        $this->submitContactForm($client, [
            'contact_request[name]' => 'Ana Popescu',
            'contact_request[email]' => 'ana@example.com',
            'contact_request[phone]' => '0712345678',
            'contact_request[message]' => 'Aș vrea o ședință de portret.',
        ]);

        self::assertResponseRedirects('/contact');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Mesajul tău a fost trimis');

        $stored = $this->entityManager()->getRepository(ContactRequest::class)->findAll();
        self::assertCount(1, $stored);
        self::assertSame('ana@example.com', $stored[0]->getEmail());
        self::assertSame('0712345678', $stored[0]->getPhone());
    }

    public function testInvalidSubmissionShowsErrorsAndStoresNothing(): void
    {
        $client = $this->createContactClient();

        $this->submitContactForm($client, [
            'contact_request[name]' => '',
            'contact_request[email]' => 'not-an-email',
            'contact_request[message]' => '',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('form', 'Te rugăm să completezi numele.');
        self::assertSelectorTextContains('form', 'Adresa de email nu este validă.');
        self::assertCount(0, $this->entityManager()->getRepository(ContactRequest::class)->findAll());
    }

    public function testHoneypotSubmissionLooksSuccessfulButIsNotStored(): void
    {
        $client = $this->createContactClient();

        $this->submitContactForm($client, $this->validValues() + ['contact_request[website]' => 'http://spam.example']);

        self::assertResponseRedirects('/contact');
        self::assertCount(0, $this->entityManager()->getRepository(ContactRequest::class)->findAll());
    }

    public function testSubmissionsAreRateLimitedPerVisitor(): void
    {
        $client = $this->createContactClient();

        for ($i = 0; $i < 5; ++$i) {
            $this->submitContactForm($client, $this->validValues());
            self::assertResponseRedirects('/contact');
        }

        $this->submitContactForm($client, $this->validValues());

        self::assertResponseStatusCodeSame(429);
        self::assertSelectorTextContains('.alert-danger', 'Ai trimis prea multe mesaje');
        self::assertCount(5, $this->entityManager()->getRepository(ContactRequest::class)->findAll());
    }

    private function createContactClient(): KernelBrowser
    {
        $client = static::createClient();
        // The limiter is keyed by IP and its storage persists on disk, so every test starts from a clean slate.
        static::getContainer()->get('cache.contact_form_limiter')->clear();

        return $client;
    }

    private function validValues(): array
    {
        return [
            'contact_request[name]' => 'Ana Popescu',
            'contact_request[email]' => 'ana@example.com',
            'contact_request[message]' => 'Aș vrea o ședință de portret.',
        ];
    }

    private function submitContactForm(KernelBrowser $client, array $values): void
    {
        $client->request('GET', '/contact');
        // The CSRF token is stateless (same-origin check), so the request must carry an Origin header like a browser would.
        $client->submitForm('Trimite mesajul', $values, 'POST', ['HTTP_ORIGIN' => 'http://localhost']);
    }
}
