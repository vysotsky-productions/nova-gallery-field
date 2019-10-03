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

            <button type="button" class="btn btn-default btn-primary" @click.prevent="showGallerySortModal = true">
                Изменить порядок галерей
            </button>

            <select v-if="!field.singular && allGalleries && currentGallery"
                    class="w-1/4 form-control form-select"
                    @change="handleGalleryChange"
            >
                <option v-for="(g, i) in allGalleries" :value="i">
                    {{g[galleryNameAttribute]}}
                </option>
            </select>

            <button type="button" v-if="currentGallery && !field.singular" class="btn btn-default btn-primary"
                    @click.prevent="showGalleryFields = true">
                Создать галлерею
            </button>
        </div>

        <sortable-galleries v-if="showGallerySortModal && field.sortable"
                            :galleries="allGalleries"
                            :gallery-name-attribute="galleryNameAttribute"
                            @change-gallery-sort="handleGallerySort"
                            @close="showGallerySortModal = false">
        </sortable-galleries>

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

                <draggable v-if="field.sortable" class="flex flex-wrap py-8 -mx-2" v-model="media">
                    <div class="p-2 w-1/4" v-for="(m, i) in media" :key="m.id">
                        <media :media="m"
                               :src="m.preview || m.original"
                               :downloadable="field.downloadable"
                               :use-cropper="useCropper"
                               :idx="i"
                               @open-cropper="openCropper"
                               @delete-media="deleteImage"
                        ></media>
                    </div>
                </draggable>
                <div v-else class="p-2 w-1/4" v-for="(m, i) in media" :key="m.id">
                    <media :media="m"
                           :src="m.preview || m.original"
                           :downloadable="field.downloadable"
                           :use-cropper="useCropper"
                           :idx="i"
                           @open-cropper="openCropper"
                           @delete-media="deleteImage"
                    ></media>
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
    </div>
</template>

<script>
    import Vue from 'vue';


    import {FormField, HandlesValidationErrors} from 'laravel-nova'
    import draggable from 'vuedraggable';

    import Cropper from "./Cropper";
    import SortableGalleries from './SortableGalleries';
    import GalleryCustomFields from "./GalleryCustomFields";
    import DownloadButton from "./buttons/DownloadButton";
    import {convertBlobToBase64} from "../utils/convertBlobToBase64";
    import getFileExtension from "../utils/getFileExtension";
    import BaseButton from "./buttons/BaseButton";
    import Media from "./Media";

    Vue.config.devtools = true;

    export default {
        components: {Cropper, DownloadButton, BaseButton, GalleryCustomFields, Media, draggable, SortableGalleries},

        mixins: [FormField, HandlesValidationErrors],

        props: ['resourceName', 'resourceId', 'field'],

        data() {
            return {
                showGallerySortModal: false,
                galleriesOrder: false,

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

                sortableColumn: '',

                //old
                value: false,
                name: false,

                useCropper: false,
                aspectRatio: null,

                showCropper: false,
            }
        },

        methods: {
            handleGallerySort(newOrder) {
                this.galleriesOrder = newOrder;
            },
            handleGalleryChange({target}) {
                this.currentGallery = this.allGalleries[target.value];
                const {
                    mediaRelationName,
                    previewFormUrl,
                    previewUrl,
                } = this.field;
                console.log(this.currentGallery);
                this.media = this.currentGallery[mediaRelationName].map(m => {
                        m.original = m[previewUrl] || null;
                        m.preview = m[previewFormUrl] || m[previewUrl] || null;
                        m.wasUpdated = false;
                        m.cropBoxData = {};
                        return m;
                    }
                );

                this.setCustomFieldsValues(this.currentGallery);

                this.deletedMedia = [];
                this.newGalleryData = null;
            },
            handleGallery(data) {
                this.newGalleryData = data;
                this.setCustomFieldsValues(data);

                this.media = [];
                this.deletedMedia = [];

                if (this.currentGallery && this.field.singular) {
                    this.currentGallery = Object.assign(this.currentGallery, data);
                } else {
                    this.currentGallery = null;
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

                if (!this.selectedMedia.file) {
                    this.selectedMedia.wasUpdated = true;
                }
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
            deleteImage({media, idx}) {
                // if existing media push to delete
                if (!media.file) {
                    this.deletedMedia = _.uniq(_.concat(this.deletedMedia, [media.id]));
                }
                // delete from media
                this.media.splice(idx, 1);
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
            strategySort(formData) {
                if (this.allGalleries.length && this.galleriesOrder) {
                    this.galleriesOrder.forEach((id, i) => {
                        formData.append(`galleries_order[${id}][${this.sortableColumn}]`, i)
                    })
                }
                this.media.forEach((m, i) => {
                    if (m.file) {
                        formData.append(`new_media_order[][${this.sortableColumn}]`, i)
                    } else {
                        formData.append(`existing_media_order[${m.id}][${this.sortableColumn}]`, i)
                    }
                });
                return formData;
            },
            strategyUpdate(formData) {
                //implement gallery order
                // const galleryOrder = null;

                if (this.field.sortable) {
                    formData = this.strategySort(formData);
                }

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
                sortable,
                sortableColumn
            } = this.field;

            this.galleryNameAttribute = galleryNameAttribute;

            this.allGalleries = this.field.galleriesCollection || [];

            if (this.allGalleries.length) {
                this.currentGallery = this.allGalleries[0]
            }

            if (sortable) {
                this.sortableColumn = sortableColumn;
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
