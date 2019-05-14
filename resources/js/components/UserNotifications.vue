<template>
  <li class="nav-item dropdown" v-if="notifications">
    <a href="#" class="nav-link" data-toggle="dropdown">
      <i class="fas fa-bell"></i>
    </a>

    <ul class="dropdown-menu">
      <li class="nav-item" v-for="notification in notifications" :key="notification.id">
        <a class="dropdown-item" href="#" v-text="notification.data.message"></a>
      </li>
    </ul>
  </li>
</template>

<script>
export default {
  data() {
    return {
      notifications: []
    };
  },
  created() {
    axios
      .get("/profiles/" + window.App.user.name + "/notifications")
      .then(response => (this.notifications = response.data));
  }
};
</script>
