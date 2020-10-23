<?php

namespace VysotskyProductions\NovaGalleryField;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Support\Collection;
use VysotskyProductions\NovaGalleryField\Traits\GalleryCropper;
use VysotskyProductions\NovaGalleryField\Traits\GalleryMeta;
use VysotskyProductions\NovaGalleryField\Traits\GalleryPreviews;
use VysotskyProductions\NovaGalleryField\Traits\GallerySort;

class NovaGalleryField extends Field
{
    use GalleryMeta;
    use GalleryPreviews;
    use GallerySort;
    use GalleryCropper;
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'NovaGalleryField';

    public $showOnIndex = false;
    public $showOnDetail = false;
    public $showOnCreation = false;
    public $showOnUpdate = true;

    public $name;
    public $galleriesCollection;
    public $singular = true;

    public $albumRelationName = 'album';
    public $mediaRelationName = 'media';

    public $deleteOrDetach = false;

    public function __construct($name, $galleriesCollection, $albumRelationName = 'album', $mediaRelationName = 'media')
    {
        $this->name = $name;
        if ($galleriesCollection instanceof Collection) {
            $this->galleriesCollection = $galleriesCollection;
        } else {
            $this->galleriesCollection = [$galleriesCollection];
        }
        $this->albumRelationName = $albumRelationName;
        $this->mediaRelationName = $mediaRelationName;

        $this->setGalleryDefaults(config('nova-gallery-field.photo_attributes'));
        $this->setSortableDefaults(config('nova-gallery-field.order'));
        $this->setCropperDefaults(config('nova-gallery-field.cropper'));
    }

    public $deletable = false;

    public $downloadable = true;


    public $handler;

    public $customGalleryFields = [];

    /**
     * @param bool $deletable
     * @return NovaGalleryField
     */
    public function setDeletable(bool $deletable): NovaGalleryField
    {
        $this->deletable = $deletable;
        return $this;
    }


    public function multiple()
    {
        $this->singular = false;
        return $this;
    }

    /**
     * @param mixed $handler
     * @return NovaGalleryField
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
        return $this;
    }

    /**
     * @param bool $downloadable
     * @return NovaGalleryField
     */
    public function setDownloadable(bool $downloadable): NovaGalleryField
    {
        $this->downloadable = $downloadable;
        return $this;
    }


    public function disableDownload()
    {
        $this->downloadable = false;

        return $this;
    }

    /**
     * @param array $customGalleryFields
     * @return NovaGalleryField
     */
    public function setCustomGalleryFields(array $customGalleryFields): NovaGalleryField
    {
        $this->customGalleryFields = $customGalleryFields;
        return $this;
    }

    /**
     * if sets to true user can handle detach / delete step by his own
     * with image handler method deleteOrDetach
     *
     * @param bool $deleteOrDetach
     * @return NovaGalleryField
     */
    public function useDeleteOrDetach(bool $deleteOrDetach = true): NovaGalleryField
    {
        $this->deleteOrDetach = $deleteOrDetach;
        return $this;
    }

    protected function fillAttributeFromRequest(NovaRequest $request,
                                                $requestAttribute,
                                                $model,
                                                $attribute)
    {
        $gallery = new Gallery($this, $model);
        $galleryRequest = new GalleryRequest($request, $this->albumRelationName);

        if ($galleryRequest->get('gallery_strategy') === 'create') {
            $gallery->createAlbum($galleryRequest->getAssocDecoded('new_gallery'))
                ->setMedia()
                ->attachMedia(
                    $this->handler->save($galleryRequest->get('new'))->pluck('id'),
                    $galleryRequest->get('new_media_order')
                );

        }
        if ($galleryRequest->get('gallery_strategy') === 'update') {
            $gallery
                ->setAlbum($galleryRequest->get('current_gallery_id'))
                ->setMedia()
                ->updateAlbum($galleryRequest->getAssocDecoded('updated_gallery_data'))
                ->attachMedia(
                    $this->handler->save($galleryRequest->get('new'))->pluck('id'),
                    $galleryRequest->get('new_media_order')
                )
                ->setAlbumOrder($galleryRequest->get('galleries_order'))
                ->sortMedia($galleryRequest->get('existing_media_order'));

            $this->handler->update($galleryRequest->getAssocDecoded('updated_media'));

            if($this->deleteOrDetach) {
                $this->handler->deleteOrDetach($galleryRequest->getDecoded('deleted_media'), $gallery);
            } else {
                $gallery->detachMedia($galleryRequest->getDecoded('deleted_media'));
                $gallery->detachAlbums($galleryRequest->getDecoded('detached_galleries'));

                if ($galleryRequest->has('deleted_media') && $this->deletable) {
                    $this->handler->delete($galleryRequest->getDecoded('deleted_media'));
                }
            }

        }

    }


    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'downloadable' => $this->downloadable,
            'deletable' => $this->deletable,
            'galleriesCollection' => $this->galleriesCollection,
            'customGalleryFields' => $this->customGalleryFields,
            'albumRelationName' => $this->albumRelationName,
            'mediaRelationName' => $this->mediaRelationName,
            'singular' => $this->singular,
        ], $this->getGalleryPreviewFields(), $this->getSortableFields(), $this->getGalleryCropperFields());
    }
}
