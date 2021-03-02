<?php

namespace IonBazan\Laravel\ContainerDebug\Command;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use IonBazan\Laravel\ContainerDebug\Helper\ContainerHelper;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class ContainerDebugCommand extends Command
{
    /**
     * @var ContainerHelper
     */
    private $helper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'container:debug {name? : A service name}
            {--tags : Displays tagged services for application}
            {--tag= : Shows all services with a specific tag}
            {--p|profile : Show profiling information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Displays application services and tags';

    /**
     * The console command help text.
     *
     * @var string
     */
    protected $help = <<<EOF
The <info>%command.name%</info> command displays all <comment>bound</comment> services and parameters:

  <info>php %command.full_name%</info>

To get specific information about a service, specify its name:

  <info>php %command.full_name% translator</info>

To get profiling information, add <comment>--profile</comment> flag:

  <info>php %command.full_name% translator --profile</info>
  
Use the <comment>--tags</comment> option to display tagged services grouped by tag:

  <info>php %command.full_name% --tags</info>

Find all services with a specific tag using <comment>--tag</comment> option:

  <info>php %command.full_name% --tag=foo</info>

EOF;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->laravel instanceof Container) {
            throw new \RuntimeException(sprintf('Your application must implement %s', Container::class));
        }

        $this->helper = new ContainerHelper($this->laravel);
        $tags = $this->option('tags');
        $tag = $this->option('tag');
        $name = $this->argument('name');

        if ((null !== $name) && ($tags || null !== $tag)) {
            throw new InvalidArgumentException('The options tags and tag can not be combined with the service name argument.');
        }

        if ($tags && null !== $tag) {
            throw new InvalidArgumentException('The options tags and tag can not be combined together.');
        }

        if ($tags) {
            foreach ($this->helper->getAllTags() as $tag) {
                $this->showTaggedServices($tag, $this->helper->getTaggedServices($tag));
            }

            return 0;
        }

        if ($tag) {
            $this->showTaggedServices($tag, $this->helper->getTaggedServices($tag));

            return 0;
        }

        $services = $this->helper->getAllServices();

        if ($name) {
            $name = $this->getServiceName($services, $name);

            if (!\in_array($name, $services)) {
                throw new InvalidArgumentException(sprintf('Service "%s" not found', $name));
            }

            $this->showSingleService($name);

            return 0;
        }

        $this->showServices($services);
        $this->getOutput()->newLine();
        $this->info('To search for a specific service, re-run this command with a search term. (e.g. <comment>container:debug translator</comment>)');
        $this->getOutput()->newLine();

        return 0;
    }

    /**
     * @param string[] $services
     *
     * @return void
     */
    private function showTaggedServices(string $tag, array $services)
    {
        $this->getOutput()->section(sprintf('Services tagged with "%s" tag', $tag));
        $this->showServices($services);
    }

    /**
     * @param string[] $services
     *
     * @return void
     */
    private function showServices(array $services)
    {
        $rows = [];

        foreach ($services as $service) {
            $rows[] = $this->getRow($service);
        }

        $headers = [
            'Service ID',
            'Class',
            'Shared',
            'Alias',
            'Resolution time',
        ];

        if (!$this->option('profile')) {
            array_pop($headers);
            foreach ($rows as &$row) {
                array_pop($row);
            }
            unset($row);
        }

        $this->table($headers, $rows);
    }

    /**
     * @return void
     */
    private function showSingleService(string $id)
    {
        $this->getOutput()->title(sprintf('Information for Service "<info>%s</info>"', $id));
        $tableRows = [
            ['Service ID', $id],
            ['Class', $this->helper->getClassNameDescription($id)],
            ['Shared', $this->helper->getContainer()->isShared($id) ? 'Yes' : 'No'],
            ['Alias', $this->helper->getContainer()->isAlias($id) ? 'Yes' : 'No'],
            ['Tags', implode(PHP_EOL, $this->helper->getServiceTags($id))],
        ];
        $this->table(['Option', 'Value'], $tableRows);
    }

    /**
     * @return string[]
     */
    private function getRow(string $id): array
    {
        $start = microtime(true);
        $className = $this->helper->getClassNameDescription($id);
        $resolutionTime = microtime(true) - $start;

        return [
            sprintf('<fg=cyan>%s</>', $id),
            $className,
            $this->helper->getContainer()->isShared($id) ? 'Yes' : 'No',
            $this->helper->getContainer()->isAlias($id) ? 'Yes' : 'No',
            (string) $resolutionTime,
        ];
    }

    /**
     * @param string[] $services
     */
    private function getServiceName(array $services, string $name): string
    {
        $name = ltrim($name, '\\');

        if (\in_array($name, $services) || !$this->input->isInteractive()) {
            return $name;
        }

        $matchingServices = $this->getServiceIdsContaining($services, $name);

        if (empty($matchingServices)) {
            throw new InvalidArgumentException(sprintf('No services found matching "%s".', $name));
        }

        if (1 === \count($matchingServices)) {
            return $matchingServices[0];
        }

        return $this->choice('Select one of the following services', $matchingServices);
    }

    /**
     * @param string[] $services
     *
     * @return string[]
     */
    private function getServiceIdsContaining(array $services, string $name): array
    {
        $foundServiceIds = $foundServiceIdsIgnoringBackslashes = [];
        foreach ($services as $serviceId) {
            if (false !== stripos(str_replace('\\', '', $serviceId), $name)) {
                $foundServiceIdsIgnoringBackslashes[] = $serviceId;
            }
            if (false !== stripos($serviceId, $name)) {
                $foundServiceIds[] = $serviceId;
            }
        }

        return $foundServiceIds ?: $foundServiceIdsIgnoringBackslashes;
    }
}
