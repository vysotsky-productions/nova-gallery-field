<template>
    <div class="border-b border-40 media-field px-8">

        <h2 class="text-80 pt-2 leading-tight">
            {{ field.name }}
        </h2>
        <input type="file" ref="photo" style="display: none;" @change="loadPhoto">

        <div class="py px-8 w-full">


            <div class="w-1/2"
                 v-if="customGalleryFields">
                <component class="border-0" :key="custom.attribute" v-for="(custom, i) in customGalleryFields"
                           :is="'form-'+custom.component"
                           :field="custom"
                >

                </component>
            </div>

            <h4 class="m-3">All media in this album</h4>
            <div class="flex flex-wrap py-8 -mx-2">
                <div v-for="(m, i) in media" :key="m.id"
                     class="px-2 w-1/5">
                    <div class="card relative card relative border border-lg border-50 overflow-hidden p-2 inline-block">
                        <img :src="m.preview || m.original" class="picture" alt="">
                    </div>
                    <p v-if="m.preview || m.original" class="flex items-center justify-between text-sm mt-3 px-2">
                        <download-button v-if="field.downloadable" :href="m.preview || m.original"></download-button>
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


        <!--        <div class="py-6 px-8 w-4/5">-->
        <!--            <div v-if="original"-->
        <!--                 @click="openCropper"-->
        <!--                 style="max-width: 320px"-->
        <!--                 class="card relative card relative border border-lg border-50 overflow-hidden px-2 py-2 inline-block"-->
        <!--            >-->
        <!--                <img :src="preview || original" class="image-preview">-->
        <!--            </div>-->

        <!--            <div v-else-->
        <!--                 @drop.prevent="loadPhoto" @dragover.prevent-->
        <!--                 class="border border-primary-30% flex hover:border-primary overflow-hidden rounded relative text-primary-30% hover:text-primary"-->
        <!--                 style="width: 250px; height: 250px"-->
        <!--                 @click="$refs.photo.click()">-->
        <!--                <icon type="add" width="50" height="50" class="m-auto"/>-->
        <!--            </div>-->

        <!--        </div>-->

        <cropper v-if="showCropper && useCropper"
                 :img-src="selectedMedia.original"
                 :crop-data="selectedMedia.cropBoxData || {}"
                 :aspectRatio="aspectRatio"
                 @cropped="saveNewCropData"
                 @close="showCropper = false"
        ></cropper>

        <div style="display: none;visibility: hidden;opacity: 0">
            <default-field :field="field" :errors="errors">
                <template slot="field">
                    <input :id="field.attribute" type="text"
                           class="w-full form-control form-input form-input-bordered"
                           :class="errorClasses"
                           :placeholder="field.name"
                           v-model="value"
                    />
                </template>
            </default-field>
        </div>
    </div>
</template>

<script>
    import Vue from 'vue';


    import {FormField, HandlesValidationErrors} from 'laravel-nova'

    import Cropper from "./Cropper"
    import DownloadButton from "./buttons/DownloadButton";
    import {convertBlobToBase64} from "../utils/convertBlobToBase64";
    import getFileExtension from "../utils/getFileExtension";
    import BaseButton from "./buttons/BaseButton";

    Vue.config.devtools = true;

    export default {
        components: {Cropper, DownloadButton, BaseButton},

        mixins: [FormField, HandlesValidationErrors],

        props: ['resourceName', 'resourceId', 'field'],

        data() {
            return {
                currentGallery: {},
                allGalleries: [],
                customGalleryFields: null,

                media: [],

                selectedMedia: {},

                updatedMedia: [],
                newMedia: [],
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
            openCropper(media) {
                this.selectedMedia = media;

                console.log(media.cropBoxData);
                this.showCropper = true;
            },
            saveNewCropData({cropBoxData, dataUrl}) {
                const {id} = this.selectedMedia;
                if (id) {
                    const media = this.media.find(m => m.id === id);
                    media
                        ? media.cropBoxData = cropBoxData
                        : this.updatedMedia.push(this.selectedMedia)
                } else {
                    const {file} = this.selectedMedia;
                    this.newMedia.push({
                        file, cropBoxData
                    })
                }
                this.selectedMedia.preview = dataUrl;
                console.log(this.updatedMedia)
            },
            loadPhoto(e) {
                let file = e.dataTransfer ? e.dataTransfer.files[0] : e.target.files[0];
                if (!file) return;

                convertBlobToBase64(file).then(img => {
                    this.original = img;
                    this.preview = img;
                    this.newPhoto = file;
                });

                this.$refs.photo.value = null;

                if (!/safari/i.test(navigator.userAgent)) {
                    this.$refs.photo.type = '';
                    this.$refs.photo.type = 'file';
                }

            },
            deleteImage(media, i) {
                if (media.id) {
                    this.deletedMedia = _.uniq(_.concat(this.deletedMedia, media), 'id');
                }
                this.media.splice(i, 1);
            },
            /**
             * Fill the given FormData object with the field's internal value.
             */
            fill(formData) {
                formData.append(`${this.field.attribute}_crop_data`, this.requestData || '');
                formData.append(`${this.field.attribute}_file`, this.newPhoto || '');
                formData.append(`${this.field.attribute}_delete_id`, this.deleteId || '');
                formData.append(`${this.field.attribute}_update_id`, this.mediaId || '');
            },

            /**
             * Update the field's internal value.
             */
            handleChange(value) {
                this.value = value
            },

            //gallery methods

            setCustomFieldsValues() {
                this.customGalleryFields = this.field.customGalleryFields.map(f => {
                    const value = this.currentGallery[f.attribute];
                    const attribute = `gallery_custom_${f.attribute}_${this.currentGallery.id}`
                    return {...f, value, attribute}
                })
            }

        },
        computed: {
            requestData() {
                return JSON.stringify(this.cropData)
            },
        },
        mounted() {
            const {value, previewFormUrl, previewUrl} = this.field;

            this.allGalleries = this.field.galleriesCollection || [];

            if (this.allGalleries.length) {
                this.currentGallery = this.allGalleries[0]
            }

            if (this.currentGallery) {
                this.media = this.currentGallery.media.map(m => {
                        m.original = m[previewUrl] || null;
                        m.preview = m[previewFormUrl] || m[previewUrl] || null;

                        m.cropBoxData = {};
                        return m;
                    }
                );

                this.setCustomFieldsValues();
                console.log(this.customGalleryFields)
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
