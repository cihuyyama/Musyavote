<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img
        :class="className"
        src="https://immdiy.or.id/wp-content/uploads/2020/07/new-logo-imm-large.png"
        alt="App Logo Icon"
    />
</template>
