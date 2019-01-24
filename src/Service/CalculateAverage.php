<?php
/**
 * Created by PhpStorm.
 * User: wilder21
 * Date: 24/01/19
 * Time: 17:08
 */

namespace App\Service;

use App\Entity\Movie;
use App\Repository\RatingRepository;

class CalculateAverage
{
    public function calculateAverage(Movie $movie, RatingRepository $ratingRepository)
    {
        $ratings=$ratingRepository->findBy(['movie'=>$movie]);
        $sum=0;
        $result=0;
        foreach ($ratings as $key=>$value) {
            $sum+=$value->getValue();
        }
        if (count($ratings)!=0) {
            $result=$sum/count($ratings);
        }
        return $result;
    }
}