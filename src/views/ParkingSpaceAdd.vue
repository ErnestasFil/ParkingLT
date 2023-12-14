<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <Loader v-if="isLoading" />
      <template v-if="true">
        <v-form @submit.prevent="create">
          <v-row no-gutters>
            <v-col cols="12" xl="9">
              <v-sheet class="pa-2 ma-2"> <MapComponent :zoneId="zoneId" :zoneData="newData.data" @update:zoneData="newData.data = $event" @createPolygon="handleCreatePolygon"></MapComponent> </v-sheet>
            </v-col>

            <v-col cols="12" xl="3">
              <v-sheet class="pa-2 ma-2">
                <v-toolbar>
                  <v-card-title>
                    <span class="text"> <span class="mdi mdi-map"></span> <b>Parkavimosi vietos pridėjimas</b> </span>
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
import MapComponent from '../components/AddSpaceMap.vue';
import store from '../plugins/store';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import Loader from '../layouts/default/Loading.vue';
import refresh from '../plugins/refreshToken';
export default {
  components: {
    MapComponent,
    Loader,
  },

  methods: {
    handleCreatePolygon(newPolygon) {
      this.zoneData.location_polygon = newPolygon;
    },
  },
  setup() {
    const isLoading = ref(true);
    const toast = useToast();
    const router = useRouter();
    const route = useRoute();
    const zoneId = ref(route.params.id);
    let zoneData = reactive({ loading: false, space_number: 0, invalid_place: 0, street: '', location_polygon: [], information: '' });
    const errors = {};
    let newData = reactive({ data: null });
    const create = async () => {
      newData.data = null;
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
            toast.success('Stovėjimo vieta pridėtą!', {
              timeout: 10000,
            });
            newData.data = { ...zoneData };
            zoneData.space_number++;
          }
        })
        .catch((error) => {
          if (error.response && error.response.status === 422) {
            const info = error.response.data;
            if (info.location_polygon) {
              toast.error(info.location_polygon[0], {
                timeout: 10000,
              });
            }
            Object.assign(errors, info);
          } else if (error.response && error.response.status === 401) {
            refresh.refreshToken(router).then(() => {
              axios
                .post(`${process.env.APP_URL}/parking_zone/${zoneId.value}/parking_space`, zoneData, {
                  headers: {
                    'Content-Type': 'application/json',
                    Accept: '*/*',
                    Authorization: `Bearer ${store.state.login.token}`,
                  },
                })
                .then((data) => {
                  if (data.status === 201) {
                    toast.success('Stovėjimo vieta pridėtą!', {
                      timeout: 10000,
                    });
                    newData.data = { ...zoneData };
                    zoneData.space_number++;
                  }
                })
                .catch((error) => {
                  if (error.response && error.response.status === 422) {
                    const info = error.response.data;
                    if (info.location_polygon) {
                      toast.error(info.location_polygon[0], {
                        timeout: 10000,
                      });
                    }
                    Object.assign(errors, info);
                  } else {
                    refresh.error403(error, router);
                    refresh.error404(error, router);
                    refresh.errorOther(error, router);
                  }
                });
            });
          } else {
            refresh.error403(error, router);
            refresh.error404(error, router);
            refresh.errorOther(error, router);
          }
        });
      zoneData.loading = false;
    };
    setTimeout(() => {
      isLoading.value = false;
    }, 1000);
    return {
      zoneData,
      isLoading,
      errors,
      create,
      zoneId,
      newData,
    };
  },
};
</script>
