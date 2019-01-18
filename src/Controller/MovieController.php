<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieType;
use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/movie")
 */
class MovieController extends AbstractController
{
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
     * @Route("/{id}", name="movie_show", methods={"GET"})
     */
    public function show(Movie $movie): Response
    {
        return $this->render('movie/show.html.twig', [
            'movie' => $movie,
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
     * @Route("/admin/import/movies", name="import_movies", methods={"GET","POST"})
     */
    public function importMovies()
    {
        $client = new \GuzzleHttp\Client();

        $request = new \GuzzleHttp\Psr7\Request('GET', 'https://api.themoviedb.org/3/movie/top_rated?api_key=b5bc52293943361515af8c82862fe832&page=25');
            $promise = $client->sendAsync($request)->then(function ($response) {
                $body = $response->getBody();
                $body = json_decode($body, true, 10);
                var_dump($body);
            });

        $promise->wait();

        die();
    }

    /**
     * @Route("/admin/import/actors", name="import_actors", methods={"GET","POST"})
     */
    public function importActors()
    {
        $client = new \GuzzleHttp\Client();

        $request = new \GuzzleHttp\Psr7\Request('GET', 'https://api.themoviedb.org/3/person/popular?api_key=b5bc52293943361515af8c82862fe832&page=25');
        $promise = $client->sendAsync($request)->then(function ($response) {
            $body = $response->getBody();
            $body = json_decode($body, true, 10);
            var_dump($body);
        });

        $promise->wait();

        die();
    }
}
