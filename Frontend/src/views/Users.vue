<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <v-card flat>
        <v-card-title class="d-flex align-center pe-2">
          <v-icon icon="mdi-account-details"></v-icon> &nbsp; Vartotojų sąrašas

          <v-spacer></v-spacer>

          <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" density="compact" label="Paieška" single-line flat hide-details variant="solo-filled"></v-text-field>
        </v-card-title>

        <v-divider></v-divider>
        <v-data-table
          :loading="isLoading"
          v-model:search="search"
          :items="data.users"
          :headers="tableHeaders"
          items-per-page-text="Vartotojų skaičius per puslapį"
          :items-per-page-options="[
            { value: 10, title: '10' },
            { value: 25, title: '25' },
            { value: 50, title: '50' },
            { value: 100, title: '100' },
            { value: -1, title: 'Visi' },
          ]"
          no-data-text="Vartotojų nerasta, pagal jūsų nurodytą paiešką."
        >
          <template v-slot:loading>
            <v-skeleton-loader type="table-row@10"></v-skeleton-loader>
          </template>
          <template v-slot:item="{ item }">
            <tr>
              <td>{{ item.name }}</td>
              <td>{{ item.surname }}</td>
              <td>{{ item.email }}</td>
              <td>{{ item.phone }}</td>
              <td>{{ item.role }}</td>
              <td>{{ item.balance }} €</td>
              <td>
                <v-btn prepend-icon="mdi-note-edit" color="" class="mr-3" @click="editItem(item.id)">
                  <template v-slot:prepend>
                    <v-icon color="yellow"></v-icon>
                  </template>
                  Redagavimas
                </v-btn>
              </td>
              <td>
                <v-btn prepend-icon="mdi-delete-forever-outline" color="" class="mr-3" @click="deleteInfo(item.id, item.email)">
                  <template v-slot:prepend>
                    <v-icon color="red"></v-icon>
                  </template>
                  Šalinimas
                </v-btn>
              </td>
            </tr>
          </template>
        </v-data-table>
      </v-card>
    </v-responsive>
  </v-container>
  <EditModal ref="editRef" />
  <ConfirmModal ref="confirmModalRef" />
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import EditModal from '../components/EditUser.vue';
import ConfirmModal from '../components/ConfirmModal.vue';
import store from '../plugins/store';
import { useToast } from 'vue-toastification';
import { useRouter } from 'vue-router';
import refresh from '../plugins/refreshToken';
export default {
  components: {
    EditModal,
    ConfirmModal,
  },
  setup() {
    const router = useRouter();
    const toast = useToast();
    const confirmModalRef = ref(null);
    const editRef = ref(null);
    const isLoading = ref(true);
    const data = reactive({
      users: [],
    });
    const tableHeaders = reactive([
      { title: 'Vardas', key: 'name' },
      { title: 'Pavardė', key: 'surname' },
      { title: 'El. paštas', key: 'email' },
      { title: 'Telefono numeris', key: 'phone' },
      { title: 'Rolė', key: 'role' },
      { title: 'Balansas', key: 'balance' },
      { title: '', key: '' },
      { title: '', key: '' },
      { title: '', key: '' },
    ]);

    onMounted(async () => {
      await axios
        .get(`${process.env.APP_URL}/user`, {
          headers: {
            'Content-Type': 'application/json',
            Accept: '*/*',
            Authorization: `Bearer ${store.state.login.token}`,
          },
        })
        .then((response) => {
          if (response.status === 200) {
            data.users = response.data.filter((item) => item.id !== Number(store.state.login.sub));
            data.users.forEach((user) => {
              if (user.role && user.role === 'User') {
                user.role = 'Vartotojas';
              } else if (user.role && user.role === 'Administrator') {
                user.role = 'Administratorius';
              } else {
                user.role = 'Nepatvirtintas vartotojas';
              }
            });
          }
        })
        .catch((error) => {
          if (error.response && error.response.status === 401) {
            refresh.refreshToken(router).then(() => {
              axios
                .get(`${process.env.APP_URL}/user`, {
                  headers: {
                    'Content-Type': 'application/json',
                    Accept: '*/*',
                    Authorization: `Bearer ${store.state.login.token}`,
                  },
                })
                .then((response) => {
                  if (response.status === 200) {
                    data.users = response.data.filter((item) => item.id !== Number(store.state.login.sub));
                    data.users.forEach((user) => {
                      if (user.role && user.role === 'User') {
                        user.role = 'Vartotojas';
                      } else if (user.role && user.role === 'Administrator') {
                        user.role = 'Administratorius';
                      } else {
                        user.role = 'Nepatvirtintas vartotojas';
                      }
                    });
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
      setTimeout(() => {
        isLoading.value = false;
      }, 2000);
    });
    const editItem = async (userId) => {
      const confirmation = await editRef.value.open(userId);
      if (confirmation) {
        const index = data.users.findIndex((user) => user.id === userId);

        if (index !== -1) {
          data.users[index].name = confirmation.name;
          data.users[index].surname = confirmation.surname;
          data.users[index].phone = confirmation.phone;
        }
      }
    };
    const deleteInfo = async (userId, email) => {
      const confirmation = await confirmModalRef.value.open('Vartotojo šalinimas', 'Ar tikrai norite pašalinti šį vartotoją - ' + email + ' ?');
      if (confirmation) {
        await axios
          .delete(`${process.env.APP_URL}/user/${userId}`, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          })
          .then((response) => {
            if (response.status === 204) {
              toast.success('Vartotojas pašalintas sėkmingai!', {
                timeout: 10000,
              });
              data.users = data.users.filter((user) => user.id !== userId);
            }
          })
          .catch((error) => {
            if (error.response && error.response.status === 401) {
              refresh.refreshToken(router).then(() => {
                axios
                  .delete(`${process.env.APP_URL}/user/${userId}`, {
                    headers: {
                      'Content-Type': 'application/json',
                      Accept: '*/*',
                      Authorization: `Bearer ${store.state.login.token}`,
                    },
                  })
                  .then((response) => {
                    if (response.status === 204) {
                      toast.success('Vartotojas pašalintas sėkmingai!', {
                        timeout: 10000,
                      });
                      data.users = data.users.filter((user) => user.id !== userId);
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

    return {
      data,
      tableHeaders,
      confirmModalRef,
      editRef,
      isLoading,
      editItem,
      deleteInfo,
    };
  },

  data() {
    return {
      search: '',
    };
  },
};
</script>
