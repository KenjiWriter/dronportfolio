<script setup>
import Form from './Form.vue';

defineProps({
    types: Object,
});
</script>

<template>
    <Form :types="types" />
</template>
