<?php

namespace App\Tests\Controller;

use App\Entity\Reclamation;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ReclamationControllerTest extends WebTestCase{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/reclamation/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Reclamation::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Reclamation index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'reclamation[Date]' => 'Testing',
            'reclamation[Etat]' => 'Testing',
            'reclamation[Message]' => 'Testing',
            'reclamation[reponse]' => 'Testing',
            'reclamation[Projet]' => 'Testing',
            'reclamation[IdClient]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Reclamation();
        $fixture->setDate('My Title');
        $fixture->setEtat('My Title');
        $fixture->setMessage('My Title');
        $fixture->setReponse('My Title');
        $fixture->setProjet('My Title');
        $fixture->setIdClient('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Reclamation');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Reclamation();
        $fixture->setDate('Value');
        $fixture->setEtat('Value');
        $fixture->setMessage('Value');
        $fixture->setReponse('Value');
        $fixture->setProjet('Value');
        $fixture->setIdClient('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'reclamation[Date]' => 'Something New',
            'reclamation[Etat]' => 'Something New',
            'reclamation[Message]' => 'Something New',
            'reclamation[reponse]' => 'Something New',
            'reclamation[Projet]' => 'Something New',
            'reclamation[IdClient]' => 'Something New',
        ]);

        self::assertResponseRedirects('/reclamation/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getDate());
        self::assertSame('Something New', $fixture[0]->getEtat());
        self::assertSame('Something New', $fixture[0]->getMessage());
        self::assertSame('Something New', $fixture[0]->getReponse());
        self::assertSame('Something New', $fixture[0]->getProjet());
        self::assertSame('Something New', $fixture[0]->getIdClient());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Reclamation();
        $fixture->setDate('Value');
        $fixture->setEtat('Value');
        $fixture->setMessage('Value');
        $fixture->setReponse('Value');
        $fixture->setProjet('Value');
        $fixture->setIdClient('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/reclamation/');
        self::assertSame(0, $this->repository->count([]));
    }
}
