<template>
    <div class="border-b border-40 media-field p-8">

        <div>
            <h2 v-if="currentGallery || newGalleryData" class="mb-8 text-center text-90 font-normal">
                {{ field.name }} {{currentGallery ? currentGallery[galleryNameAttribute] :
                newGalleryData[galleryNameAttribute]}}
            </h2>
            <h2 v-else class="mb-8 text-center text-90 font-normal">
                Галлереи отсутствуют
            </h2>
            <button type="button" class="btn btn-default btn-primary" @click.prevent="showGalleryFields = true">
                {{currentGallery || newGalleryData ? 'Редактировать галлерею' : 'Создать галлерею'}}
            </button>

            <select-control v-if="!field.singular"
                            v-model="currentGallery"
                            class="w-full form-control form-select"
                            :options="field.allGalleries"
            >
                <option value="" selected :disabled="!field.nullable">{{
                    __('Choose an option')
                    }}
                </option>
            </select-control>
        </div>

        <gallery-custom-fields v-if="showGalleryFields"
                               @close="showGalleryFields = false"
                               @new-gallery-data="handleGallery"
                               :custom-fields="customGalleryFields"
        ></gallery-custom-fields>

        <input type="file" ref="photo" multiple accept="image/*" max="5" style="display: none;" @change="loadPhoto">

        <div class="w-full pt-8">

            <button type="button" v-if="currentGallery || newGalleryData" class="btn btn-default btn-primary"
                    @click.prevent="$refs.photo.click()">
                Добавить новую фотографию
            </button>
            <button type="button" class="btn btn-default btn-primary" @click.prevent="testRequest">
                Иммитировать запрос
            </button>

            <div v-if="currentGallery || newGalleryData" class="flex flex-wrap py-8 -mx-2">
                <div v-for="(m, i) in media" :key="m.id"
                     class="p-2 w-1/4">
                    <div class="card relative card relative border border-lg border-50 overflow-hidden p-2 inline-block w-full">
                        <div v-if="m.file" class="absolute mr-2 bg-success rounded px-2 py-1 text-white"
                             style="right: 0">
                            {{__('New')}}
                        </div>
                        <img :src="m.preview || m.original" class="picture m-auto block" alt="">
                    </div>
                    <p v-if="m.preview || m.original" class="flex items-center justify-between text-sm mt-3 px-2">
                        <download-button v-if="field.downloadable && !m.file"
                                         :href="m.preview || m.original"></download-button>
                        <base-button class="text-success" @click-or-enter="openCropper(m)">
                            {{ __('Crop') }}
                        </base-button>
                        <base-button class="text-danger" @click-or-enter="deleteImage(m, i)">
                            {{ __('Delete') }}
                        </base-button>
                    </p>
                </div>

            </div>
        </div>

        <cropper v-if="showCropper && useCropper"
                 :img-src="selectedMedia.original"
                 :crop-data="selectedMedia.cropBoxData || {}"
                 :aspectRatio="aspectRatio"
                 @cropped="saveNewCropData"
                 @close="showCropper = false"
        ></cropper>

        <!--<div style="display: none;visibility: hidden;opacity: 0">-->
            <!--<default-field :field="field" :errors="errors">-->
                <!--<template slot="field">-->
                    <!--<input :id="field.attribute" type="text"-->
                           <!--class="w-full form-control form-input form-input-bordered"-->
                           <!--:class="errorClasses"-->
                           <!--:placeholder="field.name"-->
                           <!--v-model="value"-->
                    <!--/>-->
                <!--</template>-->
            <!--</default-field>-->
        <!--</div>-->
    </div>
</template>

<script>
    import Vue from 'vue';


    import {FormField, HandlesValidationErrors} from 'laravel-nova'

    import Cropper from "./Cropper"
    import GalleryCustomFields from "./GalleryCustomFields";
    import DownloadButton from "./buttons/DownloadButton";
    import {convertBlobToBase64} from "../utils/convertBlobToBase64";
    import getFileExtension from "../utils/getFileExtension";
    import BaseButton from "./buttons/BaseButton";

    Vue.config.devtools = true;

    export default {
        components: {Cropper, DownloadButton, BaseButton, GalleryCustomFields},

        mixins: [FormField, HandlesValidationErrors],

        props: ['resourceName', 'resourceId', 'field'],

        data() {
            return {
                currentGallery: null,
                allGalleries: [],
                newGalleryData: null,
                detachedGalleries: [],
                customGalleryFields: null,
                showGalleryFields: false,

                galleryNameAttribute: '',

                media: [],

                selectedMedia: {},

                deletedMedia: [],

                isSingle: true,

                //old
                value: false,
                name: false,

                useCropper: false,
                aspectRatio: null,

                showCropper: false,
            }
        },

        methods: {
            handleGallery(data) {
                this.newGalleryData = data;
                this.setCustomFieldsValues(data);

                if (this.currentGallery) {
                    this.currentGallery = Object.assign(this.currentGallery, data);
                }
            },
            openCropper(media) {
                this.selectedMedia = media;
                this.showCropper = true;
            },
            saveNewCropData({cropBoxData, dataUrl}) {
                // update current media crop data
                this.selectedMedia.cropBoxData = cropBoxData;
                //update current media preview
                this.selectedMedia.preview = dataUrl;
            },
            loadPhoto(e) {

                //get files from drop or change event
                let files = e.dataTransfer ? e.dataTransfer.files : e.target.files;

                if (!files.length) return;

                // for each file generate object and push to media and newMedia
                [...files].forEach(f => convertBlobToBase64(f).then(
                    src => ({
                        id: _.uniqueId('url_'),
                        preview: src,
                        original: src,
                        file: f,
                        cropBoxData: {}
                    })
                ).then(media => {
                    this.media.push(media);
                    // this.newMedia.push(media);
                }));

                //reset input value
                this.$refs.photo.value = null;

                if (!/safari/i.test(navigator.userAgent)) {
                    this.$refs.photo.type = '';
                    this.$refs.photo.type = 'file';
                }

            },
            deleteImage(media, i) {
                // if existing media push to delete
                if (!media.file) {
                    this.deletedMedia = _.uniq(_.concat(this.deletedMedia, [media.id]));
                }
                // delete from media
                this.media.splice(i, 1);
            },

            testRequest() {
                const fd = new FormData();
                axios.post('/test', this.fill(fd))
                    .then(({data}) => console.log(data))
                    .catch(error => console.log(error));
            },
            /**
             * Fill the given FormData object with the field's internal value.
             */
            fill(formData) {

                if (this.media.some(m => Boolean(m.file))) {
                    formData = this.strategyNewMedia(formData);
                }

                if (!this.currentGallery && this.newGalleryData) {
                    return this.strategyCreate(formData);
                }

                if (this.currentGallery) {
                    return this.strategyUpdate(formData);
                }
                return formData;
            },

            strategyNewMedia(formData) {
                this.media.filter(m => m.file)
                    .map(media => _.pick(media, ['id', 'file', 'cropBoxData']))
                    .forEach(m => {
                        formData.append(`new[${m.id}][file]`, m.file);
                        formData.append(`new[${m.id}][cropData]`, JSON.stringify(m.cropBoxData));
                    });
                return formData
            },
            strategyCreate(formData) {
                formData.append('gallery_strategy', 'create');
                formData.append('new_gallery', JSON.stringify(this.newGalleryData));
                return formData;
            },
            strategyUpdate(formData) {
                const updatedMedia = this.media.filter(m => m.wasUpdated)
                    .map(media => _.pick(media, ['id', 'cropBoxData']));

                formData.append('gallery_strategy', 'update');
                formData.append('current_gallery_id', this.currentGallery.id);

                formData.append('deleted_media', JSON.stringify(this.deletedMedia));
                formData.append('updated_media', JSON.stringify(updatedMedia));
                formData.append('updated_gallery_data', JSON.stringify(this.newGalleryData));
                formData.append('detached_galleries', JSON.stringify(this.detachedGalleries));
                return formData;
            },
            /**
             * Update the field's internal value.
             */
            handleChange(value) {
                this.value = value
            },

            //gallery methods

            setCustomFieldsValues(from) {
                this.customGalleryFields = this.field.customGalleryFields.map(f => {
                    const value = from[f.attribute];
                    return {...f, value}
                });
            },

            createGallery() {

            }

        },
        computed: {
            requestData() {
                return JSON.stringify(this.cropData)
            },
        },
        mounted() {
            const {
                galleryNameAttribute = 'name',
                previewFormUrl,
                previewUrl,
                albumRelationName,
                mediaRelationName,
            } = this.field;

            this.galleryNameAttribute = galleryNameAttribute;

            this.allGalleries = this.field.galleriesCollection || [];

            if (this.allGalleries.length) {
                this.currentGallery = this.allGalleries[0]
            }

            if (this.currentGallery) {
                this.media = this.currentGallery[mediaRelationName].map(m => {
                        m.original = m[previewUrl] || null;
                        m.preview = m[previewFormUrl] || m[previewUrl] || null;
                        m.wasUpdated = false;
                        m.cropBoxData = {};
                        return m;
                    }
                );

                this.setCustomFieldsValues(this.currentGallery);
            } else {
                this.customGalleryFields = this.field.customGalleryFields;
            }

            //old
            this.useCropper = this.field.useCropper;
            this.aspectRatio = this.field.aspectRatio;
        }
    }
</script>

<style scoped>
    .picture {
        height:          250px;
        object-fit:      contain;
        object-position: center;
    }
</style>
