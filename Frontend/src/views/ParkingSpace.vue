<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <Loader v-if="isLoading" />
      <template v-if="!isLoading">
        <v-row no-gutters>
          <v-col cols="12" xl="9">
            <v-sheet class="pa-2 ma-2"> <MapComponent :zoneData="zoneData" :spaceData="spaceData"></MapComponent> </v-sheet>
          </v-col>

          <v-col cols="12" xl="3">
            <v-sheet class="pa-2 ma-2">
              <v-toolbar>
                <v-card-title>
                  <span class="text"><span class="mdi mdi-map"></span> <b>Parkavimosi zona</b> - {{ zoneData.name }}</span>
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
import { ref, reactive, onMounted, computed } from 'vue';
import MapComponent from '../components/ParkingSpaceMap.vue';
import store from '../plugins/store';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Loader from '../layouts/default/Loading.vue';
import refresh from '../plugins/refreshToken';
export default {
  components: {
    MapComponent,
    Loader,
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const zoneId = ref(route.params.id);
    const zoneData = ref(null);
    const isLoading = ref(true);
    const spaceData = ref(null);
    const mapData = reactive({ loading: false, name: '', paying_time: 0, price: 0, location_polygon: [], information: '', city: '' });
    const fetchData = async () => {
      await axios
        .get(`${process.env.APP_URL}/parking_zone/${zoneId.value}`, {
          headers: {
            'Content-Type': 'application/json',
            Accept: '*/*',
          },
        })
        .then((response) => {
          if (response.status === 200) {
            zoneData.value = response.data;
            mapData.name = zoneData.value.name;
            mapData.paying_time = zoneData.value.paying_time;
            mapData.price = zoneData.value.price;
            mapData.location_polygon = zoneData.value.location_polygon;
            mapData.information = zoneData.value.information;
            mapData.city = zoneData.value.city;
            axios
              .get(`${process.env.APP_URL}/parking_zone/${zoneId.value}/parking_space`, {
                headers: {
                  'Content-Type': 'application/json',
                  Accept: '*/*',
                },
              })
              .then((response) => {
                spaceData.value = response.data;
              })
              .catch((error) => {
                refresh.error403(error, router);
                refresh.error404(error, router);
                refresh.errorOther(error, router);
              });
          }
        })
        .catch((error) => {
          refresh.error403(error, router);
          refresh.error404(error, router);
          refresh.errorOther(error, router);
        });

      setTimeout(() => {
        isLoading.value = false;
      }, 1000);
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
