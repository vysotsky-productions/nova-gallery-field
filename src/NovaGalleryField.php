<?php

namespace VysotskyProductions\NovaGalleryField;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class NovaGalleryField extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'NovaGalleryField';
    public $name;
    public $galleriesCollection;

    public function __construct($name, $galleriesCollection)
    {
        $this->name = $name;
        $this->galleriesCollection = $galleriesCollection;
    }

    public $deletable = true;

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
        $cropData = json_decode($request[$attribute . "_crop_data"]);

        if ($request[$attribute . "_delete_id"]) {
            $model->{$attribute}()->dissociate();
            $model->save();
            $this->handler->delete($request[$attribute . "_delete_id"]);
        }

        if ($request->file($attribute . "_file")) {

            $media = $this->handler->save(
                $request->file($attribute . "_file"),
                $cropData
            );
            $model->{$attribute}()->associate($media);
            $model->save();
        }

        if ($request[$attribute . "_update_id"] && $cropData) {
            $this->handler->update($request[$attribute . "_update_id"], $cropData);
        }
    }


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


    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'downloadable' => $this->downloadable,
            'deletable' =>  $this->deletable,
            'useCropper' => $this->useCropper,
            'galleriesCollection' => $this->galleriesCollection,
            'customGalleryFields' => $this->customGalleryFields
        ]);
    }
}
