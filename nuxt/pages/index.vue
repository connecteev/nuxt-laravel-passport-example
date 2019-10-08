<template>
  <section class="container">
    <div>
      <strong>Strategy</strong>
      <pre>{{ strategy }}</pre>
    </div>
    <div>
      <strong>User</strong>
      <pre>{{ $auth.user }}</pre>
    </div>
    <button @click="logout">Logout</button>

    <br/><br/>
    <button style="background-color:#afa; font-size:30px;" @click="loggedinUserGetTags">Logged in - Get Tags</button>
  </section>
</template>

<script>
export default {
  data() {
    return {
      strategy: this.$auth.$storage.getUniversal("strategy"),
    };
  },
  mounted() {
    console.log('mounted');
    let tagsApiResponse = this.$axios.get('/api/v1/tags');
    console.log('tagsApiResponse', tagsApiResponse);
  },
  methods: {
    logout() {
      this.$auth.logout();
      this.$router.replace("/login");
    },
    loggedinUserGetTags() {
      console.log('loggedinUserGetTags');
      let tagsApiResponse = this.$axios.get('/api/v1/tags');
      console.log('tagsApiResponse', tagsApiResponse);
    }
  }
};
</script>
