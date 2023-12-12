<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <template v-if="data.isLoading">
        <v-skeleton-loader type="table-row@10"></v-skeleton-loader>
      </template>
      <template v-else
        ><v-card-text>
          <v-alert border="start" variant="tonal" title="Vartotojo informacija"></v-alert>
          <v-table class="pt-4 mx-lg-auto">
            <tbody>
              <tr>
                <td>Vardas:</td>
                <td>{{ fullData.user.name }}</td>
              </tr>
              <tr>
                <td>Pavardė:</td>
                <td>{{ fullData.user.surname }}</td>
              </tr>
              <tr>
                <td>El paštas:</td>
                <td>{{ fullData.user.email }}</td>
              </tr>
              <tr>
                <td>Telefono numeris:</td>
                <td>{{ fullData.user.phone }}</td>
              </tr>
              <tr>
                <td>Rolė:</td>
                <td>{{ fullData.user.role }}</td>
              </tr>
              <tr>
                <td>Balansas:</td>
                <td>{{ fullData.user.balance }} €</td>
              </tr>
              <tr>
                <td colspan="2">
                  <v-btn prepend-icon="mdi-note-edit" block class="mr-3" @click="edit">
                    <template v-slot:prepend>
                      <v-icon color="yellow"></v-icon>
                    </template>
                    Redaguoti duomenis
                  </v-btn>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <v-btn prepend-icon="mdi-delete-empty-outline" block class="mr-3" @click="remove">
                    <template v-slot:prepend>
                      <v-icon color="red"></v-icon>
                    </template>
                    Pašalinti paskyrą
                  </v-btn>
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>
      </template>
    </v-responsive>
  </v-container>
  <EditModal ref="editRef" />
  <ConfirmModal ref="confirmModalRef" />
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import EditModal from '../components/EditSingleUser.vue';
import ConfirmModal from '../components/ConfirmModal.vue';
import { useRoute, useRouter } from 'vue-router';
import store from '../plugins/store';
import { useToast } from 'vue-toastification';
export default {
  components: {
    EditModal,
    ConfirmModal,
  },

  setup() {
    const toast = useToast();
    const confirmModalRef = ref(null);
    const editRef = ref(null);
    const route = useRoute();
    const router = useRouter();
    const userId = ref(route.params.id);
    const data = reactive({
      isLoading: true,
    });
    const fullData = reactive({
      user: [],
    });
    onMounted(async () => {
      try {
        axios
          .get(`${process.env.APP_URL}/user/${userId.value}`, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          })
          .then((response) => {
            if (response.status === 200) {
              fullData.user = response.data;
              if (fullData.user.role && fullData.user.role === 'User') {
                fullData.user.role = 'Vartotojas';
              } else if (fullData.user.role && fullData.user.role === 'Administrator') {
                fullData.user.role = 'Administratorius';
              } else {
                fullData.user.role = 'Nepatvirtintas vartotojas';
              }
            }
          });
        setTimeout(() => {
          data.isLoading = false;
        }, 2000);
      } catch (error) {
        toast.error(error.response ? error.response.data.message : 'Nenumatyta klaida', {
          timeout: 10000,
        });
      }
    });
    const edit = async () => {
      const confirmation = await editRef.value.open(userId);
      if (confirmation) {
        fullData.user.name = confirmation.name;
        fullData.user.surname = confirmation.surname;
        fullData.user.phone = confirmation.phone;
      }
    };
    const remove = async () => {
      const confirmation = await confirmModalRef.value.open('Vartotojo šalinimas', 'Ar tikrai norite pašalinti savo paskyra ir visą susijusią informaciją iš sistemos?');
      if (confirmation) {
        try {
          console.log(store.state.login.token);
          const response = await axios.delete(`${process.env.APP_URL}/user/${userId.value}`, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          });

          if (response.status === 204) {
            toast.success('Vartotojas pašalintas sėkmingai!', {
              timeout: 10000,
            });
            store.commit('updateUserData', null);
            router.push({ name: 'Home' });
          }
        } catch (error) {
          toast.error(error.response ? error.response.data.message : 'Nenumatyta klaida', {
            timeout: 10000,
          });
        }
      }
    };

    return {
      data,
      confirmModalRef,
      editRef,
      edit,
      remove,
      fullData,
    };
  },

  data() {
    return {
      search: '',
    };
  },
};
</script>
