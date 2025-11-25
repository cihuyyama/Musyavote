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
        src="/storage/logo/dpdimmdiy.png"
        alt="DPD IMM DIY"
    />
</template>
