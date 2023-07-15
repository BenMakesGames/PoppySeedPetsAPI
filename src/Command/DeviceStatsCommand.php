<?php
namespace App\Command;

use App\Entity\DeviceStats;
use App\Repository\DeviceStatsRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeviceStatsCommand extends Command
{
    private DeviceStatsRepository $deviceStatsRepository;

    public function __construct(DeviceStatsRepository $deviceStatsRepository)
    {
        $this->deviceStatsRepository = $deviceStatsRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:device-stats')
            ->setDescription('Export device stats as JSON.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 4 weeks
        $oldestDate = (new \DateTimeImmutable())->modify('-28 days');

        /** @var DeviceStats[] $latestStats */
        $latestStats = $this->deviceStatsRepository->createQueryBuilder('s')
            ->andWhere('s.time > :oldestTime ')
            ->andWhere('s.id IN (SELECT MAX(s2.id) FROM App:DeviceStats s2 WHERE s2.time > :oldestTime AND s2.user=s.user)')
            ->setParameter('oldestTime', $oldestDate->format('Y-m-d H:s:i'))
            ->getQuery()
            ->execute()
        ;

        $languages = [];
        $browsers = [];
        $widths = [];

        foreach($latestStats as $stat)
        {
            if(!array_key_exists($stat->getLanguage(), $languages))
                $languages[$stat->getLanguage()] = 1;
            else
                $languages[$stat->getLanguage()]++;

            $browser = DeviceStatsCommand::getBrowser($stat->getUserAgent());

            if(!array_key_exists($browser, $browsers))
                $browsers[$browser] = 1;
            else
                $browsers[$browser]++;

            $widthGroup = DeviceStatsCommand::getWidthGroup($stat->getWindowWidth());

            if(!array_key_exists($widthGroup, $widths))
                $widths[$widthGroup] = 1;
            else
                $widths[$widthGroup]++;
        }

        arsort($languages);
        arsort($browsers);
        arsort($widths);

        echo \json_encode([
            'languages' => $languages,
            'browsers' => $browsers,
            'windowWidths' => $widths,
        ], JSON_PRETTY_PRINT);

        return Command::SUCCESS;
    }

    private static function getBrowser(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        if(strpos($userAgent, ' edge/') !== false)
            return 'Edge';

        if(strpos($userAgent, ' trident/') !== false)
            return 'IE';

        if(strpos($userAgent, ' opr/') !== false)
            return 'Opera';

        if(strpos($userAgent, ' safari/') !== false && strpos($userAgent, 'mac os') !== false)
            return 'Safari';

        if(strpos($userAgent, ' firefox/') !== false)
            return 'Firefox';

        if(strpos($userAgent, ' chrome/') !== false)
            return 'Chrome';

        return 'unknown';
    }

    private static function getWidthGroup(int $width): string
    {
        if($width < 320)
            return '< 320';
        else if($width < 360)
            return '320 - 359';
        else if($width < 400)
            return '360 - 399';
        else if($width < 500)
            return '400 - 499';
        else if($width < 600)
            return '500 - 599';
        else if($width < 700)
            return '600 - 699';
        else if($width < 700)
            return '600 - 699';
        else if($width < 800)
            return '700 - 799';
        else if($width < 900)
            return '800 - 899';
        else
            return '>= 900';
    }
}
