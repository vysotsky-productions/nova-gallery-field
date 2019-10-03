<?php

namespace VysotskyProductions\NovaGalleryField;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Support\Collection;
use VysotskyProductions\NovaGalleryField\Traits\GalleryMeta;

class NovaGalleryField extends Field
{
    use GalleryMeta;
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'NovaGalleryField';

    public $showOnCreation = false;

    public $name;
    public $galleriesCollection;
    public $singular = true;
    public $sortable = false;
    public $sortableColumn = 'order';

    public $albumRelationName = 'album';
    public $mediaRelationName = 'media';

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
    }

    public $deletable = false;

    public $downloadable = true;

    public $useCropper = true;

    public $handler;

    public $customGalleryFields = [];

    /**
     * @param bool $deletable
     * @return NovaGalleryField
     */
    public function setDeletable(bool $deletable): NovaGalleryField
    {
//        todo:implement deletable logic only if deletable is true
        $this->deletable = $deletable;
        return $this;
    }

    public function setSortable(string $sortableColumn = 'order')
    {
        $this->sortableColumn = $sortableColumn;
        $this->sortable = true;
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

    /**
     * @return NovaGalleryField
     */
    public function disableCropper(): NovaGalleryField
    {
        $this->useCropper = false;
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

    protected function fillAttributeFromRequest(NovaRequest $request,
                                                $requestAttribute,
                                                $model,
                                                $attribute)
    {
        if ($request->get('gallery_strategy') === 'create') {

            $newGalleryAttrs = json_decode($request['new_gallery'], true) ?? [];


            $album = $model->{$this->albumRelationName}()->create($newGalleryAttrs);

            //todo: check if works without commented lines
//            if ($this->singular) {
//                $model->{$this->albumRelationName}()->associate($album);
//            } else {
//                $model->{$this->albumRelationName}()->attach($album);
//            }

            $mediaIds = $this->handler->save($request['new'])->pluck('id');

            $album->{$this->mediaRelationName}()->attach($mediaIds);
        }

        if ($request->get('gallery_strategy') === 'update') {

//            2.1 update gallery custom attributes
            $newGalleryAttrs = json_decode($request['updated_gallery_data'], true) ?? [];
            if ($this->singular) {
                $album = tap($model->{$this->albumRelationName})
                    ->update($newGalleryAttrs);
            } else {
                $album = tap($model->{$this->albumRelationName}()
                    ->find($request['current_gallery_id']))
                    ->update($newGalleryAttrs);
            }

            if (!$this->singular && $this->sortable && $request->has('galleries_order')) {
                $model->{$this->albumRelationName}()->syncWithoutDetaching($request['galleries_order']);
            }
//         *   2.2 save new media if exists with user class method
            $mediaIds = $this->handler->save($request['new'])->pluck('id');
//         *   2.3 attach media to gallery
//            2.3.a if sortable combine with order data
            if ($this->sortable && $request->has('new_media_order')) {
                $mediaIds = collect($mediaIds)->combine($request['new_media_order']);
            }
            $album->{$this->mediaRelationName}()->attach($mediaIds);
//           2.4 update media
            $this->handler->update(json_decode($request['updated_media'], true));

//            2.4.a update pivot order if exist
            if ($this->sortable && $request->has('existing_media_order')) {
                $album->{$this->mediaRelationName}()->syncWithoutDetaching($request['existing_media_order']);
            }

//            *   2.5 delete media if has media_deleted (delete files with user func if deletable set to true)
            if ($request->has('deleted_media')) {
                if ($this->deletable) $this->handler->delete(json_decode($request['deleted_media']));
                $album->{$this->mediaRelationName}()->detach(json_decode($request['deleted_media']));
            }
//         *   2.6 detach galleries if detached_galleries
            if ($this->singular === false && $request->has('detached_galleries')) {
                $model->{$this->albumRelationName}()->detach(json_decode($request['detached_galleries']));
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
            'useCropper' => $this->useCropper,
            'galleriesCollection' => $this->galleriesCollection,
            'customGalleryFields' => $this->customGalleryFields,
            'albumRelationName' => $this->albumRelationName,
            'mediaRelationName' => $this->mediaRelationName,
            'singular' => $this->singular,
            'sortable' => $this->sortable,
            'sortableColumn' => $this->sortableColumn
        ]);
    }
}
