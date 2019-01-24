<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Form\ActorType;
use App\Repository\ActorRepository;
use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/actor")
 */
class ActorController extends AbstractController
{
    /**
     * @Route("/", name="actor_index", methods={"GET"})
     */
    public function index(ActorRepository $actorRepository): Response
    {
        return $this->render('actor/index.html.twig', [
            'actors' => $actorRepository->findAll(),
        ]);
    }

    /**
     * @Route("/admin/new", name="actor_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $actor = new Actor();
        $form = $this->createForm(ActorType::class, $actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($actor);
            $entityManager->flush();

            return $this->redirectToRoute('actor_index');
        }

        return $this->render('actor/new.html.twig', [
            'actor' => $actor,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="actor_show", methods={"GET"})
     */
    public function show(Actor $actor): Response
    {
        return $this->render('actor/show.html.twig', [
            'actor' => $actor,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="actor_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Actor $actor): Response
    {
        $form = $this->createForm(ActorType::class, $actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('actor_index', [
                'id' => $actor->getId(),
            ]);
        }

        return $this->render('actor/edit.html.twig', [
            'actor' => $actor,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="actor_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Actor $actor): Response
    {
        if ($this->isCsrfTokenValid('delete'.$actor->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($actor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('actor_index');
    }

    /**
     * @Route("/admin/import/actors", name="import_actors", methods={"GET"})
     */
    public function importActors(MovieRepository $movieRepository, ActorRepository $actorRepository):Response
    {
        $client = new \GuzzleHttp\Client();

        $movies=$movieRepository->findAll();



        /*
        foreach ($movies as $key=>$movie) {
            $apiId=$movie->getApiId();
            $response = $client->get('https://api.themoviedb.org/3/movie/'.$apiId.'/credits?api_key=b5bc52293943361515af8c82862fe832');
            $body = $response->getBody();
            $body = json_decode($body, true, 10);

            foreach ($body['crew'] as $key => $value) {
                if ($value['job']=='Director') {
                    $director=$value['name'];
                }
            }

            $actors=$actorRepository->findAll();
            foreach ($actors as $key2 => $value2) {
                $value2->addMovie()
            }
            $movie->setDirector($director);
            $this->getDoctrine()->getManager()->persist($movie);
            $this->getDoctrine()->getManager()->flush();
            sleep( 1 );
        }  */

        return $this->redirectToRoute('actor_index');
    }
}
