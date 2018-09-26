<?php
/**
 * Created by PhpStorm.
 * User: ptiperuv
 * Date: 26/09/2018
 * Time: 21:58
 */

namespace App\Command;


use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EniCrawlCommand
 * @package App\Command
 */
class EniCrawlCommand extends Command {
    private $url = null;
    private $user = null;
    private $pwd = null;
    private $logger;

    /**
     * EniCrawlCommand constructor.
     *
     * @param array $config
     */
    public function __construct(LoggerInterface $logger, array $config = []) {
        $this->url = $config['url'];
        $this->user = $config['user'];
        $this->pwd = $config['pwd'];

        $this->logger = $logger;

        parent::__construct();
    }

    /**
     * Configure the command
     */
    protected function configure() {
        $this
            // the name of the command (the part after "bin/console")
            ->setName( 'app:crawl-books' )
            // the short description shown while running "php bin/console list"
            ->setDescription( 'Crawls books website.' )
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp( 'This command allows you to extract data from website' );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    protected function execute( InputInterface $input, OutputInterface $output ) {
        $client = \Symfony\Component\Panther\Client::createChromeClient();
        $crawler = $client->request('GET', $this->url); // Yes, this website is 100% in JavaScript

        // Wait for an element to be rendered
        $client->waitFor('#loginForm');
        $form = $crawler->filter('#loginForm')->form(['UserName' => $this->user, 'Password' => $this->pwd]);
        $client->submit($form);

        try {
            $client->waitFor('#ClientName', 5);
        } catch (\Throwable $e) {
            $this->logger->emergency("{message}\r\n{trace}", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        $client->takeScreenshot('book.png'); // Yeah, screenshot!
    }
}