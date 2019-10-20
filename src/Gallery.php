<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10/4/2019
 * Time: 2:51 PM
 */

namespace VysotskyProductions\NovaGalleryField;


class Gallery
{
    private $albumRelationName;
    private $mediaRelationName;
    private $model;
    private $album;
    private $media;
    private $singular;

    public function __construct(NovaGalleryField $field, $model)
    {
        $this->albumRelationName = $field->albumRelationName;
        $this->mediaRelationName = $field->mediaRelationName;
        $this->model = $model;
        $this->singular = $field->singular;
    }

    public function setAlbum($id)
    {
        if ($this->singular) {
            $this->album = $this->model->{$this->albumRelationName};
        } else {
            $this->album = $this->model->{$this->albumRelationName}()
                ->find($id);
        }
        return $this;
    }

    public function createAlbum($newGalleryAttrs)
    {
        $this->album = $this->model->{$this->albumRelationName}()->create($newGalleryAttrs);

        if ($this->singular) {
            $this->model->{$this->albumRelationName}()->associate($this->album);
        }

        return $this;
    }

    public function updateAlbum($newGalleryAttrs)
    {
        $this->album->update($newGalleryAttrs);
        return $this;
    }

    public function detachAlbums($ids)
    {
        if ($this->singular === false && $ids && count($ids)) {
            return $this->model->{$this->albumRelationName}()->detach($ids);
        }
        return $this;
    }

    public function setMedia()
    {
        $this->media = $this->album->{$this->mediaRelationName}();
        return $this;
    }

    public function attachMedia($mediaIds, $order)
    {
        if ($order && $mediaIds && (count($order) === count($mediaIds))) {
            $this->media->attach(collect($mediaIds)->combine($order));
        } else {
            $this->media->attach($mediaIds);
        }
        return $this;
    }

    public function detachMedia(array $ids = [])
    {
        if ($ids && count($ids)) {
            $this->media->detach($ids);
        }
        return $this;
    }


    public function sortMedia($idsWithOrder)
    {
        if ($idsWithOrder && count($idsWithOrder)) {
            $this->media->syncWithoutDetaching($idsWithOrder);
        }
        return $this;
    }

    public function setAlbumOrder($idsWithOrder)
    {
        if ($this->singular === false && $idsWithOrder && count($idsWithOrder)) {
            $this->model->{$this->albumRelationName}()->syncWithoutDetaching($idsWithOrder);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAlbum()
    {
        return $this->album->get();
    }
}