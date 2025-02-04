export default class PassportPasswordScheme {
  constructor(auth, options) {
    this.$auth = auth;
    this.name = options._name;

    this.options = Object.assign({}, DEFAULTS, options);
  }

  _setToken(token) {
    console.log('_setToken');
    if (this.options.globalToken) {
      // Set Authorization token for all axios requests
      this.$auth.ctx.app.$axios.setHeader(this.options.tokenName, token);
    }
  }

  _clearToken() {
    console.log('_clearToken');
    if (this.options.globalToken) {
      // Clear Authorization token for all axios requests
      this.$auth.ctx.app.$axios.setHeader(this.options.tokenName, false);
    }
  }

  mounted() {
    console.log('mounted');
    if (this.options.tokenRequired) {
      const token = this.$auth.syncToken(this.name);
      this._setToken(token);
    }

    return this.$auth.fetchUserOnce();
  }

  async login(args) {
    console.log('login');
    if (!this.options.endpoints.login) {
      return;
    }

    // Ditch any leftover local tokens before attempting to log in
    await this._logoutLocally();

    const request_args = {
      ...args,
      data: {
        grant_type: "password",
        client_id: this.options.client_id,
        client_secret: this.options.client_secret,
        scope: "*",
        ...args.data
      }
    };

    console.log('meh2');
    const result = await this.$auth.request(
      request_args,
      this.options.endpoints.login
    );
    console.log('meh3');

    if (this.options.tokenRequired) {
      const token = this.options.tokenType
        ? this.options.tokenType + " " + result
        : result;

      this.$auth.setToken(this.name, token);
      this._setToken(token);
    }

    return this.fetchUser();
  }

  async fetchUser(endpoint) {
    console.log('fetchUser');
    // User endpoint is disabled.
    if (!this.options.endpoints.user) {
      this.$auth.setUser({});
      return;
    }

    // Token is required but not available
    if (this.options.tokenRequired && !this.$auth.getToken(this.name)) {
      return;
    }

    // Try to fetch user and then set
    const user = await this.$auth.requestWith(
      this.name,
      endpoint,
      this.options.endpoints.user
    );
    this.$auth.setUser(user);
  }

  async logout(endpoint) {
    console.log('logout');
    // Only connect to logout endpoint if it's configured
    if (this.options.endpoints.logout) {
      await this.$auth
        .requestWith(this.name, endpoint, this.options.endpoints.logout)
        .catch(() => { });
    }

    // But logout locally regardless
    return this._logoutLocally();
  }

  async _logoutLocally() {
    console.log('logoutLocally');
    if (this.options.tokenRequired) {
      this._clearToken();
    }

    return this.$auth.reset();
  }
}

const DEFAULTS = {
  tokenRequired: true,
  tokenType: "Bearer",
  globalToken: true,
  tokenName: "Authorization"
};
