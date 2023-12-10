<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <AlertComponent :show="alert.show" :type="alert.type" :title="alert.title" :text="alert.text" :timeout="alert.timeout"></AlertComponent>

      <v-snackbar v-model="alert.show" :timeout="alert.timeout" variant="plain" origin="auto" position="fixed" class="mb-6 mx-auto" max-width="auto"> </v-snackbar>
      <template v-if="true">
        <v-form @submit.prevent="create">
          <v-row no-gutters>
            <v-col cols="9">
              <v-sheet class="pa-2 ma-2"> <MapComponent :zoneId="zoneId" :zoneData="newData.data" @update:zoneData="newData.data = $event" @createPolygon="handleCreatePolygon"></MapComponent> </v-sheet>
            </v-col>

            <v-col>
              <v-sheet class="pa-2 ma-2">
                <v-toolbar>
                  <v-card-title>
                    <span class="text-h5"> <span class="mdi mdi-map"></span> <b>Parkavimosi vietos pridėjimas</b> </span>
                  </v-card-title>
                </v-toolbar>
                <v-card-text>
                  <v-alert border="start" variant="tonal" type="warning" title="Norėdami pradėti priešti vietą paspauskite and žemėlapio" class="mb-4 mx-lg-auto"> </v-alert>
                  <v-text-field v-model="zoneData.street" label="Gatvė" :error-messages="errors.street"></v-text-field>
                  <v-textarea auto-grow rows="2" v-model="zoneData.information" label="Informacija" :error-messages="errors.information"></v-textarea>
                  <v-text-field v-model="zoneData.space_number" label="Vietos numeris" :error-messages="errors.space_number" type="number" min="0"></v-text-field>
                  <v-checkbox v-model="zoneData.invalid_place" label="Neįgaliojo vieta" :error-messages="errors.invalid_place"></v-checkbox>
                </v-card-text>
                <v-btn block outlined class="flex-grow-1" variant="tonal" :loading="zoneData.loading" type="submit"> Pridėti vietą </v-btn>
              </v-sheet>
            </v-col>
          </v-row>
        </v-form>
      </template>
    </v-responsive>
  </v-container>
</template>

<script>
import { ref, reactive } from 'vue';
import AlertComponent from '../components/Alert.vue';
import MapComponent from '../components/AddSpaceMap.vue';
import store from '../plugins/store';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';

export default {
  components: {
    AlertComponent,
    MapComponent,
  },

  methods: {
    handleCreatePolygon(newPolygon) {
      this.zoneData.location_polygon = newPolygon;
    },
  },
  setup(props, { emit }) {
    const route = useRoute();
    const alert = reactive({
      show: false,
      type: 'error',
      title: '',
      text: '',
      timeout: 10000,
    });
    const zoneId = ref(route.params.id);
    let zoneData = reactive({ loading: false, space_number: 0, invalid_place: 0, street: '', location_polygon: [], information: '' });
    const errors = {};
    let newData = reactive({ data: null });
    const create = async () => {
      newData.data = null;
      try {
        alert.show = false;
        Object.keys(errors).forEach((key) => delete errors[key]);
        zoneData.loading = true;
        await axios
          .post(`${process.env.APP_URL}/parking_zone/${zoneId.value}/parking_space`, zoneData, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          })
          .then((data) => {
            if (data.status === 201) {
              alert.show = true;
              alert.type = 'success';
              alert.title = 'Stovėjimo vieta pridėtą!';
              alert.text = '';
              alert.timeout = 10000;
              newData.data = { ...zoneData };
              //newData = 'hfcxh';

              zoneData.space_number++;
            }
          });
      } catch (error) {
        console.log(error);
        alert.show = false;
        if (error.response && error.response.status === 422) {
          const info = error.response.data;
          if (info.location_polygon) {
            alert.type = 'error';
            alert.title = 'Klaida!';
            alert.text = info.location_polygon[0];
            alert.show = true;
          }
          Object.assign(errors, info);
        } else {
          alert.type = 'error';
          alert.title = 'Klaida!';
          alert.text = error.response ? error.response.data.message : 'Nenumatyta klaida';
          alert.show = true;
        }
      } finally {
        zoneData.loading = false;
      }
    };
    return {
      alert,
      zoneData,
      errors,
      create,
      zoneId,
      newData,
    };
  },
};
</script>
