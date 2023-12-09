<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <AlertComponent :show="alert.show" :type="alert.type" :title="alert.title" :text="alert.text" :timeout="alert.timeout"></AlertComponent>

      <v-snackbar v-model="alert.show" :timeout="alert.timeout" variant="plain" origin="auto" position="fixed" class="mb-6 mx-auto" max-width="auto"> </v-snackbar>
      <template v-if="isLoading">
        <div>Loading...</div>
      </template>
      <template v-else>
        <v-row no-gutters>
          <v-col cols="9">
            <v-sheet class="pa-2 ma-2"> <MapComponent :zoneData="zoneData"></MapComponent> </v-sheet>
          </v-col>

          <v-col>
            <v-sheet class="pa-2 ma-2">
              <v-toolbar>
                <v-card-title>
                  <span class="text-h5"> <span class="mdi mdi-map"></span> <b>Parkavimosi zona</b> - {{ zoneData.name }} </span>
                </v-card-title>
              </v-toolbar>
              <v-card-text>
                <v-alert border="start" variant="tonal" title="Informacija">{{ zoneData.information }} </v-alert>
                <v-table class="pt-4 mx-lg-auto">
                  <tbody>
                    <tr>
                      <td>Zonos pavadinimas:</td>
                      <td>{{ zoneData.name }}</td>
                    </tr>
                    <tr>
                      <td>Miestas:</td>
                      <td>{{ zoneData.city }}</td>
                    </tr>
                    <tr>
                      <td>Apmokestinamas laikas:</td>
                      <td>{{ zoneData.paying_time }} min</td>
                    </tr>
                    <tr>
                      <td>Kaina:</td>
                      <td>{{ typeof zoneData.price !== 'undefined' ? zoneData.price.toFixed(2) + ' €' : 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td>Spalva:</td>
                      <td :style="{ backgroundColor: zoneData.colour }">{{ zoneData.colour }}</td>
                    </tr>
                  </tbody>
                </v-table>
              </v-card-text>
            </v-sheet>
          </v-col>
        </v-row>
      </template>
    </v-responsive>
  </v-container>
</template>

<script>
import { ref, reactive, onMounted, watch } from 'vue';
import AlertComponent from '../components/Alert.vue';
import MapComponent from '../components/ParkingSpaceMap.vue';
import store from '../plugins/store';
import { useRoute } from 'vue-router';
import axios from 'axios';
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
    const route = useRoute();
    const zoneId = ref(route.params.id);
    const zoneData = ref(null);
    const isLoading = ref(true);
    const mapData = reactive({ loading: false, name: '', colour: '', paying_time: 0, price: 0, location_polygon: [], information: '', city: '' });
    const errors = {};
    const fetchData = async () => {
      alert.show = false;
      try {
        const response = await axios.get(`${process.env.APP_URL}/parking_zone/${zoneId.value}`, {
          headers: {
            'Content-Type': 'application/json',
            Accept: '*/*',
          },
        });

        if (response.status === 200) {
          zoneData.value = response.data;
          mapData.name = zoneData.value.name;
          mapData.colour = zoneData.value.colour;
          mapData.paying_time = zoneData.value.paying_time;
          mapData.price = zoneData.value.price;
          mapData.location_polygon = zoneData.value.location_polygon;
          mapData.information = zoneData.value.information;
          mapData.city = zoneData.value.city;
          console.log(mapData);
        }
      } catch (error) {
        console.log(error);
        alert.show = true;
        alert.type = 'error';
        alert.title = 'Klaida!';
        alert.text = error.response ? error.response.data.message : 'Nenumatyta klaida';
        alert.timeout = 10000;
      }
      isLoading.value = false;
    };

    onMounted(() => {
      fetchData();
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
      isLoading,
      zoneData,
    };
  },
};
</script>
