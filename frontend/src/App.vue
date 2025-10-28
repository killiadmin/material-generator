<template>
  <MaterialGenerator />
</template>

<script>
import MaterialGenerator from './components/MaterialGenerator.vue'

export default {
  name: 'App',
  components: {
    MaterialGenerator
  }
}
</script>

<style>
body {
  margin: 0;
}

#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}
</style>
