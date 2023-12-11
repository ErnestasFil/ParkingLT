<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <AlertComponent :show="alert.show" :type="alert.type" :title="alert.title" :text="alert.text" :timeout="alert.timeout"></AlertComponent>

      <v-snackbar v-model="alert.show" :timeout="alert.timeout" variant="plain" origin="auto" position="fixed" class="mb-6 mx-auto" max-width="auto"> </v-snackbar>
      <template v-if="isLoading">
        <div>Loading...</div>
      </template>
      <template v-else>
        <v-form @submit.prevent="update">
          <v-row no-gutters>
            <v-col cols="9">
              <v-sheet class="pa-2 ma-2"> <MapComponent :zoneData="zoneData" @updatePolygon="handleUpdatePolygon"></MapComponent> </v-sheet>
            </v-col>

            <v-col>
              <v-sheet class="pa-2 ma-2">
                <v-toolbar>
                  <v-card-title>
                    <span class="text-h5"> <span class="mdi mdi-map"></span> <b>Stovėjimo vieta</b> - {{ zoneData.space.space_number }} </span>
                  </v-card-title>
                </v-toolbar>
                <v-card-text>
                  <v-alert border="start" variant="tonal" title="Neredaguota informacija">{{ zoneData.space.information }} </v-alert>
                  <v-table class="pt-4 mx-lg-auto">
                    <tbody>
                      <tr>
                        <td>Zonos pavadinimas:</td>
                        <td>{{ zoneData.zone.name }}</td>
                      </tr>
                      <tr>
                        <td>Miestas:</td>
                        <td>{{ zoneData.zone.city }}</td>
                      </tr>
                      <tr>
                        <td>Gatvė:</td>
                        <td>{{ zoneData.space.street }}</td>
                      </tr>
                      <tr>
                        <td>Stovėjimo vietos numeris:</td>
                        <td>{{ zoneData.space.space_number }}</td>
                      </tr>
                      <tr>
                        <td>Neįgaliojo vieta:</td>
                        <td>{{ zoneData.space.invalid_place ? 'Taip' : 'Ne' }}</td>
                      </tr>
                      <tr>
                        <td>Apmokestinamas laikas:</td>
                        <td>{{ zoneData.zone.paying_time }} min</td>
                      </tr>
                      <tr>
                        <td>Kaina:</td>
                        <td>{{ typeof zoneData.zone.price !== 'undefined' ? zoneData.zone.price.toFixed(2) + ' €' : 'N/A' }}</td>
                      </tr>
                    </tbody>
                  </v-table>
                </v-card-text>
                <v-card-text>
                  <v-alert border="start" variant="tonal" type="warning" title="Informacijos redagavimas" class="mb-4 mx-lg-auto"> </v-alert>
                  <v-text-field v-model="updateData.street" label="Gatvė" :error-messages="errors.street"></v-text-field>
                  <v-textarea auto-grow rows="2" v-model="updateData.information" label="Informacija" :error-messages="errors.information"></v-textarea>
                  <v-text-field v-model="updateData.space_number" label="Vietos numeris" :error-messages="errors.space_number" step="1" min="0"></v-text-field>
                  <v-checkbox v-model="updateData.invalid_place" label="Neįgaliojo vieta" :error-messages="errors.invalid_place"></v-checkbox>
                </v-card-text>
                <v-btn block outlined class="flex-grow-1" variant="tonal" :loading="updateData.loading" type="submit"> Atnaujinti duomenis </v-btn>
              </v-sheet>
            </v-col>
          </v-row></v-form
        >
      </template>
    </v-responsive>
  </v-container>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import AlertComponent from '../components/Alert.vue';
import MapComponent from '../components/EditSpaceMap.vue';
import store from '../plugins/store';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useRouter } from 'vue-router';

export default {
  components: {
    AlertComponent,
    MapComponent,
  },
  data: () => ({
    menu: false,
    color: '',
  }),
  computed: {
    swatchStyle() {
      const { menu, color } = this;
      return {
        backgroundColor: color,
        cursor: 'pointer',
        height: '30px',
        width: '30px',
        borderRadius: menu ? '50%' : '4px',
        transition: 'border-radius 200ms ease-in-out',
      };
    },
  },
  watch: {
    'updateData.colour': {
      immediate: true,
      handler(newVal) {
        this.color = newVal;
      },
    },
  },
  methods: {
    toggleColorPicker() {
      this.menu = !this.menu;
    },
    handleUpdatePolygon(newPolygon) {
      this.updateData.location_polygon = newPolygon;
    },
  },
  setup() {
    const router = useRouter();
    const color = ref('');
    const route = useRoute();
    const alert = reactive({
      show: false,
      type: 'error',
      title: '',
      text: '',
      timeout: 10000,
    });
    const isLoading = ref(true);
    const zoneId = ref(route.params.parking_zone);
    const spaceId = ref(route.params.parking_space);
    const zoneData = reactive({ zone: {}, space: {} });
    const updateData = reactive({ loading: false, street: '', information: '', space_number: 0, invalid_place: 0, location_polygon: [] });
    const errors = {};
    const fetchData = async () => {
      alert.show = false;
      try {
        await axios
          .get(`${process.env.APP_URL}/parking_zone/${zoneId.value}`, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
            },
          })
          .then((response) => {
            if (response.status === 200) {
              zoneData.zone = response.data;
              axios
                .get(`${process.env.APP_URL}/parking_zone/${zoneId.value}/parking_space/${spaceId.value}`, {
                  headers: {
                    'Content-Type': 'application/json',
                    Accept: '*/*',
                  },
                })
                .then((data) => {
                  if (data.status === 200) {
                    zoneData.space = data.data;
                    updateData.street = zoneData.space.street;
                    updateData.information = zoneData.space.information;
                    updateData.space_number = zoneData.space.space_number;
                    updateData.invalid_place = zoneData.space.invalid_place;
                    updateData.location_polygon = zoneData.space.location_polygon;
                  }
                });
              console.log(updateData);
            }
          });
      } catch (error) {
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
    });
    const update = async () => {
      try {
        Object.keys(errors).forEach((key) => delete errors[key]);
        updateData.loading = true;
        updateData.colour = color.value;
        console.log(updateData);
        await axios
          .put(`${process.env.APP_URL}/parking_zone/${zoneId.value}/parking_space/${spaceId.value}`, updateData, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          })
          .then((data) => {
            if (data.status === 200) {
              const updateAlert = {
                show: true,
                type: 'success',
                title: 'Stovėjimo vietos informacija atnaujina!',
                text: '',
                timeout: 10000,
              };
              store.commit('setAlert', updateAlert);
              router.push({ name: 'ParkingSpace', params: { id: zoneId.value } });
            }
          });
      } catch (error) {
        console.log(error);
        alert.show = false;
        if (error.response && error.response.status === 422) {
          const info = error.response.data;
          Object.assign(errors, info);
        } else {
          alert.type = 'error';
          alert.title = 'Klaida!';
          alert.text = error.response ? error.response.data.message : 'Nenumatyta klaida';
          alert.show = true;
        }
      } finally {
        updateData.loading = false;
      }
    };

    return {
      alert,
      zoneId,
      zoneData,
      isLoading,
      updateData,
      errors,
      update,
      color,
    };
  },
};
</script>