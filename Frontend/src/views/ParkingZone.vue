<template>
  <Loader v-if="data.overlay" />
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <v-card flat>
        <v-card-title class="d-flex align-center pe-2">
          <v-icon icon="mdi-map-marker-question-outline"></v-icon> &nbsp; Parkavimo zonos paieška

          <v-spacer></v-spacer>

          <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" density="compact" label="Paieška" single-line flat hide-details variant="solo-filled"></v-text-field>
        </v-card-title>

        <v-divider></v-divider>
        <v-data-table
          v-model:search="search"
          :items="data.zoneData"
          :headers="tableHeaders"
          items-per-page-text="Parkavimo zonų per puslapį"
          :items-per-page-options="[
            { value: 10, title: '10' },
            { value: 25, title: '25' },
            { value: 50, title: '50' },
            { value: 100, title: '100' },
            { value: -1, title: 'Visi' },
          ]"
          no-data-text="Parkavimosi zonų nerasta, pagal jūsų nurodytą paiešką."
        >
          <template v-slot:item="{ item }">
            <tr>
              <td>{{ item.name }}</td>
              <td>{{ item.city }}</td>
              <td>{{ item.paying_time }} min</td>
              <td>{{ item.price.toFixed(2) }} €</td>
              <td>{{ item.information }}</td>
              <td>
                <v-btn @click="goToZone(item.id)" color="primary"> Atidaryti zoną </v-btn>
              </td>
              <td v-if="isAdmin">
                <v-btn density="comfortable" color="yellow" icon="mdi-note-edit" class="mr-3" @click="editItem(item.id)"></v-btn><v-btn density="comfortable" color="red" icon="mdi-delete-forever" @click="deleteItem(item.id, item.name)"></v-btn>
              </td>
            </tr>
          </template>
        </v-data-table>
      </v-card>
      <ConfirmModal ref="confirmModalRef" />
    </v-responsive>
  </v-container>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import ConfirmModal from '../components/ConfirmModal.vue';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import store from '../plugins/store';
import Loader from '../layouts/default/Loading.vue';
import refresh from '../plugins/refreshToken';
export default {
  components: {
    ConfirmModal,
    Loader,
  },
  setup() {
    const toast = useToast();
    const confirmModalRef = ref(null);
    const data = reactive({
      zoneData: [],
      overlay: true,
    });
    const router = useRouter();
    const isAdmin = computed(() => {
      return store.getters.isAdmin;
    });
    const tableHeaders = reactive([
      { title: 'Zonos pavadinimas', key: 'name' },
      { title: 'Miestas', key: 'city' },
      { title: 'Apmokestinamas laikas', key: 'paying_time' },
      { title: 'Kaina', key: 'price' },
      { title: 'Informacija', key: 'information' },
      { title: '', key: '' },
      ...(isAdmin.value ? [{ title: 'Admin meniu', key: '' }] : []),
    ]);
    onMounted(async () => {
      try {
        const response = await axios.get(`${process.env.APP_URL}/parking_zone`, {
          headers: {
            'Content-Type': 'application/json',
            Accept: '*/*',
          },
        });

        if (response.status === 200) {
          data.zoneData = response.data;
        }
      } catch (error) {
        toast.error(error.response ? error.response.data.message : 'Nenumatyta klaida', {
          timeout: 10000,
        });
      }
      setTimeout(() => {
        data.overlay = false;
      }, 1000);
    });

    const goToZone = (zoneId) => {
      router.push({ name: 'ParkingSpace', params: { id: zoneId } });
    };
    const deleteItem = async (zoneId, name) => {
      const confirmation = await confirmModalRef.value.open('Parkavimo zonos pašalinimas', 'Ar tikrai norite pašalinti parkavimosi zoną - ' + name + ' ir visą su ja susijusią informaciją?');
      if (confirmation) {
        await axios
          .delete(`${process.env.APP_URL}/parking_zone/${zoneId}`, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          })
          .then((response) => {
            if (response.status === 204) {
              toast.success('Parkavimosi zona pašalinta sėkmingai!', {
                timeout: 10000,
              });
              data.zoneData = data.zoneData.filter((item) => item.id !== zoneId);
            }
          })
          .catch((error) => {
            if (error.response && error.response.status === 401) {
              refresh.refreshToken(router).then(() => {
                axios
                  .delete(`${process.env.APP_URL}/parking_zone/${zoneId}`, {
                    headers: {
                      'Content-Type': 'application/json',
                      Accept: '*/*',
                      Authorization: `Bearer ${store.state.login.token}`,
                    },
                  })
                  .then((response) => {
                    if (response.status === 204) {
                      toast.success('Parkavimosi zona pašalinta sėkmingai!', {
                        timeout: 10000,
                      });
                      data.zoneData = data.zoneData.filter((item) => item.id !== zoneId);
                    }
                  })
                  .catch((error) => {});
              });
            } else if (error.response && error.response.status === 403) {
              toast.error('Prieiga negalima!', {
                timeout: 10000,
              });
              router.push({ name: 'Home' });
            } else if (error.response && error.response.status === 404) {
              toast.error(error.response.data.message, {
                timeout: 10000,
              });
              router.push({ name: 'Home' });
            } else {
              toast.error(error.response ? error.response.data.message : 'Nenumatyta klaida', {
                timeout: 10000,
              });
            }
          });
      }
    };

    const editItem = (zoneId) => {
      router.push({ name: 'EditParkingZone', params: { id: zoneId } });
    };

    return {
      data,
      tableHeaders,
      goToZone,
      deleteItem,
      confirmModalRef,
      editItem,
      isAdmin,
    };
  },

  data() {
    return {
      search: '',
    };
  },
};
</script>
