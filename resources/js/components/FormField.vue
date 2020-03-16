<template>
    <div class="border-b border-40 media-field p-8">

        <div class="flex w-full justify-between">
            <div class="flex flex-col">
                <h2 v-if="currentGallery || newGalleryData" class="font-normal mt-2">
                    {{ field.name }} {{currentGallery ? currentGallery[galleryNameAttribute] :
                    newGalleryData[galleryNameAttribute]}}
                </h2>
                <h2 v-else class="font-normal mt-2">
                    Галлереи отсутствуют
                </h2>

                <button type="button" class="btn mt-4 btn-default btn-primary" v-if="!currentGallery && !newGalleryData"
                        @click.prevent="createGallery">
                    {{__('Create')}}
                </button>

                <base-button type="edit" class="text-sm font-semibold mt-auto" v-if="currentGallery || newGalleryData"
                             @click-or-enter="editGallery">
                    {{__('Edit')}}
                </base-button>
            </div>


            <div class="w-1/2 flex flex-col" v-if="!field.singular && allGalleries && currentGallery">
                <div class="flex w-full items-center">
                    <label class="block text-80">
                        {{__('Choose gallery')}}:
                    </label>
                    <select class="flex-1 form-control form-select mr-1 ml-4"
                            @change="handleGalleryChange"
                    >
                        <option v-for="(g, i) in allGalleries" :value="i">
                            {{g[galleryNameAttribute]}}
                        </option>
                    </select>
                    <button type="button" class="btn btn-default btn-primary" v-if="canCreateGallery"
                            @click.prevent="createGallery">
                        {{__('Create')}}
                    </button>
                </div>

                <base-button type="edit" v-if="field.sortable" @click-or-enter="showGallerySortModal = true"
                             class="ml-auto text-sm font-semibold mt-4">
                    {{__('Sort Galleries')}}
                </base-button>
            </div>

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

        <!--<button type="button" v-if="currentGallery || newGalleryData" class="btn btn-default btn-primary"-->
        <!--@click.prevent="$refs.photo.click()">-->
        <!--Добавить новую фотографию-->
        <!--</button>-->
        <div v-if="currentGallery || newGalleryData"
             @drop.prevent="loadPhoto" @dragover.prevent
             class="w-full rounded-lg bg-40 mt-8"
             style="height: 250px;">
            <button type="button"
                    class="w-full h-full block text-2xl text-70 capitalize"
                    @click.prevent="$refs.photo.click()">
                {{__('Drop Photos Here')}}
            </button>
        </div>

        <input type="file" ref="photo" multiple accept="image/*" max="5" style="display: none;" @change="loadPhoto">

        <div class="w-full">

            <!--<button type="button" class="btn btn-default btn-primary" @click.prevent="testRequest">-->
            <!--Иммитировать запрос-->
            <!--</button>-->

            <div v-if="currentGallery || newGalleryData">

                <draggable :forceFallback="true" v-bind="dragOptions" v-if="field.sortable" class="flex w-full flex-wrap py-8 -mx-2" v-model="media">
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

    // Vue.config.devtools = true;

    export default {
        components: {Cropper, DownloadButton, BaseButton, GalleryCustomFields, Media, draggable, SortableGalleries},

        mixins: [FormField, HandlesValidationErrors],

        props: ['resourceName', 'resourceId', 'field'],

        data() {
            return {
                showGallerySortModal: false,
                galleriesOrder: false,

                editGalleryMode: true,
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
            createGallery() {
                this.editGalleryMode = false;
                this.showGalleryFields = true;
                // this.currentGallery = null;
                // this.media = [];
                // this.deletedMedia = [];
            },
            editGallery() {
                this.editGalleryMode = true;
                this.showGalleryFields = true;
            },
            handleGallerySort(newOrder) {
                this.galleriesOrder = newOrder;
            },
            handleGalleryChange({target}) {
                this.currentGallery = this.allGalleries[target.value];
                const {
                    mediaRelationName,
                    previewFormUrl,
                    previewUrl,
                    cropBoxDataField
                } = this.field;
                this.media = this.currentGallery[mediaRelationName].map(m => {
                        m.original = m[previewUrl] || null;
                        m.preview = m[previewFormUrl] || m[previewUrl] || null;
                        m.wasUpdated = false;
                        m.cropBoxData = m[cropBoxDataField] || {};
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

                if (!this.editGalleryMode) {
                    this.currentGallery = null;
                    this.media = [];
                    this.deletedMedia = [];
                }

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
                    if (this.field.mediaToEnd) {
                        this.media.push(media);
                    } else {
                        this.media.unshift(media);
                    }
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

            namespaceRequest(str) {
                return `${this.field.albumRelationName}_` + str;
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

                if (this.field.sortable) {
                    formData = this.strategySort(formData);
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
                        formData.append(this.namespaceRequest(`new[${m.id}][file]`), m.file);
                        formData.append(this.namespaceRequest(`new[${m.id}][cropData]`), JSON.stringify(m.cropBoxData));
                    });
                return formData
            },
            strategyCreate(formData) {
                formData.append(this.namespaceRequest('gallery_strategy'), 'create');
                formData.append(this.namespaceRequest('new_gallery'), JSON.stringify(this.newGalleryData));
                return formData;
            },
            strategySort(formData) {
                if (this.allGalleries.length && this.galleriesOrder) {
                    this.galleriesOrder.forEach((id, i) => {
                        formData.append(this.namespaceRequest(`galleries_order[${id}][${this.sortableColumn}]`), i)
                    })
                }
                this.media.forEach((m, i) => {
                    if (m.file) {
                        formData.append(this.namespaceRequest(`new_media_order[][${this.sortableColumn}]`), i)
                    } else {
                        formData.append(this.namespaceRequest(`existing_media_order[${m.id}][${this.sortableColumn}]`), i)
                    }
                });
                return formData;
            },
            strategyUpdate(formData) {
                //implement gallery order
                // const galleryOrder = null;

                // if (this.field.sortable) {
                //     formData = this.strategySort(formData);
                // }

                const updatedMedia = this.media.filter(m => m.wasUpdated)
                    .map(media => _.pick(media, ['id', 'cropBoxData']));

                formData.append(this.namespaceRequest('gallery_strategy'), 'update');
                formData.append(this.namespaceRequest('current_gallery_id'), this.currentGallery.id);

                formData.append(this.namespaceRequest('deleted_media'), JSON.stringify(this.deletedMedia));
                formData.append(this.namespaceRequest('updated_media'), JSON.stringify(updatedMedia));
                formData.append(this.namespaceRequest('updated_gallery_data'), JSON.stringify(this.newGalleryData));
                formData.append(this.namespaceRequest('detached_galleries'), JSON.stringify(this.detachedGalleries));
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
            dragOptions() {
                return {
                    animation: 200,
                    group: "description",
                    disabled: false,
                    ghostClass: "ghost"
                };
            },
            isNewGallery() {
                return !this.currentGallery && this.newGalleryData;
            },
            canCreateGallery() {
                return !this.currentGallery && !this.newGalleryData
                    || !this.field.singular && this.currentGallery
            },
            canEditGallery() {
                return Boolean(this.currentGallery);
            }
        },
        created() {
            const {
                galleryNameAttribute = 'name',
                previewFormUrl,
                previewUrl,
                mediaRelationName,
                sortable,
                sortableColumn,
                cropBoxDataField,
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
                        m.cropBoxData = m[cropBoxDataField] || {};
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

    .flip-list-move {
        transition: transform 0.5s;
    }

    .no-move {
        transition: transform 0s;
    }

    .ghost {
        opacity:    0.5;
        background: #c8ebfb;
    }
</style>
