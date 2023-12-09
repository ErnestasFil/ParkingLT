<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <AlertComponent :show="alert.show" :type="alert.type" :title="alert.title" :text="alert.text" :timeout="alert.timeout"></AlertComponent>
      <MapComponent></MapComponent>
      <v-snackbar v-model="alert.show" :timeout="alert.timeout" variant="plain" origin="auto" position="fixed" class="mb-6 mx-auto" max-width="auto"> </v-snackbar>
    </v-responsive>
  </v-container>
</template>

<script>
import { ref, reactive, onMounted, watch } from 'vue';
import AlertComponent from '../components/Alert.vue';
import MapComponent from '../components/ParkingZoneMap.vue';
import store from '../plugins/store';
export default {
  components: {
    AlertComponent,
    MapComponent,
  },
  setup() {
    const alert = reactive({
      show: false,
      type: 'error',
      title: '',
      text: '',
      timeout: 10000,
    });

    onMounted(() => {
      updateAlert();
      watch(() => store.state.alert, updateAlert);
    });

    const updateAlert = () => {
      const loginAlert = store.state.alert;

      if (loginAlert.show) {
        alert.show = loginAlert.show;
        alert.type = loginAlert.type;
        alert.title = loginAlert.title;
        alert.text = loginAlert.text;
        alert.timeout = loginAlert.timeout;
        store.commit('resetAlert');
      }
    };
    return {
      alert,
    };
  },
};
</script>
