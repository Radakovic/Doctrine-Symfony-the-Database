<?php

namespace App\Controller;

use App\Entity\Question;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AbstractController
{
    private $logger;
    private $isDebug;

    public function __construct(LoggerInterface $logger, bool $isDebug)
    {
        $this->logger = $logger;
        $this->isDebug = $isDebug;
    }

    /**
     * @Route("/questions/new")
     * @param EntityManagerInterface $entityManager
     * @throws Exception
     */
    public function new(EntityManagerInterface $entityManager): Response
    {
        $question = new Question();
        $question->setName('Neko ime')
            ->setSlug("neko-ime-" . random_int(1, 1000))
            ->setQuestion('Neko pitanje');

        if (random_int(1, 10) < 2) {
            $question->setAskedAt(new DateTime(sprintf('-d% days', random_int(1, 100))));
        }

        $entityManager->persist($question);
        $entityManager->flush();

        return new Response(sprintf("Well hello. The shiny new question is id #%d, slug %s",
            $question->getId(),
            $question->getSlug()
        ));
    }

    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage(): Response
    {
        return $this->render('question/homepage.html.twig');
    }

    /**
     * @Route("/questions/{slug}", name="app_question_show")
     * @param $slug
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function show($slug, EntityManagerInterface $entityManager): Response
    {
        $questionRepository = $entityManager->getRepository(Question::class);
        /** @var Question|null $question */
        $question = $questionRepository->findOneBy(['slug' => $slug]);

        $answers = [
            'Make sure your cat is sitting `purrrfectly` still 🤣',
            'Honestly, I like furry shoes better than MY cat',
            'Maybe... try saying the spell backwards?',
        ];

        if (!$question) {
            throw $this->createNotFoundException(sprintf('No question found for slug %s', $slug));
        }

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'answers' => $answers,
        ]);
    }
}
