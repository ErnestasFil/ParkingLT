<template>
  <v-dialog v-model="dialog" :max-width="options.width" :style="{ zIndex: options.zIndex }" @keydown.esc="cancel">
    <v-card>
      <v-toolbar>
        <v-card-title>
          <span class="text">
            <b>{{ title }}</b>
          </span>
        </v-card-title>
      </v-toolbar>
      <v-card-text>
        <v-alert border="start" variant="tonal" title="Informacija">{{ message }} </v-alert>
      </v-card-text>
      <v-card-actions class="pt-3">
        <v-spacer></v-spacer>
        <v-btn v-if="!options.noconfirm" color="grey" text class="body-2 font-weight-bold" @click.native="cancel">Atšaukti</v-btn>
        <v-btn color="primary" class="body-2 font-weight-bold" outlined @click.native="agree">Patvirtinti</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: 'ConfirmDlg',
  data() {
    return {
      dialog: false,
      resolve: null,
      reject: null,
      message: null,
      title: null,
      options: {
        width: 600,
        zIndex: 200,
        noconfirm: false,
      },
    };
  },

  methods: {
    open(title, message, options) {
      this.dialog = true;
      this.title = title;
      this.message = message;
      this.options = Object.assign(this.options, options);
      return new Promise((resolve, reject) => {
        this.resolve = resolve;
        this.reject = reject;
      });
    },
    agree() {
      this.resolve(true);
      this.dialog = false;
    },
    cancel() {
      this.resolve(false);
      this.dialog = false;
    },
  },
};
</script>
