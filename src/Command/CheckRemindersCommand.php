<?php
namespace App\Command;

use App\Repository\ReminderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckRemindersCommand extends Command
{
    private $em;
    private $reminderRepository;

    public function __construct(EntityManagerInterface $em, ReminderRepository $reminderRepository)
    {
        $this->em = $em;
        $this->reminderRepository = $reminderRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:check-reminders')
            ->setDescription('Check if any reminders need to be triggered, and trigger them.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reminders = $this->reminderRepository->findReadyReminders();

        $auth = [
            'VAPID' => [
                'subject' => 'https://poppyseedpets.com',
                'publicKey' => getenv('VAPID_PUBLIC_KEY'),
                'privateKey' => getenv('VAPID_PRIVATE_KEY'),
            ]
        ];

        $webPush = (new WebPush($auth))
            ->setAutomaticPadding(false)
            ->setReuseVAPIDHeaders(true)
        ;

        $subscriptionsByEndpoint = [];

        foreach($reminders as $reminder)
        {
            if($reminder->getReminderInterval() === null)
                $this->em->remove($reminder);
            else
                $reminder->updateNextReminder();

            foreach($reminder->getUser()->getPushSubscriptions() as $subscription)
            {
                $subscriptionsByEndpoint[$subscription->getEndpoint()] = $subscription;

                $webPush->sendNotification(
                    Subscription::create([
                        'endpoint' => $subscription->getEndpoint(),
                        'publicKey' => $subscription->getP256dh(),
                        'authToken' => $subscription->getAuth(),
                    ]),
                    $reminder->getText()
                );
            }
        }

        foreach($webPush->flush() as $report)
        {
            if($report->isSubscriptionExpired())
                $this->em->remove($subscriptionsByEndpoint[$report->getEndpoint()]);
        }

        return Command::SUCCESS;
    }
}
