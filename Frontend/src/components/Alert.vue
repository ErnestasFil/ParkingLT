<template>
  <v-card transition="slide-y-reverse-transition" class="mb-6 mx-auto" max-width="600">
    <v-alert transition="slide-y-reverse-transition" variant="outlined" v-model="show" :timeout="timeout" :type="type" :title="title" :text="text"></v-alert>
  </v-card>
</template>

<script>
import { ref, defineComponent } from 'vue';

export default defineComponent({
  props: {
    show: Boolean,
    type: String,
    title: String,
    text: String,
    timeout: Number,
  },
});
</script>
