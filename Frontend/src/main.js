import { registerPlugins } from '@/plugins';

import App from './App.vue';

import { createApp } from 'vue';
import 'flag-icons/css/flag-icons.min.css';
import 'v-phone-input/dist/v-phone-input.css';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
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

app.use(Toast, {
  transition: 'Vue-Toastification__fade',
  maxToasts: 2,
  newestOnTop: true,
  position: 'bottom-center',
  closeOnClick: false,
  pauseOnFocusLoss: false,
  pauseOnHover: false,
  draggable: false,
  draggablePercent: 1.06,
  showCloseButtonOnHover: false,
  hideProgressBar: false,
  closeButton: 'button',
  icon: true,
  rtl: false,
});
app.use(vPhoneInput);
app.mount('#app');
