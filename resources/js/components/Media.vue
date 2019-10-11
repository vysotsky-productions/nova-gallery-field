<template>
    <div :key="media.id">
        <div class="card relative card relative border border-lg border-50 overflow-hidden p-2 inline-block w-full">
            <div v-if="media.file" class="absolute mr-2 bg-success rounded px-2 py-1 text-white"
                 style="right: 0">
                {{__('New')}}
            </div>
            <img :style="style" @load="style.opacity = 1" :src="src" class="picture m-auto block" alt="">
        </div>
        <p v-if="src" class="flex items-center justify-between text-sm mt-3 px-2">
            <download-button v-if="downloadable && !media.file"
                             :href="media.preview || media.original"></download-button>
            <base-button v-if="useCropper" class="text-success" @click-or-enter="openCropper(media)">
                {{ __('Crop') }}
            </base-button>
            <base-button class="text-danger" @click-or-enter="deleteImage(media, idx)">
                {{ __('Delete') }}
            </base-button>
        </p>
    </div>
</template>

<script>
    import Vue from 'vue';
    Vue.config.devtools = true;
    import DownloadButton from "./buttons/DownloadButton";
    import BaseButton from "./buttons/BaseButton";
    export default {
        name: "Media",
        components: {DownloadButton, BaseButton},
        props: {
            idx:{},
            media: {type: Object},
            downloadable: {
                type: Boolean,
                default: true,
            },
            src: {},
            useCropper: {
                type: Boolean,
                default: true,
            }
        },
        data: () => ({
           style: {
               opacity: 0,
               transition: 'opacity .3s'
           }
        }),
        methods: {
            openCropper(m) {
                this.$emit('open-cropper', m);
            },
            deleteImage(media, idx) {
                this.$emit('delete-media', {media, idx});
            }
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