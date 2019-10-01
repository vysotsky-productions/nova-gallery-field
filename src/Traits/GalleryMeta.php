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

    public function getPhoto(string $previewUrl = null)
    {
        return $this->withMeta(compact('previewUrl'));
    }

    public function getPhotoDetail(string $previewDetailUrl = null)
    {
        return $this->withMeta(compact('previewDetailUrl'));
    }

    public function getPhotoForm(string $previewFormUrl = null)
    {
        return $this->withMeta(compact('previewFormUrl'));
    }

    public function getPhotoIndex(string $previewIndexUrl = null)
    {
        return $this->withMeta(compact('previewIndexUrl'));
    }
}