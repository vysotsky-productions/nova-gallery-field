<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 9/30/2019
 * Time: 3:27 PM
 */

namespace VysotskyProductions\NovaGalleryField\Traits;


trait GalleryMeta
{
    public function aspectRatio(float $aspectRatio)
    {
        return $this->withMeta(compact('aspectRatio'));
    }

    public function params(array $params)
    {
        return $this->withMeta(['params' => $params]);
    }
}