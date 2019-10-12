<template>
    <button
            type="button"
            @keydown.enter.prevent="$emit('click-or-enter')"
            @click.prevent="$emit('click-or-enter')"
            tabindex="0"
            class="cursor-pointer dim btn btn-link inline-flex items-center"
    >
        <icon :type="type" class="mr-2" view-box="0 0 20 20" width="16" height="16"/>
        <span class="class mt-1">
            <slot/>
        </span>
    </button>
</template>

<script>
    export default {
        name: "BaseButton",
        props: {
            type: {
                type: String,
                default: 'edit'
            }
        }
    }
</script>

<style scoped>

</style>