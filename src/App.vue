<template>
  <div id="app">
    <upload-form></upload-form>
  </div>
</template>

<script>
import UploadForm from './components/UploadForm';

export default {
  name: 'app',

  components: {
    UploadForm
  }
}
</script>

<style>
  body {
     font-family: Arial, sans-serif;
  }

  .list-inline {
    list-style-type: none;
    padding: 0;
    margin: 0;
    margin-left: 10px;
  }

  .list-inline__item {
    display: inline;
    margin-right: 20px;
  }

  .last-inline__item-last {
    margin-right: 0;
  }

  .dragndrop__status {
    padding: 10px;
    text-align: center;
  }
</style>
