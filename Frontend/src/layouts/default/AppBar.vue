<template>
  <v-app>
    <v-app-bar :elevation="4" app fixed>
      <v-app-bar-nav-icon @click.stop="toggleSidebar"></v-app-bar-nav-icon>
      <router-link :to="{ name: 'Home' }" style="cursor: pointer; text-decoration: none; color: inherit">
        <v-toolbar-title> ParkingLT</v-toolbar-title>
      </router-link>
      <template v-slot:append>
        <v-btn icon>
          <v-menu activator="parent">
            <v-list
              ><template v-if="!isAuthenticated">
                <v-list-item v-for="(item, index) in data.navitems" :key="index" :value="index" @click="navigateTo(item.path)">
                  <v-list-item-title>{{ item.title }}</v-list-item-title>
                </v-list-item>
              </template>
              <template v-if="isAuthenticated">
                <v-list-item v-for="(item, index) in data.userMenuItems" :key="index" :value="index" @click="navigateTo(item.path)">
                  <v-list-item-title>{{ item.title }}</v-list-item-title>
                </v-list-item>
                <v-list-item @click="logout">
                  <v-list-item-title>Atsijungimas</v-list-item-title>
                </v-list-item>
              </template>
            </v-list>
          </v-menu>
          <v-icon>mdi-account-circle</v-icon>
        </v-btn>

        <v-btn icon>
          <v-icon>mdi-magnify</v-icon>
        </v-btn>

        <v-btn icon>
          <v-icon>mdi-dots-vertical</v-icon>
        </v-btn>
      </template>
    </v-app-bar>

    <v-navigation-drawer v-model="drawer" permanent :disable-resize-watcher="true">
      <v-divider></v-divider>
      <v-list dense nav>
        <v-list-item v-for="(item, index) in data.sideitems" :key="index" @click="navigateTo(item.path)">
          <v-list-item :prepend-icon="item.icon">{{ item.title }}</v-list-item>
        </v-list-item>
      </v-list>
      <v-list v-if="isAdmin" title="dense" nav>
        <v-list-item disabled>Administratoriaus meniu</v-list-item>
        <v-list-item v-for="(item, index) in data.adminItems" :key="index" @click="navigateTo(item.path)">
          <v-list-item :prepend-icon="item.icon">{{ item.title }}</v-list-item>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>
    <v-main>
      <v-container fluid>
        <router-view />
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import store from '../../plugins/store';
import axios from 'axios';
export default {
  setup() {
    const drawer = ref(window.innerWidth >= 768);
    const router = useRouter();
    const data = reactive({
      navitems: [
        { title: 'Prisijungimas', path: 'Login' },
        { title: 'Registracija', path: 'Register' },
      ],
      sideitems: [
        { title: 'Parkavimosi zonos', path: 'ParkingZone', icon: 'mdi-map-legend' },
        { title: 'Test', path: 'About', icon: 'mdi-forum' },
      ],
      userMenuItems: [{ title: 'Profilis', path: 'Profile' }],
      adminItems: [
        { title: 'Parkavimosi zonos pridėjimas', path: 'ParkingZoneAdd', icon: 'mdi-plus' },
        { title: 'Test', path: 'About', icon: 'mdi-forum' },
      ],
    });
    const logout = async () => {
      try {
        const response = await axios
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
              const alert = {
                show: true,
                type: 'success',
                title: 'Atsijungta sėkmingai!',
                text: '',
                timeout: 10000,
              };
              store.commit('setAlert', alert);
              store.commit('updateUserData', null);
              router.push({ name: 'Home' });
            }
          });
      } catch (error) {
        if (error.response && error.response.status === 401) {
          const info = error.response.data;
          const alert = {
            show: true,
            type: 'error',
            title: info.message,
            text: 'Prašome prisijungti iš naujo.',
            timeout: 10000,
          };
          store.commit('setAlert', alert);
        } else {
          const alert = {
            show: true,
            type: 'error',
            title: 'Autorizacijos klaida!',
            text: error.response ? error.response.data.message : 'Nenumatyta klaida',
            timeout: 10000,
          };
          store.commit('setAlert', alert);
        }
        store.commit('updateUserData', null);
        router.push({ name: 'Home' });
      }
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

    const navigateTo = (path) => {
      router.push({ name: path });
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
