const { createApp } = Vue;

createApp({
  data() {
    return {
      stations: []
    };
  },
  mounted() {
    console.log("test");
  },
  methods: {

  }
}).mount("#app");