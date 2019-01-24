<?php
/**
 * Created by PhpStorm.
 * User: wilder21
 * Date: 18/01/19
 * Time: 12:24
 */

namespace App\Controller;

use App\Repository\MovieRepository;
use App\Repository\RatingRepository;
use App\Service\CalculateAverage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home", methods={"GET"})
     */
    public function index(MovieRepository $movieRepository, RatingRepository $ratingRepository, CalculateAverage $calculateAverage): Response
    {
        $movies=$movieRepository->findAll();
        $ratings=[];
        foreach ($movies as $key=>$movie) {
            $average=$calculateAverage->calculateAverage($movie, $ratingRepository);
            $id=$movie->getId();
            $ratings[$id]=$average;
        }
        arsort($ratings);
        $topRated=array_slice($ratings, 0, 9, true);
        $topRated=array_keys($topRated);
        $topRatedMovies=[];
        $topRatedId=[];
        foreach ($topRated as $id) {
            $topRatedMovies[]=$movieRepository->findOneBy(['id'=>$id])->getPicture();
            $topRatedId[]=$id;
        }
        return $this->render('home/home.html.twig', ['topRated'=>$topRatedMovies, 'topRatedId'=>$topRatedId]);
    }
}