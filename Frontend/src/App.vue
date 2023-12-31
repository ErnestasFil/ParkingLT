<template>
  <v-app>
    <v-app-bar :elevation="4" app height="46">
      <v-app-bar-nav-icon @click.stop="toggleSidebar"></v-app-bar-nav-icon>
      <router-link :to="{ name: 'Home' }" style="cursor: pointer; text-decoration: none; color: inherit">
        <v-toolbar-title> ParkingLT</v-toolbar-title>
      </router-link>
      <v-spacer></v-spacer>
      <v-card v-slot:append>
        <v-btn icon>
          <v-menu activator="parent">
            <v-list>
              <v-card v-if="!isAuthenticated">
                <v-list-item v-for="(item, index) in data.navitems" :key="index" :value="index" @click="navigateTo(item.path)">
                  <v-list-item-title>{{ item.title }}</v-list-item-title>
                </v-list-item>
              </v-card>
              <v-card v-if="isAuthenticated">
                <v-list-item v-for="(item, index) in data.userMenuItems" :key="index" :value="index" :id="item.id" @click="navigateTo(item.path, item.id)">
                  <v-list-item-title>{{ item.title }}</v-list-item-title>
                </v-list-item>
                <v-list-item @click="logout">
                  <v-list-item-title>Atsijungimas</v-list-item-title>
                </v-list-item>
              </v-card>
            </v-list>
          </v-menu>
          <v-icon>mdi-account-circle</v-icon>
        </v-btn>
      </v-card>
    </v-app-bar>
    <v-main>
      <v-container fluid> <router-view /> </v-container>
    </v-main>
    <v-footer app :elevation="4" height="46">
      <v-container>
        <v-row align="center" justify="center">
          <v-col>
            <div class="text-center">{{ new Date().getFullYear() }} — <b>Ernestas Filipavičius</b></div>
          </v-col>
        </v-row>
      </v-container>
    </v-footer>
    <v-navigation-drawer v-model="drawer" permanent :disable-resize-watcher="true">
      <v-divider></v-divider>
      <v-list dense nav>
        <v-list-item v-for="(item, index) in data.sideitems" :key="index" @click="navigateTo(item.path)">
          <v-list-item :prepend-icon="item.icon">{{ item.title }}</v-list-item>
        </v-list-item>
        <v-list-item v-if="isAuthenticated" v-for="(item, index) in data.userItems" :key="index" :id="item.id" @click="navigateTo(item.path, item.id)">
          <v-list-item :prepend-icon="item.icon">{{ item.title }}</v-list-item>
        </v-list-item>
      </v-list>
      <v-list v-if="isAdmin" title="dense" nav>
        <v-list-item disabled>Administratoriaus meniu</v-list-item>
        <v-list-item v-for="(item, index) in data.adminItems" :key="index" :id="item.id" @click="navigateTo(item.path, item.id)">
          <v-list-item :prepend-icon="item.icon">{{ item.title }}</v-list-item>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>
  </v-app>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, reactive, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import store from './plugins/store';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import refresh from './plugins/refreshToken';
export default {
  name: 'App',
  setup() {
    const toast = useToast();
    const drawer = ref(window.innerWidth >= 768);
    let userId = store.state.login.sub;
    const router = useRouter();
    watch(
      () => store.state.login,
      () => {
        userId = store.state.login.sub !== null ? store.state.login.sub : 0;
        data.userMenuItems[0].id = userId;
        data.userItems[0].id = userId;
        data.adminItems[1].id = userId;
      }
    );
    const data = reactive({
      navitems: [
        { title: 'Prisijungimas', path: 'Login' },
        { title: 'Registracija', path: 'Register' },
      ],
      sideitems: [{ title: 'Parkavimosi zonos', path: 'ParkingZone', icon: 'mdi-map-legend' }],
      userMenuItems: [{ title: 'Profilis', path: 'Profile', id: userId }],
      userItems: [{ title: 'Mano rezervacijos', path: 'UserReservations', icon: 'mdi-calendar-check', id: userId }],
      adminItems: [
        { title: 'Parkavimosi zonos pridėjimas', path: 'ParkingZoneAdd', icon: 'mdi-plus' },
        { title: 'Rezervacijų sąrašas', path: 'AllReservations', icon: 'mdi-calendar-account', id: userId },
        { title: 'Vartotojų sąrašas', path: 'Users', icon: 'mdi-account-group' },
      ],
    });
    const logout = async () => {
      await axios
        .post(
          `${process.env.APP_URL}/logout`,
          {},
          {
            headers: {
              Authorization: `Bearer ${store.state.login.token}`,
            },
          }
        )
        .then((data) => {
          if (data.status === 204) {
            toast.success('Atsijungta sėkmingai!', {
              timeout: 10000,
            });
            store.commit('removeUserData', null);
            router.push({ name: 'Home' });
          }
        })
        .catch((error) => {
          if (error.response && error.response.status === 401) {
            refresh.refreshToken(router).then(() => {
              axios
                .post(
                  `${process.env.APP_URL}/logout`,
                  {},
                  {
                    headers: {
                      Authorization: `Bearer ${store.state.login.token}`,
                    },
                  }
                )
                .then((data) => {
                  if (data.status === 204) {
                    toast.success('Atsijungta sėkmingai!', {
                      timeout: 10000,
                    });
                    store.commit('removeUserData', null);
                    router.push({ name: 'Home' });
                  }
                })
                .catch((error) => {
                  if (error.response && error.response.status === 401) {
                    toast.error('Prašome prisijungti iš naujo.', {
                      timeout: 10000,
                    });
                    store.commit('updateUserData', null);
                    router.push({ name: 'Home' });
                  } else {
                    refresh.errorOther(error, router);
                  }
                });
            });
          } else {
            refresh.errorOther(error, router);
          }
        });
    };
    const isAuthenticated = computed(() => {
      return store.getters.isAuthenticated;
    });
    const isAdmin = computed(() => {
      return store.getters.isAdmin;
    });
    const handleResize = () => {
      drawer.value = window.innerWidth >= 768;
    };

    const toggleSidebar = () => {
      drawer.value = !drawer.value;
    };

    const navigateTo = (path, idVal) => {
      router.push({ name: path, params: { id: idVal } });
    };

    onMounted(() => {
      window.addEventListener('resize', handleResize);
    });

    onBeforeUnmount(() => {
      window.removeEventListener('resize', handleResize);
    });

    return {
      drawer,
      data,
      toggleSidebar,
      navigateTo,
      isAuthenticated,
      logout,
      isAdmin,
    };
  },
};
</script>
