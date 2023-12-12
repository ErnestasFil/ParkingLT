<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <template v-if="isLoading">
        <div>Loading...</div>
      </template>
      <template v-else>
        <v-row no-gutters>
          <v-col cols="9">
            <v-sheet class="pa-2 ma-2"> <MapComponent :zoneData="zoneData" :spaceData="spaceData"></MapComponent> </v-sheet>
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
                  </tbody>
                </v-table>
              </v-card-text>

              <v-btn v-if="isAdmin" block outlined class="flex-grow-1" variant="tonal" @click="addSpace"> Pridėti stovėjimo vietą </v-btn>
            </v-sheet>
          </v-col>
        </v-row>
      </template>
    </v-responsive>
  </v-container>
</template>

<script>
import { ref, reactive, onMounted, watch, computed } from 'vue';
import MapComponent from '../components/ParkingSpaceMap.vue';
import store from '../plugins/store';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from 'vue-toastification';
export default {
  components: {
    MapComponent,
  },
  setup() {
    const toast = useToast();
    const route = useRoute();
    const router = useRouter();
    const zoneId = ref(route.params.id);
    const zoneData = ref(null);
    const isLoading = ref(true);
    const spaceData = ref(null);
    const mapData = reactive({ loading: false, name: '', paying_time: 0, price: 0, location_polygon: [], information: '', city: '' });
    const fetchData = async () => {
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
          mapData.paying_time = zoneData.value.paying_time;
          mapData.price = zoneData.value.price;
          mapData.location_polygon = zoneData.value.location_polygon;
          mapData.information = zoneData.value.information;
          mapData.city = zoneData.value.city;
          await axios
            .get(`${process.env.APP_URL}/parking_zone/${zoneId.value}/parking_space`, {
              headers: {
                'Content-Type': 'application/json',
                Accept: '*/*',
              },
            })
            .then((response) => {
              spaceData.value = response.data;
            });
        }
      } catch (error) {
        toast.error(error.response ? error.response.data.message : 'Nenumatyta klaida', {
          timeout: 10000,
        });
      }
      isLoading.value = false;
    };

    onMounted(() => {
      fetchData();
    });

    const isAdmin = computed(() => {
      return store.getters.isAdmin;
    });
    const addSpace = async () => {
      router.push({ name: 'ParkingSpaceAdd', params: { id: zoneId.value } });
    };

    return {
      isLoading,
      zoneData,
      isAdmin,
      addSpace,
      spaceData,
    };
  },
};
</script>
