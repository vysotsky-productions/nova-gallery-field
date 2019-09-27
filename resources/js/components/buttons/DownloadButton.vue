<template>
    <a
            :href="href"
            @keydown.enter.prevent="download"
            @click.prevent="download"
            tabindex="0"
            class="cursor-pointer dim btn btn-link text-primary inline-flex items-center"
    >
        <icon
                class="mr-2"
                type="download"
                view-box="0 0 24 24"
                width="16"
                height="16"
        ></icon>
        <span class="class mt-1">{{__('Download')}}</span>
    </a>
</template>

<script>
    export default {
        name: "DownloadButton",
        props: {
            href: {
                type: String,
                default: ''
            }
        },
        methods: {
            download() {
                if (this.href) {
                    let link = document.createElement('a');
                    link.href = this.href;
                    link.download = 'download';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            },
        }
    }
</script>

<style scoped>

</style>