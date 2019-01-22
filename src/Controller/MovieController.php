<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Movie;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\MovieType;
use App\Repository\CommentRepository;
use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/movie")
 */
class MovieController extends AbstractController
{
    const PAGE_TO_IMPORT=25;
    private $security;

    public function __construct(Security $security)
    {
        // Avoid calling getUser() in the constructor: auth may not
        // be complete yet. Instead, store the entire Security object.
        $this->security = $security;
    }

    /**
     * @Route("/", name="movie_index", methods={"GET"})
     */
    public function index(MovieRepository $movieRepository): Response
    {
        return $this->render('movie/index.html.twig', [
            'movies' => $movieRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="movie_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $movie = new Movie();
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($movie);
            $entityManager->flush();

            return $this->redirectToRoute('movie_index');
        }

        return $this->render('movie/new.html.twig', [
            'movie' => $movie,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="movie_show", methods={"GET","POST"})
     */
    public function show(Movie $movie, Request $request): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->security->getUser();
            $comment->setUserId($user);
            $comment->setMovie($movie);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash(
                'success',
                "Your comment has been registered !"
            );
            return $this->redirectToRoute('movie_show', ['id'=>$movie->getId()]);
        }

        $comments=$movie->getComments();

        return $this->render('movie/show.html.twig', [
            'movie' => $movie,
            'form' => $form->createView(),
            'comments' => $comments,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="movie_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Movie $movie): Response
    {
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('movie_index', [
                'id' => $movie->getId(),
            ]);
        }

        return $this->render('movie/edit.html.twig', [
            'movie' => $movie,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="movie_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Movie $movie): Response
    {
        if ($this->isCsrfTokenValid('delete'.$movie->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($movie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('movie_index');
    }

    /**
     * @Route("/admin/import", name="import_movies", methods={"GET"})
     */
    public function importMovies():Response
    {
        $client = new \GuzzleHttp\Client();

        for ($i=1; $i<=self::PAGE_TO_IMPORT; $i++) {
            $request = new \GuzzleHttp\Psr7\Request('GET', 'https://api.themoviedb.org/3/movie/top_rated?api_key=b5bc52293943361515af8c82862fe832&page='.$i);
            $promise = $client->sendAsync($request)->then(function ($response) {
                $body = $response->getBody();
                $body = json_decode($body, true, 10);
                foreach ($body['results'] as $key => $value) {
                    $movie=new Movie();
                    $movie->setApiId($value['id']);
                    $movie->setTitle($value['title']);
                    $movie->setSynopsis($value['overview']);
                    $movie->setReleaseDate(substr($value['release_date'], 0,4));

                    $this->getDoctrine()->getManager()->persist($movie);
                    $this->getDoctrine()->getManager()->flush();
                }
            });
            $promise->wait();
        }
        $promise->wait();

        return $this->redirectToRoute('movie_index');
    }

    /**
     * @Route("/admin/import/posters", name="import_posters", methods={"GET"})
     */
    public function importPosters(MovieRepository $movieRepository):Response
    {
        $client = new \GuzzleHttp\Client();

        // https://image.tmdb.org/t/p/w300_and_h450_bestv2/jX94vnfcuJ8rTnFbsoriY6dlHrC.jpg

        $movies=$movieRepository->findAll();

        foreach ($movies as $key=>$movie) {
            $apiId=$movie->getApiId();
            $request = new \GuzzleHttp\Psr7\Request('GET', 'https://api.themoviedb.org/3/movie/'.$apiId.'?api_key=b5bc52293943361515af8c82862fe832');
            $promise = $client->sendAsync($request)->then(function ($response) {
                $body = $response->getBody();
                $body = json_decode($body, true, 10);

                return $body['poster_path'];
            });
            $promise->wait();
            $movie->setPicture($body['poster_path']);
            $this->getDoctrine()->getManager()->persist($movie);
            $this->getDoctrine()->getManager()->flush();
        }
        $promise->wait();

        return $this->redirectToRoute('movie_index');
    }
}
