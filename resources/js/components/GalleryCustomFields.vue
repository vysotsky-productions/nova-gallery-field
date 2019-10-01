<template>
    <modal @modal-close="closeModal">
        <card class="text-center m-2 mb-0 bg-white rounded-lg p-view shadow-lg overflow-hidden test"
              style="width: 80vw">
            <form class=""
                  v-if="customFields"
                  @submit.prevent="sendData">
                <component class="custom-component" :key="custom.attribute" v-for="(custom, i) in customFields"
                           :is="'form-'+custom.component"
                           :field="custom"
                >

                </component>
                <div class="pt-8">
                    <button class="btn btn-default btn-primary" type="submit">
                        {{__('Continue')}}
                    </button>
                    <button class="btn btn-default btn-primary" type="button" @click="closeModal">
                        {{__('Cancel')}}
                    </button>
                </div>
            </form>
        </card>
    </modal>
</template>

<script>
    export default {
        name: "GalleryCustomFields",
        props: ['customFields'],
        methods: {
            closeModal() {
                this.$emit('close')
            },
            sendData() {
                const formData = new FormData();
                this.customFields.forEach(f => f.fill(formData));
                this.$emit('new-gallery-data', _.fromPairs([...formData.entries()]));
                this.closeModal();
            }
        }
    }
</script>

<style scoped lang="scss">
    .custom-component {
        input {
            width: 100%;
        }
    }
</style>