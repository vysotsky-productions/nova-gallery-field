# nova-photo-field

```
   composer require vysotsky-productions/nova-gallery-field

   php artisan vendor:publish --tag=nova-gallery-field
   

 NovaGalleryField::make('Альбом', $this->albums, 'albums')
                ->aspectRatio(3/4)
                ->setUseCropper($bool default = true)
                ->setCropBoxDataField('crop_data_field')
                ->getPhoto('original_url')
                ->mediaToEnd()
                ->getPhotoForm('preview_url')
                ->getPhotoDetail('preview_url')
                ->getPhotoIndex('preview_url')
                ->cropBoxDataField('crop_data')
                ->setCustomGalleryFields([
                    Text::make('name'),
                    Text::make('description')
                ])
                ->multiple()
                ->setSortable('order')
                ->setHandler(
                    new SavePhotoCollection(
                        new SavePhoto('persons/albums', config('thumbs.user.persons/avatar'))
                    )
                )
```
