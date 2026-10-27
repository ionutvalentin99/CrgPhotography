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
        $client = static::createClient();

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
        $client = static::createClient();

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

    private function submitContactForm(KernelBrowser $client, array $values): void
    {
        $client->request('GET', '/contact');
        // The CSRF token is stateless (same-origin check), so the request must carry an Origin header like a browser would.
        $client->submitForm('Trimite mesajul', $values, 'POST', ['HTTP_ORIGIN' => 'http://localhost']);
    }
}
