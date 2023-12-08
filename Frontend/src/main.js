/**
 * main.js
 *
 * Bootstraps Vuetify and other plugins then mounts the App`
 */

// Plugins
import { registerPlugins } from '@/plugins';

// Components
import App from './App.vue';

// Composables
import { createApp } from 'vue';
import 'flag-icons/css/flag-icons.min.css';
import 'v-phone-input/dist/v-phone-input.css';
import { createVPhoneInput } from 'v-phone-input';
import { VAutocomplete } from 'vuetify/components';

const app = createApp(App);

registerPlugins(app);
const vPhoneInput = createVPhoneInput({
  countryIconMode: 'svg',
  enableSearchingCountry: true,
  displayFormat: 'international',
});
app.component('VAutocomplete', VAutocomplete);

app.use(vPhoneInput);
app.mount('#app');
