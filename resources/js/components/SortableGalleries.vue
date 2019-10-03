<template>
    <modal @modal-close="closeModal">
        <card class="text-center m-2 mb-0 bg-white rounded-lg p-view shadow-lg overflow-hidden test flex flex-col">
            <draggable v-bind="dragOptions" v-model="ordered">
                <transition-group type="transition">
                    <div class="m-2 p-3 rounded shadow-md text-info" :key="g.id" v-for="g in ordered">
                        {{g[galleryNameAttribute] ||
                        'Аттрибут не указан'}}
                    </div>
                </transition-group>
            </draggable>

            <div class="pt-8">
                <button class="btn btn-default btn-primary mx-1" type="button" @click.prevent="handleSort">
                    {{__('Continue')}}
                </button>
                <button class="btn btn-default btn-primary mx-1" type="button" @click="closeModal">
                    {{__('Cancel')}}
                </button>
            </div>
        </card>
    </modal>
</template>

<script>
    import draggable from 'vuedraggable';

    export default {
        name: "SortableGalleries",
        data: () => ({ordered: []}),
        props: {
            galleries: {
                type: Array,
            },
            galleryNameAttribute: {
                type: String,
            }
        },
        components: {
            draggable
        },
        methods: {
            handleSort() {
                this.$emit('change-gallery-sort', this.ordered.map(g => g.id));
                this.closeModal();
            },
            closeModal() {
                this.$emit('close')
            }
        },
        mounted() {
            this.ordered = this.galleries;
        },
        computed: {
            dragOptions() {
                return {
                    animation: 200,
                    group: "description",
                    disabled: false,
                    ghostClass: "ghost"
                };
            }
        }
    }
</script>

<style scoped>
    .flip-list-move {
        transition: transform 0.5s;
    }
    .no-move {
        transition: transform 0s;
    }
    .ghost {
        opacity: 0.5;
        background: #c8ebfb;
    }
</style>