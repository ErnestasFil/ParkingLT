<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
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
                    <span class="text-h5"> <span class="mdi mdi-map"></span> <b>Parkavimosi zona</b> - {{ zoneData.name }} </span>
                  </v-card-title>
                </v-toolbar>
                <v-card-text>
                  <v-alert border="start" variant="tonal" title="Neredaguota informacija">{{ zoneData.information }} </v-alert>
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
                <v-card-text>
                  <v-alert border="start" variant="tonal" type="warning" title="Informacijos redagavimas" class="mb-4 mx-lg-auto"> </v-alert>
                  <v-text-field v-model="updateData.name" label="Zonos pavadinimas" :error-messages="errors.name"></v-text-field>
                  <v-textarea auto-grow rows="2" v-model="updateData.information" label="Informacija" :error-messages="errors.information"></v-textarea>
                  <v-text-field v-model="updateData.city" label="Miestas" :error-messages="errors.city"></v-text-field>
                  <v-text-field v-model="updateData.paying_time" label="Apmokestinamas laikas (minutėmis)" :error-messages="errors.paying_time" type="number" min="0"></v-text-field>
                  <v-text-field v-model="updateData.price" label="Kaina" :error-messages="errors.price" type="number" step="0.01" min="0"></v-text-field>
                  <v-row @click="toggleColorPicker">
                    <v-col style="min-width: 220px">
                      <v-text-field v-model="color" label="Spalva" class="ma-0 pa-0" :error-messages="errors.colour" solo>
                        <template v-slot:append>
                          <v-menu v-model="menu" :close-on-content-click="false">
                            <template v-slot:activator="{ on }">
                              <div :style="swatchStyle" />
                            </template>
                            <v-dialog v-model="menu">
                              <v-card style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%)">
                                <v-card-text class="pa-0">
                                  <v-color-picker v-model="color" mode="hex" :modes="['hex']" flat />
                                </v-card-text>
                              </v-card>
                            </v-dialog>
                          </v-menu>
                        </template>
                      </v-text-field>
                    </v-col>
                  </v-row>
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
import MapComponent from '../components/EditZoneMap.vue';
import store from '../plugins/store';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from 'vue-toastification';
export default {
  components: {
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
    const toast = useToast();
    const router = useRouter();
    const color = ref('');
    const route = useRoute();
    const isLoading = ref(true);
    const zoneId = ref(route.params.id);
    const zoneData = ref(null);
    const updateData = reactive({ loading: false, name: '', colour: '', paying_time: 0, price: 0, location_polygon: [], information: '', city: '' });
    const errors = {};
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
          updateData.name = zoneData.value.name;
          updateData.colour = zoneData.value.colour;
          updateData.paying_time = zoneData.value.paying_time;
          updateData.price = zoneData.value.price;
          updateData.location_polygon = zoneData.value.location_polygon;
          updateData.information = zoneData.value.information;
          updateData.city = zoneData.value.city;
          console.log(updateData);
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
    const update = async () => {
      try {
        Object.keys(errors).forEach((key) => delete errors[key]);
        updateData.loading = true;
        updateData.colour = color.value;
        await axios
          .put(`${process.env.APP_URL}/parking_zone/${zoneId.value}`, updateData, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          })
          .then((data) => {
            if (data.status === 200) {
              toast.error('Parkavimosi zonos informacija atnaujina!', {
                timeout: 10000,
              });
              router.push({ name: 'ParkingSpace', params: { id: zoneId.value } });
            }
          });
      } catch (error) {
        if (error.response && error.response.status === 422) {
          const info = error.response.data;
          Object.assign(errors, info);
        } else {
          toast.error(error.response ? error.response.data.message : 'Nenumatyta klaida', {
            timeout: 10000,
          });
        }
      } finally {
        updateData.loading = false;
      }
    };

    return {
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
