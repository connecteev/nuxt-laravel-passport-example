<template>
  <section class="container">
    <div>
      <strong>loggedIn?</strong>
      <pre>{{ $auth.loggedIn }}</pre>
    </div>
    <div>
      <form>
        <div>
          <label for="username">Username</label>
          <input name="username" v-model="user.username" />
        </div>

        <div>
          <label for="password">Password</label>
          <input type="password" name="password" v-model="user.password" />
        </div>

        <div>
          <button type="submit" @click.prevent="passwordGrantLogin">Login with Password Grant</button>
        </div>

        <div>
          <button type="submit" @click.prevent="customPasswordGrantLogin">Login with Custom Passport Password Scheme</button>
        </div>

      </form>
    </div>
    <hr />
    <div><button @click="oauthLogin">Login with OAuth</button></div>

    <br/><br/>
    <button style="background-color:#faa; font-size:30px;" @click="guestUserGetTags">Guest - Get Tags (wont work)</button>

  </section>
</template>

<script>
export default {
  auth: false,
  data() {
    return {
      user: {
        username: "",
        password: ""
      }
    };
  },
  fetch(context) {
    if (context.$auth.loggedIn) {
      context.redirect(301, '/');
    }
  },
  methods: {
    async passwordGrantLogin() {
      await this.$auth.loginWith("password_grant", {
        data: {
          grant_type: "password",
          client_id: process.env.PASSPORT_PASSWORD_GRANT_ID,
          client_secret: process.env.PASSPORT_PASSWORD_GRANT_SECRET,
          scope: "*",
          username: this.user.username,
          password: this.user.password
        }
      });
      this.$router.replace("/");
    },
    async customPasswordGrantLogin() {
      await this.$auth.loginWith("password_grant_custom", {
        data: this.user
      });
      this.$router.replace("/");
    },
    oauthLogin() {
      this.$auth.loginWith("laravel.passport");
    },
    guestUserGetTags() {
      console.log('guestUserGetTags');
      let tagsApiResponse = this.$axios.get('/api/v1/tags');
      console.log('tagsApiResponse', tagsApiResponse);
    }
  }
};
</script>

<style scoped>
div {
  margin: 10px 0;
}
</style>
