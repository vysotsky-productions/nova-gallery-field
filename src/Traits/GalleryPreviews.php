<?php


namespace VysotskyProductions\NovaGalleryField\Traits;


trait GalleryPreviews
{
    protected $previewUrl;
    protected $previewDetailUrl;
    protected $previewFormUrl;
    protected $previewIndexUrl;
    protected $cropBoxDataField;

    public function setGalleryDefaults($config)
    {
        return $this->getPhoto($config['original'])
            ->getPhotoForm($config['photo_on_form'])
            ->getPhotoDetail($config['photo_on_detail'])
            ->getPhotoIndex($config['photo_on_index']);
    }

    public function getGalleryPreviewFields(): array
    {
        return [
            'previewUrl' => $this->previewUrl,
            'previewDetailUrl' => $this->previewDetailUrl,
            'previewFormUrl' => $this->previewFormUrl,
            'previewIndexUrl' => $this->previewIndexUrl,
        ];
    }

    public function getPhoto(string $previewUrl = null)
    {
        $this->previewUrl = $previewUrl;
        return $this;
    }

    public function getPhotoDetail(string $previewDetailUrl = null)
    {
        $this->previewDetailUrl = $previewDetailUrl;
        return $this;
    }

    public function getPhotoForm(string $previewFormUrl = null)
    {
        $this->previewFormUrl = $previewFormUrl;
        return $this;
    }

    public function getPhotoIndex(string $previewIndexUrl = null)
    {
        $this->previewIndexUrl = $previewIndexUrl;
        return $this;
    }
}