<?php
namespace App\Command;

use App\Entity\PetSpecies;
use App\Entity\Survey;
use App\Entity\SurveyQuestion;
use App\Entity\User;
use App\Repository\SurveyQuestionAnswerRepository;
use App\Repository\SurveyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportSurveyAnswersCommand extends Command
{
    private SurveyRepository $surveyRepository;
    private SurveyQuestionAnswerRepository $surveyQuestionAnswerRepository;
    private UserRepository $userRepository;

    public function __construct(
        SurveyRepository $surveyRepository, UserRepository $userRepository,
        SurveyQuestionAnswerRepository $surveyQuestionAnswerRepository
    )
    {
        $this->surveyRepository = $surveyRepository;
        $this->userRepository = $userRepository;
        $this->surveyQuestionAnswerRepository = $surveyQuestionAnswerRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:export-survey-answers')
            ->addArgument('guid', InputArgument::REQUIRED, 'The GUID of the survey to export.')
            ->setDescription('Export survey answers to CSV. Pipe to a file to save.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $guid = $input->getArgument('guid');

        $survey = $this->surveyRepository->findOneBy([ 'guid' => $guid ]);

        if(!$survey)
        {
            $output->writeln('Survey not found.');
            return 1;
        }

        $qb = $this->surveyQuestionAnswerRepository->createQueryBuilder('a');
        $results = $qb
            ->select('DISTINCT(a.user) AS id')
            ->where('a.id IN (:answerIds)')
            ->setParameter('answerIds', $survey->getQuestions()->map(fn (SurveyQuestion $q) => $q->getId()))
            ->getQuery()
            ->getResult()
        ;

        $ids = array_map(fn($r) => (int)$r['id'], $results);

        $column = 0;
        $questionColumns = [];
        $questionText = [];

        foreach($survey->getQuestions() as $question)
        {
            $questionText[] = $question->getTitle();
            $questionColumns[$question->getId()] = $column;
            $column++;
        }

        $output->write('id,name,registered on,moneys,museum points,');
        $output->writeln(ExportSurveyAnswersCommand::arrayToCSVLine($questionText));

        for($i = 0; $i < count($ids); $i += 50)
        {
            $this->exportUsers(array_slice($ids, $i, 50), $questionColumns, $output);
        }

        return 0;
    }

    private function exportUsers(array $ids, array $questionColumns, OutputInterface $output)
    {
        $users = $this->userRepository->findBy([ 'id' => $ids ], [ 'id' => 'ASC' ]);

        foreach($users as $user)
        {
            $row = [];

            for($i = 0; $i < count($questionColumns); $i++)
                $row[$i] = '';

            $answers = $this->surveyQuestionAnswerRepository->findBy([
                'user' => $user,
                'question' => array_keys($questionColumns)
            ]);

            foreach($answers as $answer)
                $row[$questionColumns[$answer->getQuestion()->getId()]] = $answer->getAnswer();

            $this->exportAnswers($user, $row, $output);
        }
    }

    private function exportAnswers(User $user, array $row, OutputInterface $output)
    {
        $data = [
            $user->getId(),
            $user->getName(),
            $user->getRegisteredOn()->format('Y-m-d H:i:s'),
            $user->getMoneys(),
            $user->getMuseumPoints()
        ];

        foreach($row as $r)
            $data[] = $r;

        $output->writeln(ExportSurveyAnswersCommand::arrayToCSVLine($data));
    }

    private static function arrayToCSVLine(array $values)
    {
        return implode(',', array_map(function ($v) {
            return '"' . str_replace('"', '""', $v) . '"';
        }, $values));
    }
}
