<?php


namespace VysotskyProductions\NovaGalleryField\Traits;


trait GalleryCropper
{
    protected $cropBoxDataField;
    protected $useCropper = true;

    public function setCropperDefaults($config)
    {
        $this->setCropBoxDataField($config['crop_field'])
            ->setUseCropper($config['use_cropper']);
    }

    public function getGalleryCropperFields(): array
    {
        return [
            'cropBoxDataField' => $this->cropBoxDataField,
            'useCropper' => $this->useCropper
        ];
    }

    public function setUseCropper(bool $useCropper = true)
    {
        $this->useCropper = $useCropper;
        return $this;
    }

    public function setCropBoxDataField(string $cropBoxDataField)
    {
        $this->cropBoxDataField = $cropBoxDataField;
        return $this;
    }
}