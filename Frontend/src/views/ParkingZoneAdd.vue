<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <AlertComponent :show="alert.show" :type="alert.type" :title="alert.title" :text="alert.text" :timeout="alert.timeout"></AlertComponent>

      <v-snackbar v-model="alert.show" :timeout="alert.timeout" variant="plain" origin="auto" position="fixed" class="mb-6 mx-auto" max-width="auto"> </v-snackbar>
      <template v-if="true">
        <v-form @submit.prevent="create">
          <v-row no-gutters>
            <v-col cols="9">
              <v-sheet class="pa-2 ma-2"> <MapComponent @createPolygon="handleCreatePolygon"></MapComponent> </v-sheet>
            </v-col>

            <v-col>
              <v-sheet class="pa-2 ma-2">
                <v-toolbar>
                  <v-card-title>
                    <span class="text-h5"> <span class="mdi mdi-map"></span> <b>Parkavimosi zonos pridėjimas</b> </span>
                  </v-card-title>
                </v-toolbar>
                <v-card-text>
                  <v-alert border="start" variant="tonal" type="warning" title="Norėdami pradėti priešti zoną paspauskite and žemėlapio" class="mb-4 mx-lg-auto"> </v-alert>
                  <v-text-field v-model="zoneData.name" label="Zonos pavadinimas" :error-messages="errors.name"></v-text-field>
                  <v-textarea auto-grow rows="2" v-model="zoneData.information" label="Informacija" :error-messages="errors.information"></v-textarea>
                  <v-text-field v-model="zoneData.city" label="Miestas" :error-messages="errors.city"></v-text-field>
                  <v-text-field v-model="zoneData.paying_time" label="Apmokestinamas laikas (minutėmis)" :error-messages="errors.paying_time" type="number" min="0"></v-text-field>
                  <v-text-field v-model="zoneData.price" label="Kaina" :error-messages="errors.price" type="number" step="0.01" min="0"></v-text-field>
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
                <v-btn block outlined class="flex-grow-1" variant="tonal" :loading="zoneData.loading" type="submit"> Pridėti zoną </v-btn>
              </v-sheet>
            </v-col>
          </v-row>
        </v-form>
      </template>
    </v-responsive>
  </v-container>
</template>

<script>
import { ref, reactive, onMounted, watch } from 'vue';
import AlertComponent from '../components/Alert.vue';
import MapComponent from '../components/AddZoneMap.vue';
import store from '../plugins/store';
import { useRouter } from 'vue-router';
import axios from 'axios';

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
    'zoneData.colour': {
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
    handleCreatePolygon(newPolygon) {
      this.zoneData.location_polygon = newPolygon;
    },
  },
  setup() {
    const router = useRouter();
    const color = ref('');
    const alert = reactive({
      show: false,
      type: 'error',
      title: '',
      text: '',
      timeout: 10000,
    });
    const zoneData = reactive({ loading: false, name: '', colour: '#000000', paying_time: 0, price: 0, location_polygon: [], information: '', city: '' });
    const errors = {};

    const create = async () => {
      try {
        Object.keys(errors).forEach((key) => delete errors[key]);
        zoneData.loading = true;
        zoneData.colour = color.value;
        console.log(zoneData);
        await axios
          .post(`${process.env.APP_URL}/parking_zone`, zoneData, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          })
          .then((data) => {
            if (data.status === 201) {
              const createAlert = {
                show: true,
                type: 'success',
                title: 'Parkavimosi zona pridėtą!',
                text: '',
                timeout: 10000,
              };
              store.commit('setAlert', createAlert);
              router.push({ name: 'Home' });
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
      color,
      create,
    };
  },
};
</script>
